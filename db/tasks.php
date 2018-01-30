<?php
/*
 * MtnCreeks Forum Notices
 *
 * Scheduled task Definition
 *
 * @package    : local_forumnotices
 * @copyright  : 2017 Pukunui
 * @author     : Priya Ramakrishnan, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$tasks = array(
             array(
                 'classname' => 'local_forumnotices\task\expirynotices',
                 'blocking'  => 0,
                 'minute'    => 15,
                 'hour'      => '0',
                 'day'       => '*',
                 'dayofweek' => '*',
                 'month'     => '*'
             )
         );
