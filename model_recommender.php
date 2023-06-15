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
 * Connect with external file.
 *
 * @package     block_recommender
 * @author      2023 Aina Palacios, Laia Subirats, Magali Lescano, Alvaro Martin, JuanCarlo Castillo, Santi Fort
 * @copyright   2022 Eurecat.org <dev.academy@eurecat.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot. '/blocks/recommender/lib.php');
require_once("{$CFG->dirroot}/blocks/recommender/classes/query/courserating.php");


require_login();

/**
 * To call api recommender.
 * @param Mixed $text .
 * @return Mixed .
 */
function predict_recommender($predict2) {
    $makecall = callapifr('POST', 'https://d75rw7c769oxjm63lab.online/recommender', $predict2);
    $response = json_decode($makecall, true);
    return $response;
}

/**
 * To strip tags and lentities from text.
 * @param Mixed $string .
 * @return Mixed .
 */
function clean_recommender($string) {
    $string = mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
    $string = strtolower(strip_tags($string));
    return  preg_replace('/[^a-zA-Z0-9_ -]/s', '', $string); // Removes special chars.
}

/**
 * To call api.
 * @param String $method .
 * @param Mixed $url .
 * @param Mixed $data .
 * @return (String | Bolean).
 */
function callapifr($method, $url, $data) {
    $curl = curl_init();
    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data) {
                  curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                // C curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));.
            }
         break;
        case "PUT":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            if ($data) {
                  curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                // C curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));.
            }
         break;
        default:
            if ($data) {
                $url = sprintf("%s?%s", $url, http_build_query($data));
            }
    }

    // OPTIONS.
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'APIKEY: '.get_config('block_recommender', 'apikey'),
        'Content-Type: application/json',
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    // EXECUTE.
    $result = curl_exec($curl);
    if (!$result) {
        die("Connection Failure");
    }
    curl_close($curl);
    return $result;
}

function get_coursesummary() {
    global $DB;
    $sql = "SELECT c.id, c.fullname, c.summary FROM {course} c";
    $result = $DB->get_records_sql($sql);
    return $result ;
}

function recommenderpython() {
    $courses = get_coursesummary();
    $predict = array();
    foreach ($courses as $c) { 
        $fullname = !empty(trim($c->fullname)) ? clean_recommender($c->fullname) : "";        
        $summary = !empty(trim($c->summary)) ? clean_recommender($c->summary) : "";
        $predict[$c->id] = $fullname . $summary ;
    }
    $predict2 =  json_encode($predict, true);
    $result = predict_recommender($predict2);
    save_related_courses($result);
}

function save_related_courses($result) {

    global $DB;
    $table_name = 'block_recommender_descrip';
    $data = array();
    foreach($result as $key => $value) {
        $courses = array();
        foreach ($value as $item) {
            array_push($courses, $item);
        }

        $record = new stdClass();
        $record->courseid = $key;
        $related_courses_serialized = serialize($courses);
        $record->related_courses = $related_courses_serialized;

        $existing = $DB->get_record($table_name, array('courseid' => $key));

        if ($existing) {
            $record->id = $existing->id;
            $DB->update_record($table_name, $record);
        } else {
            $DB->insert_record($table_name, $record);
        }
    }

}