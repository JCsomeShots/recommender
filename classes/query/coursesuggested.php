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

require_once("$CFG->libdir/dml/pgsql_native_moodle_database.php");

defined('MOODLE_INTERNAL') || die;


function suggested_table() {

    global $DB, $USER;
    $current_user_id = $USER->id;
    // $categoryid = 1;
    // $categoryid = 42;
    $sql = "SELECT c.id as courseid, c.fullname, c.summary
            FROM {course} c
            JOIN {block_recommender_suggested} br ON br.courseid = c.id 
            -- WHERE NOT EXISTS (
            --     SELECT 1
            --     FROM {user_enrolments} ue
            --     JOIN {enrol} e ON e.id = ue.enrolid
            --     WHERE e.courseid = c.id
            --     AND ue.userid = :current_user_id
            -- )
            -- WHERE c.category = :categoryid
            ORDER BY c.id ASC
            -- LIMIT 3
            ";
    $params = [
        'current_user_id' => $current_user_id,
        // 'categoryid' => $categoryid
    ];

    $results = $DB->get_records_sql($sql, $params);
    if (count($results) > 3) {
        shuffle($results);
        $results = array_slice($results, 0, 3);
    }
    return $results;
}
