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


function updaterecommender($iduser, $idcourse){
    global $DB;
    $precheck  = precheck($iduser, $idcourse);
    $enrol = enrol($iduser, $idcourse);
    $timeenrol = getquery();

    if ($precheck && !empty($enrol)  ) {

        $record = new stdClass();
        $record->userid = $iduser;
        $record->courseid = $idcourse;
        $record->timecreated = time();
        $record->enrol = 1;
        $DB->insert_record('block_recommender_clicks', $record);
    }
}