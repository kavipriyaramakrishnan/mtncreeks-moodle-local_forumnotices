<?php
/*
 * MtnCreeks Forum Notices
 *
 * Notices List screen
 *
 * @package    : local_forumnotices
 * @copyright  : 2018 Pukunui
 * @author     : Priya Ramakrishnan, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require($CFG->dirroot.'/local/forumnotices/locallib.php');
require($CFG->dirroot.'/local/forumnotices/printforum_form.php');
require_login();
$config = get_config('local_forumnotices');
$courseid = $config->courses;
$strtitle  = get_string('printforum', 'local_forumnotices');
$systemcontext = context_system::instance();
$url = new moodle_url('/local/forumnotices/printforum.php');

// Set up PAGE Object.
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($strtitle);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($strtitle);
$PAGE->requires->css("$CFG->dirroot/local/forumnotices/style.css");

$mform = new printforum_form('', array('courseid' => $courseid));

if ($data = $mform->get_data()) {
    $crses = '';
    foreach ($data->forums as $dat => $key) {
       $crses .= $key.',';
    }
    $crslen = strlen($crses);
    $crsid = substr($crses, 0, $crslen-1);
    local_forumnotices_printforum($crsid);
}
echo $OUTPUT->header();
echo $mform->display();
//local_forumnotices_printforum();
echo $OUTPUT->footer();
