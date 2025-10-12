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
 * Defines the job creation form
 *
 * @package    archivingmod_assign
 * @copyright  2025 Niels Gandraß <niels@gandrass.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace archivingmod_assign\form;

defined('MOODLE_INTERNAL') || die(); // @codeCoverageIgnore

require_once($CFG->dirroot . '/lib/formslib.php'); // @codeCoverageIgnore


/**
 * Form to initiate a new assignment archive job
 */
class job_create_form extends \local_archiving\form\job_create_form {
    /**
     * Defines header elements in form
     *
     * @return void
     * @throws \coding_exception
     */
    #[\Override]
    protected function definition_header(): void {
        global $OUTPUT;

        parent::definition_header();

        // Add WIP warning.
        $this->_form->addElement('html', $OUTPUT->notification(
            'The assignment archving driver is not yet implemented! It will only return a stub file.',
            \core\output\notification::NOTIFY_WARNING
        ));
    }
}
