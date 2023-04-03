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
 * Lib in the block.
 *
 * @package     block_recommender
 * @author      2023 JuanCarlo Castillo <juancarlo.castillo20@gmail.com>
 * @copyright   2023 JuanCarlo Castillo & Eurecat.dev
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die;

/**
 * Retrieve and print information about user enrollments in courses
 */
function getquery() {
    global $DB;

    // Build SQL query to retrieve user enrollments, course information, and enrollment date
    $sql = "SELECT ue.userid, ue.enrolid, ue.timecreated, c.fullname, c.summary
            FROM {user_enrolments} ue
            JOIN {enrol} e ON e.id = ue.enrolid
            JOIN {course} c ON c.id = e.courseid";

    // Execute SQL query and retrieve results as an array of objects
    $result = $DB->get_records_sql($sql);
    return $result;

}

function printquery() {
    $result = getquery();
    // Iterate over each result and print the user ID, course name, enrollment ID, enrollment date, and summary
    foreach ($result as $row) {
        echo "the user ID " . $row->userid . " is enrolled in " . $row->fullname . " (enrollment ID: " . $row->enrolid . ") on " . date('Y-m-d', $row->timecreated) . ", with this description: " . $row->summary . "<br>";
    }
}
