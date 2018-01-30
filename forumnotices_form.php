<?php
/*
 * MtnCreeks Forum Notices
 *
 * Notices posting screen - Form elements
 *
 * @package    : local_forumnotices
 * @copyright  : 2017 Pukunui
 * @author     : Priya Ramakrishnan, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
/*
 * Class forumnotices_form extends moodleform
 */
class forumnotices_form extends moodleform {
    /*
     * Function Definition to define Form elements
     */
    public function definition() {
        global $DB, $CFG;
        $mform =& $this->_form;
        $courseid = $this->_customdata['courseid'];
        $expiry = $this->_customdata['expiry'];
        $fnid = $this->_customdata['id'];
        $action = $this->_customdata['action'];
        if (!strcmp($action, 'edit')) {
            $fnrec = $DB->get_record('local_forumnotices', array('id' => $fnid));
        }
        $coursename = $DB->get_field('course', 'fullname', array('id' => $courseid));
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_RAW);
        $mform->addElement('hidden', 'postid', $fnrec->postid);
        $mform->setType('postid', PARAM_RAW);
        $mform->addElement('hidden', 'discussid', $fnrec->discussionid);
        $mform->setType('discussid', PARAM_RAW);
        $mform->addElement('hidden', 'fntid', $fnid);
        $mform->setType('fntid', PARAM_RAW);

        $mform->addElement('text', 'coursename', get_string('coursename', 'local_forumnotices'), array('size' => 50, 'disabled' => 'disabled'));
        $mform->setType('coursename', PARAM_RAW);
        $mform->setDefault('coursename', $coursename);

        $mform->addElement('text', 'subject', get_string('subject', 'local_forumnotices'), array('size' => 50));
        $mform->setType('subject', PARAM_RAW);
        $mform->addRule('subject', get_string('required'), 'required', null, 'client');
        
        if (!strcmp($action, 'edit')) {
            $mform->addElement('editor', 'message', get_string('message', 'local_forumnotices'))->setValue(array('text' => $fnrec->message));
        } else {
            $mform->addElement('editor', 'message', get_string('message', 'local_forumnotices'), null);
        }
        $mform->setType('message', PARAM_RAW);
        $mform->addRule('message', get_string('required'), 'required', null, 'client');
        
        $mform->addElement('filepicker', 'attachments', get_string('attachment', 'forum'), null);
        $sql = "SELECT id, name
                FROM {forum} 
                WHERE name NOT IN ('Announcements','Past Notices')
                AND course = $courseid
                ORDER BY name";
        $forumlist = $DB->get_records_sql_menu($sql);
        //$forumlist = $DB->get_records_menu('forum', array('course' => $courseid), 'name', 'id, name');
        $mform->addElement('select', 'forums', get_string('forums', 'local_forumnotices'), $forumlist);
        $mform->getElement('forums')->setMultiple(true);
      
        $mform->addElement('date_time_selector', 'timestart', get_string('displaystart', 'local_forumnotices'), array('optional' => true));
        $mform->addElement('date_time_selector', 'timeend', get_string('displayend', 'local_forumnotices'), array('optional' => true));
        $expirydays = $expiry;
        if ($expirydays) {
            $enddate = time() + ($expirydays * 24 * 60 * 60) ;
            $mform->setDefault('timeend', $enddate);
        }
        
        $mform->addElement('static', 'expirynotice', get_string('expirynotice', 'local_forumnotices'));
        $choices = array(
                   get_string('no'),
                   get_string('yes')
                   );
        $mform->addElement('select', 'pinned', get_string('pinned', 'local_forumnotices'), $choices);
        if (!strcmp($action, 'edit')) {
            $mform->setDefault('subject', $fnrec->subject);
//            $mform->setDefault('message', $posts->message);
            $mform->setDefault('timestart', $fnrec->timemodified);
            $mform->setDefault('timeend', $fnrec->timeend);
            $mform->setDefault('pinned', $fnrec->pinned); 
            $frmarray = explode(",", $fnrec->forums);
            //$mform->getElement('forums')->setSelected(array($frlist));
            $mform->setDefault('forums', $frmarray);
        }
        $buttonarray = array();
        if (!strcmp($action, 'edit')) {
            $buttonarray[] = &$mform->createElement('submit', 'update', get_string('update', 'local_forumnotices'));
        } else {
            $buttonarray[] = &$mform->createElement('submit', 'save', get_string('post', 'local_forumnotices'));
        }
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('cancel', 'local_forumnotices'));
        $mform->addGroup($buttonarray, 'buttonar', '&nbsp;', array(''), false);
    }
}
