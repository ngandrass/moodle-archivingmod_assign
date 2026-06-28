<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here
 *
 * @package     archivingmod_assign
 * @category    string
 * @copyright   2025 Niels Gandraß <niels@gandrass.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// @codingStandardsIgnoreFile

$string['cutoffdate'] = 'Cut-off date';
$string['duedate'] = 'Due date';
$string['gradingduedate'] = 'Grading due date';
$string['openingdate'] = 'Opening date';
$string['pluginname'] = 'Assignment';
$string['privacy:metadata'] = 'TODO';
$string['setting_enabled'] = 'Enabled';
$string['setting_enabled_desc'] = 'Enables or disables this activity archiving driver. If disabled, no activities can be archived using this driver.';
$string['setting_header_archive_worker'] = 'Archive Worker Service';
$string['setting_header_archive_worker_desc'] = 'Configuration of the archive worker service and the Moodle web service it uses.';
$string['setting_internal_wwwroot'] = 'Custom Moodle base URL';
$string['setting_internal_wwwroot_desc'] = 'Overwrites the default Moodle base URL (<code>$CFG->wwwroot</code>) inside generated assignment submission reports. This can be useful if you are running the archiving worker service inside a private network (e.g., Docker) and want it to access Moodle directly.<br/>Example: <code>http://moodle/</code>';
$string['setting_webservice_enabler'] = 'Moodle web services';
$string['setting_webservice_enabler_desc'] = 'This plugin uses Moodle web services to communicate with the worker service. Therefore, web services and the REST protocol must be enabled for this plugin to work. You can check the current status below. If everything reads green, you are ready to go.';
$string['setting_worker_url'] = 'Archive worker URL';
$string['setting_worker_url_desc'] = 'URL of the archive worker service to call for assignment archiving task execution. If you only want to try the Assignment Archiver, you can use the <a href="https://quizarchiver.gandrass.de/installation/archiveworker/#using-the-free-public-demo-service" target="_blank">free public demo archive worker service</a>, eliminating the need to set up your own worker service right away.<br/>Example: <code>http://127.0.0.1:8080</code> or <code>http://moodle-archive-worker:8080</code>';
