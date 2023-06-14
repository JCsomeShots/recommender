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

function precheck( $iduser , $idcourse) {
    global $DB;
    return $DB->record_exists('block_recommender_clicks', array('userid' => $iduser, 'courseid' => $idcourse));
}

function enrol( $iduser , $idcourse) {
    global $DB;

    $sql =  $sql = "SELECT ue.userid, ue.enrolid, ue.timecreated, c.fullname, c.summary
    FROM {user_enrolments} ue
    JOIN {enrol} e ON e.id = ue.enrolid
    JOIN {course} c ON c.id = e.courseid
    WHERE userid = ? AND c.id = ?";
    $params = array( $iduser , $idcourse);
    return $DB->get_record_sql($sql, $params);
}

function best_ratingcourse() {
    global $DB;

    // Obtener los 3 cursos con más estudiantes inscritos
    $sql = "SELECT COUNT(DISTINCT ue.userid) AS num_students, c.id AS course_id, c.fullname, c.summary
    FROM {user_enrolments} ue
    JOIN {enrol} e ON ue.enrolid = e.id
    JOIN {course} c ON c.id = e.courseid
    WHERE c.visible = 1
    GROUP BY c.id
    ORDER BY num_students DESC
    LIMIT 3";
    $courses = $DB->get_records_sql($sql);

    return $courses;
    // Imprimir los resultados
    // foreach ($courses as $course) {
    // echo "El curso " . $course->course_name . " con ID " . $course->course_id . " tiene " . $course->num_students . " estudiantes inscritos. <br>";
    // }
}

// function get_top_completed_courses() {
//     global $DB;

//     // Get all the visible courses
//     $courses = $DB->get_records('course', ['visible' => 1]);

//     $completed_courses = [];

//     // Calculate the completion percentage for each course
//     foreach ($courses as $course) {
//         // Get the completion percentage for the course
//         $completion_percentage = course_completion_percentage($course);

//         // Add the completion percentage to the course object
//         $course->completion_percentage = $completion_percentage;

//         // Add the course to the completed_courses array
//         $completed_courses[] = $course;
//     }

//     // Sort the completed courses array in descending order of completion percentage
//     usort($completed_courses, function($a, $b) {
//         return $b->completion_percentage - $a->completion_percentage;
//     });

//     // Return the top 3 completed courses
//     echo array_slice($completed_courses, 0, 3);
// }

// Helper function to calculate course completion percentage
// function course_completion_percentage($course) {
//     global $DB;

//     $cm = get_coursemodule_from_instance('course', $course->id);
//     $completion = new \completion_info($course);
//     $completion_data = $completion->get_data($cm);
//     $completion_percentage = 0;

//     if (!empty($completion_data)) {
//         $completion_percentage = $completion_data->completionstate;
//     }

//     return $completion_percentage;
// }

function add_recommended_block() {
    global $DB, $CFG;

    require_once($CFG->dirroot.'/course/lib.php');
    $courseids = $DB->get_fieldset_select('course', 'id', '1=1');

    foreach ($courseids as $courseid) {
        $course = \core_course_selector::load_course_by_id($courseid);
        $instance = block_instance('html');
        $instance->config = array('content' => 'recommender');
        blocks_add_block_to_course($instance, $course);
        course_set_display($course);
    }
}