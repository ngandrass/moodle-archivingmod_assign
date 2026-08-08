<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

// FIXME: THIS FILE IS FOR DEVELOPMENT / DEBUGGING ONLY.

// FIXME: REMOVE BEFORE RELEASE!!!
// FIXME: REMOVE BEFORE RELEASE!!!
// FIXME: REMOVE BEFORE RELEASE!!!
// FIXME: REMOVE BEFORE RELEASE!!!
// FIXME: REMOVE BEFORE RELEASE!!!
// FIXME: REMOVE BEFORE RELEASE!!!
// FIXME: REMOVE BEFORE RELEASE!!!
// FIXME: REMOVE BEFORE RELEASE!!!
// FIXME: REMOVE BEFORE RELEASE!!!

use archivingmod_assign\assignment_manager;

require_once('../../../../../config.php');

$am = new assignment_manager(28, 92);
require_login($am->get_course());

$url = new moodle_url('/local/archiving/mod/assign/foo.php');
$PAGE->set_url($url);
//$PAGE->set_context($am->get_cm()->context);

$report = new \archivingmod_assign\submission_report($am->get_course(), $am->get_cm(), $am->get_assignment());
echo $report->generate_full_page(5);

echo "<pre>";
print_r($am->get_submission_attachments_metadata(5));
echo "</pre>";

