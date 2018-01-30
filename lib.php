<?php
/*
 * CRON function.
 *
 * @uses $DB
 */
function local_forumnotices_expirynotices() {
   global $DB, $CFG;
   mtrace('Forumnotices CRON started');
   $lasttimecron = get_config('local_forumnotices', 'lastcron');
   $config = get_config('local_forumnotices');
   $courseid = $config->courses;
   $time = time();
   if ($lasttimecron = 0) {
       $lasttimecron = time();
   }
   $sql = "SELECT *
           FROM {local_forumnotices} fn
           WHERE fn.timeend <= $time";
   $fnlist = $DB->get_records_sql($sql);
    
   $sql = "SELECT id
           FROM {forum} 
           WHERE course = $courseid
           AND name = 'Past Notices'";
   $pastnoticeid = $DB->get_field_sql($sql);
   mtrace('Updating forum discussions');
   foreach ($fnlist as $fn) {
      $postarray = explode(",", $fn->postid);
      $discussarray = explode(",", $fn->discussionid);
      // Delete Post record.
      /*foreach ($postarray as $pak => $pad) {
         $records = new stdClass();
         $records->id = $pad;
         $DB->delete_records('forum_posts', $records);
      }*/
      // Delete discussion records.
      foreach ($discussarray as $dsk => $dsd) {
         $records = new stdClass();
         $records->id = $dsd;
         $records->forum = $pastnoticeid;
         $DB->update_record('forum_discussions', $records);
      }
      // Delete froum notices records.
      $rec = new stdClass();
      $rec->id = $fn->id;
      $rec->forums = $pastnoticeid;
      $rec->pastnotices = 1;
      $DB->update_record('local_forumnotices', $rec);
   }
   set_config('lastcron', $time);
   mtrace('ENd of forumnotices CRON');
}
