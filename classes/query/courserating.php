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
 * To get records from course rating if this exists. 
 * @return Mixed $result with the average course from tool rating 
 */
function courserating() {

    global $DB;
    $sql = "SELECT cr.courseid, AVG(cr.rating) as avg_rating 
            FROM {tool_courserating_rating} cr 
            GROUP BY cr.courseid";
    
    if ($DB->get_manager()->table_exists('tool_courserating_rating')) {
    
        if ($DB->count_records('userid') != 0) {
            $results = $DB->get_records_sql($sql);
        } 
    } 
    
    return $results = $results ?: 0;
}

