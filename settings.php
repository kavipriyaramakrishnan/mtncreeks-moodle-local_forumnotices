<?php
/*
 * MtnCreeks Forum Notices
 *
 * Settings page
 *
 * @package    : local_forumnotices
 * @copyright  : 2017 Pukunui
 * @author     : Priya Ramakrishnan, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


$settings = new admin_settingpage('local_forumnotices', 
                                   new lang_string('pluginname', 'local_forumnotices'), 
                                   'local/forumnotices:view');
$courses = $DB->get_records_menu('course', array(), 'fullname', 'id, fullname');
$settings->add(new admin_setting_configselect(
               'local_forumnotices/courses',
               new lang_string('selectcourse', 'local_forumnotices'),
               '',
               '',
               $courses));
                
$settings->add(new admin_setting_configtext(
               'local_forumnotices/expiry',
               new lang_string('forumexpiry', 'local_forumnotices'),
               '',
               '',
               PARAM_INT,
               40
               ));

$ADMIN->add('localplugins', $settings);
