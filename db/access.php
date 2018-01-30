<?php
/*
 * MtnCreeks Forum Notices
 *
 * Defining Capabilities
 *
 * @package    : local_forumnotices
 * @copyright  : 2017 Pukunui
 * @author     : Priya Ramakrishnan, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$capabilities = array(
    'local/forumnotices:view' => array(
         'riskbitmask' => RISK_DATALOSS,
         'captype'     => 'read',
         'contextlevel' => CONTEXT_SYSTEM,
         'archetypes' => array(
             'manager' => CAP_ALLOW,
             'student' => CAP_PREVENT,
         )
    )
);
