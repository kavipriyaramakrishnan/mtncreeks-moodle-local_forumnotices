<?php
/*
 * MtnCreeks Forum Notices
 *
 * Notices List screen
 *
 * @package    : local_forumnotices
 * @copyright  : 2017 Pukunui
 * @author     : Priya Ramakrishnan, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require($CFG->dirroot.'/local/forumnotices/locallib.php');
require_login();
$strtitle  = get_string('forumlist', 'local_forumnotices');
$systemcontext = context_system::instance();
$url = new moodle_url('/local/forumnotices/forumlist.php');

// Set up PAGE Object.
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($strtitle);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($strtitle);
echo $OUTPUT->header();
local_forumnotices_list();
echo $OUTPUT->footer();
//exit();
