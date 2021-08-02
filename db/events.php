<?php
// This file is part of the Sertifier Certificate module for Moodle - https://moodle.org/
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
 * Privacy Subsystem implementation for mod_sertifier.
 *
 * @package    mod_sertifier
 * @copyright  Sertifier <hr@sertifier.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'includefile' => '/mod/sertifier/lib.php',
        'callback' => 'sertifier_quiz_submission_handler',
        'internal' => false
    ),
    array(
        'eventname'   => '\core\event\course_completed',
        'includefile' => '/mod/sertifier/lib.php',
        'callback'    => 'sertifier_course_completed_handler',
        'internal' => false
    )
);
