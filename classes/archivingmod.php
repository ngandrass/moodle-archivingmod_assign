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
 * Assignment activity archiving driver
 *
 * @package     archivingmod_assign
 * @copyright   2025 Niels Gandraß <niels@gandrass.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace archivingmod_assign;

use context_system;
use local_archiving\activity_archiving_task;
use local_archiving\exception\yield_exception;
use local_archiving\type\activity_archiving_task_status;

// @codingStandardsIgnoreFile
defined('MOODLE_INTERNAL') || die(); // @codeCoverageIgnore


/**
 * Assignment activity archiving driver
 */
class archivingmod extends \local_archiving\driver\archivingmod {

    #[\Override]
    public static function get_supported_activities(): array {
        return ['assign'];
    }

    #[\Override]
    public function get_job_create_form(string $handler, \cm_info $cminfo): \local_archiving\form\job_create_form {
        return new form\job_create_form($handler, $cminfo);
    }

    #[\Override]
    public function can_be_archived(): bool {
        // TODO: Implement can_be_archived() method.
        return true;
    }

    #[\Override]
    public function execute_task(activity_archiving_task $task): void {
        // TODO: Implement execute_task() method.
        try {
            if ($task->get_status(usecached: true) == activity_archiving_task_status::UNINITIALIZED) {
                $task->set_status(activity_archiving_task_status::CREATED);
            }

            if ($task->get_status(usecached: true) == activity_archiving_task_status::CREATED) {
                $task->set_status(activity_archiving_task_status::AWAITING_PROCESSING);
            }

            if ($task->get_status(usecached: true) == activity_archiving_task_status::AWAITING_PROCESSING) {
                $task->set_status(activity_archiving_task_status::RUNNING);
                throw new yield_exception();
            }

            if ($task->get_status(usecached: true) == activity_archiving_task_status::RUNNING) {
                // Create a stub file...
                $fs = get_file_storage();
                $file = $fs->create_file_from_string([
                    'contextid' => context_system::instance()->id,
                    'component' => 'archivingmod_assign',
                    'filearea' => 'draft',
                    'itemid' => 0,
                    'filepath' => '/',
                    'filename' => 'artifact.txt',
                ], "Hello world at ".userdate(time()));
                $task->link_artifact($file, takeownership: true);

                $task->set_status(activity_archiving_task_status::FINALIZING);
            }

            if ($task->get_status(usecached: true) == activity_archiving_task_status::FINALIZING) {
                $task->set_status(activity_archiving_task_status::FINISHED);
            }
        } catch (\Exception $e) {
            // Catch the yield silently and let everything else bubble up.
            if (!$e instanceof yield_exception) {
                $task->set_status(activity_archiving_task_status::FAILED);
                $task->get_logger()->error($e->getMessage());
                throw $e;
            }
        }
    }

}
