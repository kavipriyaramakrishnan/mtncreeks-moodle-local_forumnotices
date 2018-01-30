<?php
/*
 * MtnCreeks Forum Notices
 *
 * Class definition for schedule task
 *
 * @package    : local_forumnotices
 * @copyright  : 2017 Pukunui
 * @author     : Priya Ramakrishnan, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_forumnotices\task;

require_once($CFG->dirroot.'/local/forumnotices/lib.php');

/*
 * Extend core scheduled task
 */
class expirynotices extends \core\task\scheduled_task {
    /**
     * Return name of th task
     *
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'local_forumnotices');
    }
    /**
     * Perform the task
     */
    public function execute() {
        local_forumnotices_expirynotices('auto');
    }
}
