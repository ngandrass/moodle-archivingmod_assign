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

/**
 * Tests for the submission_report class
 *
 * @package   archivingmod_assign
 * @copyright 2026 Niels Gandraß <niels@gandrass.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace archivingmod_assign;


/**
 * Tests for the submission_report class
 */
final class submission_report_test extends \advanced_testcase {
    /**
     * Returns the data generator for the archivingmod_assign plugin
     *
     * @return \archivingmod_assign_generator The data generator for the archivingmod_assign plugin
     */
    // phpcs:ignore
    public static function getDataGenerator(): \archivingmod_assign_generator {
        return parent::getDataGenerator()->get_plugin_generator('archivingmod_assign');
    }

    /**
     * Tests that a submission_report instance can be constructed with valid arguments.
     *
     * @covers \archivingmod_assign\submission_report
     *
     * @return void
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_create_submission_report(): void {
        $this->resetAfterTest();
        $testdata = $this::getDataGenerator()->create_assignment();

        $ctx = \context_module::instance($testdata->cm->id);
        $assign = new \assign($ctx, $testdata->cm, $testdata->course);

        $report = new submission_report($testdata->course, $testdata->cm, $assign);
        $this->assertInstanceOf(submission_report::class, $report);
    }

    /**
     * Tests that the constructor throws a moodle_exception when the course module
     * does not belong to the given course.
     *
     * @covers \archivingmod_assign\submission_report
     *
     * @return void
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_create_submission_report_throws_for_mismatched_course(): void {
        $this->resetAfterTest();
        $testdata1 = $this::getDataGenerator()->create_assignment();
        $testdata2 = $this::getDataGenerator()->create_assignment();

        $ctx = \context_module::instance($testdata2->cm->id);
        $assign = new \assign($ctx, $testdata2->cm, $testdata2->course);

        $this->expectException(\moodle_exception::class);
        new submission_report($testdata1->course, $testdata2->cm, $assign);
    }

    /**
     * Tests that the constructor throws a moodle_exception when the assignment
     * instance does not match the course module instance.
     *
     * @covers \archivingmod_assign\submission_report
     *
     * @return void
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_create_submission_report_throws_for_mismatched_assignment(): void {
        $this->resetAfterTest();
        $testdata1 = $this::getDataGenerator()->create_assignment();
        $testdata2 = $this::getDataGenerator()->create_assignment();

        $ctx1 = \context_module::instance($testdata1->cm->id);
        $ctx2 = \context_module::instance($testdata2->cm->id);
        $assign2 = new \assign($ctx2, $testdata2->cm, $testdata2->course);

        $this->expectException(\moodle_exception::class);
        new submission_report($testdata1->course, $testdata1->cm, $assign2);
    }

    /**
     * Tests that generate() includes key submission metadata in the rendered HTML.
     *
     * @covers \archivingmod_assign\submission_report
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_generate_contains_expected_content(): void {
        $this->resetAfterTest();
        $testdata = $this::getDataGenerator()->create_assignment_with_text_submission();

        $ctx = \context_module::instance($testdata->cm->id);
        $assign = new \assign($ctx, $testdata->cm, $testdata->course);

        $report = new submission_report($testdata->course, $testdata->cm, $assign);
        $html = $report->generate($testdata->submission->id);

        $this->assertStringContainsString(
            $testdata->assignment->name,
            $html,
            'Assignment title not found in rendered HTML'
        );
        $this->assertStringContainsString(
            $testdata->course->fullname,
            $html,
            'Course name not found in rendered HTML'
        );
        $this->assertStringContainsString(
            (string) $testdata->submission->id,
            $html,
            'Submission ID not found in rendered HTML'
        );
        $this->assertStringContainsString(
            (string) $testdata->student->id,
            $html,
            'Student user ID not found in rendered HTML'
        );
    }

    /**
     * Tests that generate() throws a moodle_exception when the submission belongs
     * to a different assignment.
     *
     * @covers \archivingmod_assign\submission_report
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_generate_throws_for_wrong_assignment(): void {
        $this->resetAfterTest();
        $testdata1 = $this::getDataGenerator()->create_assignment_with_text_submission();
        $testdata2 = $this::getDataGenerator()->create_assignment();

        $ctx = \context_module::instance($testdata2->cm->id);
        $assign = new \assign($ctx, $testdata2->cm, $testdata2->course);

        $report = new submission_report($testdata2->course, $testdata2->cm, $assign);

        $this->expectException(\moodle_exception::class);
        $report->generate($testdata1->submission->id);
    }

    /**
     * Tests that generate_full_page() returns non-empty HTML for a valid submission.
     *
     * @covers \archivingmod_assign\submission_report
     *
     * @return void
     * @throws \DOMException
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_generate_full_page_stub(): void {
        $this->resetAfterTest();
        $testdata = $this::getDataGenerator()->create_assignment_with_text_submission();

        $ctx = \context_module::instance($testdata->cm->id);
        $assign = new \assign($ctx, $testdata->cm, $testdata->course);

        $report = new submission_report($testdata->course, $testdata->cm, $assign);
        $html = $report->generate_full_page(
            $testdata->submission->id,
            false, // We need to disable this since $OUTPUT->header() is not working during tests.
            false, // We need to disable this since $OUTPUT->header() is not working during tests.
            true
        );

        $this->assertNotEmpty($html, 'Generated full page report is empty');
    }
}
