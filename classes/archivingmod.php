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

use local_archiving\driver\mod\task;

// @codingStandardsIgnoreFile
defined('MOODLE_INTERNAL') || die(); // @codeCoverageIgnore


/**
 * Assignment activity archiving driver
 */
class archivingmod extends \local_archiving\driver\mod\archivingmod {

    public static function get_name(): string {
        return get_string('pluginname', 'archivingmod_assign');
    }

    public static function get_plugname(): string {
        return 'assign';
    }

    public static function get_supported_activities(): array {
        return ['assign'];
    }

    public function get_job_create_form(string $handler, \cm_info $cminfo): \local_archiving\form\job_create_form {
        return new form\job_create_form($handler, $cminfo);
    }

    public function can_be_archived(): bool {
        // TODO: Implement can_be_archived() method.
        return true;
    }

    public function execute_task(task $task): void {
        // TODO: Implement execute_task() method.
    }

}
