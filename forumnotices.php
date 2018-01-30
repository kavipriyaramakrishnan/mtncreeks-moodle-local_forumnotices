<?php
/*
 * MtnCreeks Forum Notices
 *
 * Notices posting screen. 
 *
 * @package    : local_forumnotices
 * @copyright  : 2017 Pukunui
 * @author     : Priya Ramakrishnan, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require($CFG->dirroot.'/local/forumnotices/forumnotices_form.php');
define('FORUM_MAILED_PENDING', 0);
require_login();
$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_RAW);

$config = get_config('local_forumnotices');
$courseid = $config->courses;
$expiry = $config->expiry;
$strtitle  = get_string('forumnotices', 'local_forumnotices');
$systemcontext = context_system::instance();
$url = new moodle_url('/local/forumnotices/forumnotices.php');

// Set up PAGE Object.
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($strtitle);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($strtitle);

$mform = new forumnotices_form('', array('courseid' => $courseid, 'expiry' => $expiry, 'id' => $id, 'action' => $action));

if ($data = $mform->get_data()) {
    $save = optional_param('save', '', PARAM_RAW);
    $update = optional_param('update', '', PARAM_RAW);
    if (!empty($save)) {
	    $timenow = time();
	    $pid = '';
	    $did = '';  
	    foreach ($data->forums as $frmd => $frmv) {
		    $forum = $DB->get_record('forum', array('id' => $frmv));
		    $cm = get_coursemodule_from_instance('forum', $frmv);
		    $context    = context_module::instance($cm->id);
		    // Add a new post
		    $cm    = get_coursemodule_from_instance('forum', $frmv);
		    $post = new stdClass();
		    $post->discussion    = 0;
		    $post->parent        = 0;
		    $post->userid        = $USER->id;
		    $post->created       = $timenow;
		    $post->modified      = $timenow;
		    $post->mailed        = FORUM_MAILED_PENDING;
		    $post->subject       = $data->subject;
		    $post->message       = $data->message['text'];
		    $post->messageformat = $data->message['format'];
		    $post->attachments   = isset($data->attachments) ? $data->attachments : null;

		    $post->id = $DB->insert_record("forum_posts", $post);
		    $pid .= $post->id.',';

		    // Add new discussion.
		    $discussion = new stdClass();
		    $discussion->course       = $data->courseid;
		    $discussion->firstpost    = $post->id;
		    $discussion->forum        = $frmv;
		    $discussion->name         = $data->subject;
		    $discussion->userid       = $USER->id;
		    $discussion->timemodified = $timenow;
		    $discussion->timestart    = $data->timestart;
		    $discussion->timeend      = $data->timeend;
		    if ($data->pinned) {
			    $pinnedpost = $DB->get_record('forum_discussions', array('course' => $data->courseid, 'forum' => $frmv, 'pinned' => 1));
			    if ($pinnedpost) {
				    $rec = new stdClass();
				    $rec->id = $pinnedpost->id;
				    $rec->pinned = 0;
				    $DB->update_record('forum_discussions', $rec);
				    $discussion->pinned = $data->pinned;
			    } else {
				    $discussion->pinned = $data->pinned;
			    }
		    }
		    $discussion->id = $DB->insert_record('forum_discussions', $discussion);
		    $did .= $discussion->id.',';
		    // Finally, set the pointer on the post.
		    $DB->set_field("forum_posts", "discussion", $discussion->id, array("id"=>$post->id));
		    /*$post->message = file_save_draft_area_files($post->itemid, $context->id, 'mod_forum', 'post', $post->id,
		      mod_forum_post_form::editor_options($context, null), $post->message);
		      $DB->set_field('forum_posts', 'message', $post->message, array('id'=>$post->id));*/
		    if (!empty($post->attachments)) {
			    $info = file_get_draft_area_info($post->attachments);
			    $present = ($info['filecount']>0) ? '1' : '';
			    file_save_draft_area_files($post->attachments, $context->id, 'mod_forum', 'attachment', $post->id,
					    mod_forum_post_form::attachment_options($forum));
			    $DB->set_field('forum_posts', 'attachment', $present, array('id'=>$post->id));
		    }
	    }
	    // Add to frumnotices table.
	    $forumids = '';
	    $forumnotices = new stdClass();
	    $forumnotices->subject = $data->subject;
	    $forumnotices->message = $data->message['text'];;
	    $forumnotices->userid = $USER->id;
	    $forumnotices->course = $data->courseid;
	    foreach ($data->forums as $frmd => $frmv) {
		    $forumids .= $frmv.',';
	    }
	    $forumidslen = strlen($forumids);
	    $forumlist = substr($forumids, 0, $forumidslen - 1); 
	    $forumnotices->forums  = $forumlist;
	    $forumnotices->timemodified = $timenow;
	    $forumnotices->timestart = $data->timestart;
	    $forumnotices->timeend = $data->timeend;
	    $pidlen = strlen($pid);
	    $pidlist = substr($pid, 0, $pidlen-1);
	    $forumnotices->postid = $pidlist;
	    $didlen = strlen($did);
	    $didlist = substr($did, 0, $didlen-1);
	    $forumnotices->discussionid = $didlist;
            $forumnotices->pinned = $data->pinned;
	    $DB->insert_record("local_forumnotices", $forumnotices);
	    redirect($CFG->wwwroot."/course/view.php?id=$courseid");
    } else if (!empty($update)) {
        $extfn = $DB->get_record('local_forumnotices', array('id' => $data->fntid));
        $newforum = array();
        $forumarray = explode(",", $extfn->forums);
        $discussarray = explode(",", $data->discussid);
        $postarray = explode(",", $data->postid);
        foreach ($data->forums as $frmd => $frmv) {
           if (!in_array($frmv, $forumarray)) {
               // Update.
               $newforum[] = $frmv;
           }
        }echo 'New Forum';print_object($newforum);
        foreach ($forumarray as $frml => $frmd) {
           if (!in_array($frmd, $data->forums)) {
               // Delete.
               $delforum[] = $frmd;
           }
        }
	$timenow = time();
        $discussionid = '';
        $postid = '';
        $deldisid = '';
        $delpostid = '';
        foreach ($discussarray as $disk => $disd) {
                $discussion = new stdClass();
                $discussion->id = $disd;
                $discussion->course       = $data->courseid;
                $discussion->name         = $data->subject;
                $discussion->userid       = $USER->id;
                $discussion->timemodified = $timenow;
                $discussion->timeend      = $data->timeend;
                if ($data->pinned) {
                        $pinnedpost = $DB->get_record('forum_discussions', array('course' => $data->courseid, 'forum' => $frmv, 'pinned' => 1));
                        if ($pinnedpost) {
                                $rec = new stdClass();
                                $rec->id = $pinnedpost->id;
                                $rec->pinned = 0;
                                $DB->update_record('forum_discussions', $rec);
                                $discussion->pinned = $data->pinned;
                        } else {
                                $discussion->pinned = $data->pinned;
                        }
                }
                $discussion->id = $DB->update_record('forum_discussions', $discussion);
                //$discussionid .= $disd.',';echo $discussionid.'*';
                $postrec = $DB->get_record('forum_posts', array('discussion' => $disd));
                // Update the post
                $post = new stdClass();
                $post->id = $postrec->id;
                $post->discussion = $disd;
                $post->userid        = $USER->id;
                $post->modified      = $timenow;
                $post->subject       = $data->subject;
                $post->message       = $data->message['text'];
                $post->messageformat = $data->message['format'];
                $post->attachments   = isset($data->attachments) ? $data->attachments : null;
                $post->id = $DB->update_record("forum_posts", $post);
                // To delete the forum
                $forumid = $DB->get_field('forum_discussions', 'forum', array('id' => $disd));
                if (!in_array($forumid, $delforum)) {
                    $discussionid .= $disd.',';
                    $postid .= $postrec->id.',';
                } else {
                    $deldisid .= $disd.',';
                    $delpostid .= $postrec->id.',';
                }
        }
        // For new records.
        foreach ($newforum as $newd => $newk) {
		$post = new stdClass();
		$post->discussion    = 0;
		$post->parent        = 0;
		$post->userid        = $USER->id;
		$post->created       = $timenow;
		$post->modified      = $timenow;
		$post->mailed        = FORUM_MAILED_PENDING;
		$post->subject       = $data->subject;
		$post->message       = $data->message['text'];
		$post->messageformat = $data->message['format'];
		$post->attachments   = isset($data->attachments) ? $data->attachments : null;
		$post->id = $DB->insert_record("forum_posts", $post);
		$postid .= $post->id.',';

		// Add new discussion.
		$discussion = new stdClass();
		$discussion->course       = $data->courseid;
		$discussion->firstpost    = $post->id;
		$discussion->forum        = $newk;
		$discussion->name         = $data->subject;
		$discussion->userid       = $USER->id;
		$discussion->timemodified = $timenow;
		$discussion->timestart    = $data->timestart;
		$discussion->timeend      = $data->timeend;
		if ($data->pinned) {
			$pinnedpost = $DB->get_record('forum_discussions', array('course' => $data->courseid, 'forum' => $frmv, 'pinned' => 1));
			if ($pinnedpost) {
				$rec = new stdClass();
				$rec->id = $pinnedpost->id;
				$rec->pinned = 0;
				$DB->update_record('forum_discussions', $rec);
				$discussion->pinned = $data->pinned;
			} else {
				$discussion->pinned = $data->pinned;
			}
		}
		$discussion->id = $DB->insert_record('forum_discussions', $discussion);
		$discussionid .= $discussion->id.',';
		// Finally, set the pointer on the post.
		$DB->set_field("forum_posts", "discussion", $discussion->id, array("id"=>$post->id));           
        }
        $forumids = '';
        $forumnotices = new stdClass();
        $forumnotices->id = $data->fntid;
        $forumnotices->subject = $data->subject;
        $forumnotices->message = $data->message['text'];;
        $forumnotices->userid = $USER->id;
        $forumnotices->course = $data->courseid;
        foreach ($data->forums as $frmd => $frmv) {
           if (!in_array($frmv, $delforum))  {
              $forumids .= $frmv.',';
           }
        }
        $forumidslen = strlen($forumids);
        $forumlist = substr($forumids, 0, $forumidslen - 1);
        $forumnotices->forums  = $forumlist;
        $forumnotices->timemodified = $timenow;
        $forumnotices->timestart = $data->timestart;
        $forumnotices->timeend = $data->timeend;
        $postidslen = strlen($postid);
        $fpostid = substr($postid, 0, $postidslen - 1);
        $forumnotices->postid = $fpostid;
        $discussidlen = strlen($discussionid);
        $fdiscussid = substr($discussionid, 0, $discussidlen - 1);
        $forumnotices->discussionid = $fdiscussid;
        $DB->update_record("local_forumnotices", $forumnotices);
        // Deleting froums.
        if (!empty($deldisid)) {
            $todelarr = explode(",", $deldisid);
            foreach ($todelarr as $tok => $tod) {
               $rec =  new stdClass();
               $rec->id = $tod;
               $DB->delete_records('forum_discussions', array('id' => $tod));
            }
        }
        if (!empty($delpostid)) {
            $todelarr = explode(",", $delpostid);
            foreach ($todelarr as $tok => $tod) {
               $rec =  new stdClass();
               $rec->id = $tod;
               $DB->delete_records('forum_posts', array('id' => $tod));
            }
        }
        redirect($CFG->wwwroot."/course/view.php?id=$data->courseid");
    }
} else if ($mform->is_cancelled()) {
   redirect($CFG->wwwroot); 
}

// Output renderers.
echo $OUTPUT->header();
echo $mform->display();
echo $OUTPUT->footer();
