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

// phpcs:ignore
defined('MOODLE_INTERNAL') || die(); // @codeCoverageIgnore

global $CFG;
require_once($CFG->dirroot . '/mod/assign/locallib.php');


/**
 * Tests generator for the archivingmod_assign plugin
 *
 * @package   archivingmod_assign
 * @copyright 2026 Niels Gandraß <niels@gandrass.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class archivingmod_assign_generator extends \testing_data_generator {
    /**
     * Creates a test course with an assignment activity, a teacher, and a student.
     *
     * Both users are enrolled in the course. The assignment has online text
     * submissions enabled and no submission drafts.
     *
     * @return \stdClass Object with properties: course, cm, assignment, teacher, student
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function create_assignment(): \stdClass {
        // Create entities.
        $course = $this->create_course();
        $teacher = $this->create_user();
        $student = $this->create_user();

        $this->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->enrol_user($student->id, $course->id, 'student');

        // Create assignment.
        /** @var \mod_assign_generator $assigngen */
        $assigngen = $this->get_plugin_generator('mod_assign');
        $assignment = $assigngen->create_instance([
            'course'                              => $course->id,
            'assignsubmission_onlinetext_enabled' => 1,
            'submissiondrafts'                    => 0,
        ]);

        // Build response.
        [$course, $cm] = get_course_and_cm_from_cmid($assignment->cmid, 'assign');

        return (object) [
            'course'     => $course,
            'cm'         => $cm,
            'assignment' => $assignment,
            'teacher'    => $teacher,
            'student'    => $student,
        ];
    }

    /**
     * Creates a test course with an assignment activity and one submitted online
     * text submission from the student.
     *
     * Internally calls {@see create_assignment()} and adds a submission on top.
     *
     * @param string|null $submissiontext Optional text to use for the submission. If null, a default text will be used.
     * @return \stdClass Object with properties: course, cm, assignment, teacher, student, submission
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function create_assignment_with_text_submission(?string $submissiontext = null): \stdClass {
        global $DB;

        $testdata = $this->create_assignment();

        // Add a text submission to the prepared assignment.
        /** @var \mod_assign_generator $assigngen */
        $assigngen = $this->get_plugin_generator('mod_assign');
        $assigngen->create_submission([
            'cmid'       => $testdata->assignment->cmid,
            'userid'     => $testdata->student->id,
            'onlinetext' => $submissiontext ?? 'Test submission text.',
            'status'     => ASSIGN_SUBMISSION_STATUS_SUBMITTED,
        ]);

        // Prepare response.
        $submission = $DB->get_record(
            'assign_submission',
            ['assignment' => $testdata->assignment->id, 'userid' => $testdata->student->id],
            '*',
            MUST_EXIST
        );

        return (object) [
            'course'     => $testdata->course,
            'cm'         => $testdata->cm,
            'assignment' => $testdata->assignment,
            'teacher'    => $testdata->teacher,
            'student'    => $testdata->student,
            'submission' => $submission,
        ];
    }
}
