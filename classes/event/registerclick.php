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

require_once(__DIR__.'/../../../../config.php');
require_login();

    $idcourse = optional_param('course_id', null, PARAM_INT);
    $iduser = optional_param('user_id', null, PARAM_INT);

    global $DB;

    $precheck = $DB->record_exists('block_recommender_clicks', array('rating' => $iduser));

    // if ($precheck == false) {
    // }
    
    $record = new stdClass();
    $record->userid = $iduser;
    $record->courses = $idcourse;
    $record->timemodified = time();
    $DB->insert_record('block_recommender_clicks', $record);

echo 'sucess';