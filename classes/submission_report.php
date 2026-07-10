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
 * This file defines the submission report renderer class
 *
 * @package   archivingmod_assign
 * @copyright 2026 Niels Gandraß <niels@gandrass.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace archivingmod_assign;

use core_course\output\activity_icon;
use local_archiving\util\report_util;

// phpcs:ignore
defined('MOODLE_INTERNAL') || die(); // @codeCoverageIgnore


/**
 * Assignment submission report renderer
 *
 * This class handles everything related to getting information for a specific
 * submission out of a given assignment and rendering it as HTML.
 */
class submission_report {
    /**
     * Creates a new assignment submission report renderer
     *
     * @param \stdClass $course Course this attempt renderer is associated with
     * @param \cm_info $cm Course module this renderer is associated with
     * @param \assign $assignment Assignment this submission renderer is associated with
     * @throws \dml_exception If no valid assignment can be found for the given course module
     * @throws \moodle_exception If the given course module is not an assignment
     */
    public function __construct(
        /** @var \stdClass Course this attempt renderer is associated with */
        protected \stdClass $course,
        /** @var \cm_info Course module this attempt renderer is associated with */
        protected \cm_info $cm,
        /** @var \assign Assignment this attempt renderer is associated with */
        protected \assign $assignment
    ) {
        // Check cm.
        if ($this->cm->course != $this->course->id) {
            throw new \moodle_exception('Course module not part of course');
        }
        if ($this->cm->modname !== 'assign') {
            throw new \moodle_exception('Invalid course module type');
        }

        if ($this->cm->instance != $this->assignment->get_instance()->id) {
            throw new \moodle_exception('Invalid assignment instance');
        }
    }

    /**
     * Generates a HTML representation of the assignment submission
     *
     * @param int $submissionid ID of the submission this report is for
     *
     * @return string HTML DOM of the rendered assignment submission report
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function generate(int $submissionid): string {
        global $DB, $OUTPUT;

        // Get and validate submission.
        $submission = $DB->get_record('assign_submission', ['id' => $submissionid], '*', MUST_EXIST);
        if ($submission->assignment != $this->assignment->get_instance()->id) {
            throw new \moodle_exception('Submission must belong to the assignment');
        }

        // Gather further info.
        /** @var \mod_assign\output\renderer $renderer */
        $renderer = $this->assignment->get_renderer();
        $assigninstance = $this->assignment->get_instance();
        $submittinguser = \core_user::get_user($submission->userid, '*', MUST_EXIST);

        // Force rendered comment areas to become static.
        $_GET['nonjscomment'] = true;

        // Render output.
        return $OUTPUT->render_from_template('archivingmod_assign/submissionreport', [
            'course' => [
                'id' => $this->course->id,
                'name' => $this->course->fullname,
            ],
            'assignment' => [
                'id' => $assigninstance->id,
                'title' => $assigninstance->name,
                'description' => format_module_intro('assign', $assigninstance, $this->cm->id),
                'activityinstructions' => $renderer->format_activity_text(
                    assign: $assigninstance,
                    cmid: $this->cm->id,
                ),
                'introattachments' => $this->assignment->render_area_files('mod_assign', ASSIGN_INTROATTACHMENT_FILEAREA, 0),
                'icon' => $OUTPUT->render(activity_icon::from_modname('assign')),
                'dates' => [
                    'opened' => $assigninstance->allowsubmissionsfromdate,
                    'due' => $assigninstance->duedate,
                    'cutoff' => $assigninstance->cutoffdate,
                    'gradingdue' => $assigninstance->gradingduedate,
                ],
            ],
            'submission' => [
                'id' => $submissionid,
                'user' => [
                    'id' => $submittinguser->id,
                    'idnumber' => $submittinguser->idnumber,
                    'picture' => $OUTPUT->render(new \user_picture($submittinguser)),
                    'profilelink' => $OUTPUT->render(new \action_link(
                        new \moodle_url('/user/view.php', ['id' => $submittinguser->id]),
                        fullname($submittinguser, true) . " (#$submittinguser->id)",
                    )),
                ],
                'report' => $this->assignment->view_student_summary($submittinguser, true),
            ],
            'archivingdate' => time(),
        ]);
    }

    /**
     * Like generate() but includes a full page HTML DOM including header and
     * footer
     *
     * @param int $submissionid ID of the submission this report is for
     * @param bool $fixrelativeurls If true, all relative URLs will be
     * forcefully mapped to the Moodle base URL
     * @param bool $minimal If true, unneccessary elements (e.g. navbar) are
     * stripped from the generated HTML DOM
     * @param bool $inlineimages If true, all images will be inlined as base64
     * to prevent rendering issues on user side
     *
     * @return string HTML DOM of the rendered assignment submission report
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \DOMException
     */
    public function generate_full_page(
        int $submissionid,
        bool $fixrelativeurls = true,
        bool $minimal = true,
        bool $inlineimages = true
    ): string {
        global $CFG, $OUTPUT, $PAGE;

        // Add a assignment archiver specific CSS class to provide a unique CSS selector.
        // This can be used to add additional styling to the quiz report page accessed by the worker,
        // for example by specifying additional (s)css in the theme scss setting in the moodle administration.
        $PAGE->add_body_class('assign-archiver-report');

        // Build HTML tree.
        $html = "";
        $html .= $OUTPUT->header();
        $html .= self::generate($submissionid);
        $html .= $OUTPUT->footer();

        // Parse HTML as DOMDocument but supress consistency check warnings.
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        // Patch relative URLs.
        if ($fixrelativeurls) {
            $basenode = $dom->createElement("base");
            $basenode->setAttribute("href", $CFG->wwwroot);
            $dom->getElementsByTagName('head')[0]->appendChild($basenode);
        }

        // Cleanup DOM if desired.
        if ($minimal) {
            // We need to inject custom CSS to hide elements since the DOM generated by.
            // Moodle can be corrupt which causes the PHP DOMDocument parser to die...
            $csshacksnode = $dom->createElement("style", "
                nav.navbar,
                .secondary-navigation {
                    display: none !important;
                }

                .drawer {
                    display: none !important;
                }

                footer {
                    display: none !important;
                }

                div#page,
                div.main-inner {
                    margin: 0 !important;
                    padding: 0 !important;
                    height: initial !important;
                }

                div#page-wrapper {
                    height: initial !important;
                }

                .stackinputerror {
                    display: none !important;
                }

                /* Remove add comment section in submission summary */
                .commentscontainer > h3,
                .commentscontainer > form {
                    display: none !important;
                }

                /* Remove 'View annotated PDF' link on editpdf feedbacks */
                .feedback div[class*='summary_assignfeedback_editpdf_'] > div > a[id*='random'] {
                    display: none !important;
                }

                /* Force code boxes to reflow to page width */
                pre[class*='language-'] {
                    overflow: visible !important;
                    white-space: pre-wrap !important;
                }

                /* Remove padding from codebox comments to prevent them from drawing over student code */
                code .token.comment {
                    padding: 0.5rem !important;
                }
            ");
            $dom->getElementsByTagName('head')[0]->appendChild($csshacksnode);
        }

        // Convert all local images to base64 if desired.
        if ($inlineimages) {
            $wwwroot = get_config('archivingmod_assign')->internal_wwwroot ?: null;
            foreach ($dom->getElementsByTagName('img') as $img) {
                if (!report_util::convert_image_to_base64($img, $wwwroot)) {
                    $img->setAttribute('x-debug-inlining-failed', 'true');
                }
            }
        }

        return $dom->saveHTML();
    }
}
