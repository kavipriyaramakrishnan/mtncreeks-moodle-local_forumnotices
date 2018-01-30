<?php
/*
 * MtnCreeks Print Student Notices
 *
 * Student notices print screen - Form elements
 *
 * @package    : local_forumnotices
 * @copyright  : 2018 Pukunui
 * @author     : Priya Ramakrishnan, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
/*
 * Class prinforum_form extends moodleform
 */
class printforum_form extends moodleform {
    /*
     * Function Definition to define Form elements
     */
    public function definition() {
        global $DB, $CFG;
        $mform =& $this->_form;
        $courseid = $this->_customdata['courseid'];
        $sql = "SELECT id, name
                FROM {forum}
                WHERE name NOT IN ('Announcements','Past Notices','Staff Notices')
                AND course = $courseid
                ORDER BY name";
        $forumlist = $DB->get_records_sql_menu($sql);
        //$forumlist = $DB->get_records_menu('forum', array('course' => $courseid), 'name', 'id, name');
        $mform->addElement('select', 'forums', get_string('forums', 'local_forumnotices'), $forumlist);
        $mform->getElement('forums')->setMultiple(true);

        $mform->addElement('submit', 'print', get_string('print', 'local_forumnotices'));
    }
}    
