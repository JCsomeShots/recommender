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



function get_related_courses() {
    global $USER, $DB;

    $userid = $USER->id;
    $courseids = array();
    $related_courses = array();
    $courses = array();

    if (isset($userid) && is_numeric($userid)) { 
        $courses = enrol_get_users_courses($userid, true);
    }

    if (!empty($courses)) {
        // var_dump($courses);
        foreach ($courses as $course) {
            // var_dump($course->id);
            // var_dump($course->fullname);
            $courseids[] = $course->id;
    
            if ($description = $DB->get_record('block_recommender_descrip', array('courseid' => $course->id))) {
                $related_courses_course = explode(',', $description->related_courses);
                $related_courses_course = array_slice($related_courses_course, 0, 5); 
                foreach ($related_courses_course as $related_course) {
                    array_push($related_courses, $related_course);
                }
            }
        }
    
        $related_courses = array_unique($related_courses);
        $related_course_arr = unserialize($related_course);
        $related_course_arr = array_map('intval', $related_course_arr);
        // var_dump($related_course_arr);
        $courseids = array_map('intval', $courseids);
        // var_dump($courseids);
    
        $related_courses = array_diff($related_course_arr, $courseids);
    
        // print_object($related_courses);
        $result = get_courses_list($related_courses);
        // print_object($result);
        return $result;
    } else {
        $result = [];
        return $result;
    }

}


function get_courses_list($courses){
    global $DB;
    // var_dump($courses);
    // var_dump(gettype($courses));
    $course_list = $DB->get_records_list('course', 'id', $courses);
    // shuffle($course_list);
    // print_object($course_list);
    return $course_list;

}
