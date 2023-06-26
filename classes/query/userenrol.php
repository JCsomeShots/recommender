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

/**
 * Retrieve and print information about user enrollments in courses
 */
function getquery() {
    global $DB;

    // Build SQL query to retrieve user enrollments, course information, and enrollment date
    $sql = "SELECT ue.userid as userid, ue.enrolid, ue.timecreated, c.fullname, c.id as courseid, c.summary
            FROM {user_enrolments} ue
            JOIN {enrol} e ON e.id = ue.enrolid
            JOIN {course} c ON c.id = e.courseid";

    // Execute SQL query and retrieve results as an array of objects
    $result = $DB->get_records_sql($sql);
    return $result;

}

/**
 * Like a reference. To provide information to the recommender.
 */
function printquery() {
    $result = getquery();
    // Iterate over each result and print the user ID, course name, enrollment ID, enrollment date, and summary
    foreach ($result as $row) {
        echo "the user ID " . $row->userid . " is enrolled in " . $row->fullname . " (enrollment ID: " . $row->enrolid . ") on " . date('Y-m-d', $row->timecreated) . ", with this description: " . $row->summary . "<br>";
    }
}


/**
 * To give this courses that the user is not enrol.
 * @return Array $results 
 */
function notenrol(){
    global $DB, $USER;

    $current_user_id = $USER->id;
    // $categoryid = 1;
    $categoryid = 42;

    // Consulta SQL modificada para obtener los cursos a los que el usuario no está enrolado
    $sql = "SELECT c.id as courseid, c.fullname, c.summary
            FROM {course} c
            WHERE NOT EXISTS (
                SELECT 1
                FROM {user_enrolments} ue
                JOIN {enrol} e ON e.id = ue.enrolid
                WHERE e.courseid = c.id
                AND ue.userid = :current_user_id   
                
            ) AND c.category = :categoryid
            ORDER BY RAND()
            LIMIT 3";
    $params = [
        'current_user_id' => $current_user_id,
        'categoryid' => $categoryid
    ];

    // Ejecutar la consulta SQL y obtener los resultados como un array de objetos
    $results = $DB->get_records_sql($sql, $params);
// var_dump($results);
    return $results;
}

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

/**
 * Returns the course with the most enrolments.
 * This function iterates through all the courses in Moodle and counts the number of users enrolled in each course. It then returns the course with the highest enrolment count.
 * @return object The course object with the most enrolments.
 */
function get_course_with_most_enrolments() {
    global $DB;

    // Get all courses in Moodle.
    $courses = $DB->get_records('course');

    // Initialize variables to keep track of the course with the most enrolments.
    $max_enrolments = 0;
    $max_enrolments_course = null;

    // Iterate through all courses and count the number of users enrolled in each course.
    foreach ($courses as $course) {
        $enrolments_count = $DB->count_records('user_enrolments', array('enrolid' => $course->id));
        if ($enrolments_count > $max_enrolments) {
            $max_enrolments = $enrolments_count;
            $max_enrolments_course = $course;
        }
    }

    // Return the course with the most enrolments.
    return $max_enrolments_course;
}

/**
 * Devuelve una matriz con los 3 cursos con más inscripciones.
 * Esta función recorre todos los cursos en Moodle y cuenta el número de inscripciones para cada curso. Luego devuelve una matriz con los 3 cursos con el recuento de inscripción más alto.
 * @return array La matriz con los 3 cursos con más inscripciones.
 */
function get_top_courses_by_enrolment() {
    global $DB;

    // Obtener todos los cursos en Moodle.
    $courses = $DB->get_records('course');

    // Inicializar una matriz para realizar un seguimiento de los 3 cursos con más inscripciones.
    $top_courses = array();

    // Iterar a través de todos los cursos y contar el número de inscripciones para cada curso.
    foreach ($courses as $course) {
        $enrolments_count = $DB->count_records('enrol', array('courseid' => $course->id));
        $top_courses[$course->id] = $enrolments_count;
    }

    // Ordenar la matriz en orden descendente según el recuento de inscripciones.
    arsort($top_courses);

    // Obtener los 3 cursos con más inscripciones.
    $top_courses = array_slice($top_courses, 0, 3, true);

    // Obtener los objetos de curso correspondientes a los 3 cursos con más inscripciones.
    $top_courses_objects = array();
    foreach ($top_courses as $courseid => $enrolments_count) {
        $course_object = $DB->get_record('course', array('id' => $courseid));
        $top_courses_objects[] = $course_object;
    }

    // Devolver la matriz con los 3 cursos con más inscripciones.
    return $top_courses_objects;
}


/**
 * Returns the course with the most users who have completed the course.
 * This function loops through all the courses in Moodle and counts the number of users who have completed each course. It then returns the course with the highest completion count.
 * @return object The course object with the most users who have completed the course.
 */
function get_course_with_most_completed_users() {
    global $DB;

    // Get all courses in Moodle.
    $courses = $DB->get_records('course');

    // Initialize variables to track the course with the most users who have completed the course.
    $max_completed_users = 0;
    $max_completed_users_course = null;

    // Iterate through all courses and count the number of users who have completed each course.
    foreach ($courses as $course) {
        $completed_users_count = $DB->count_records_sql("SELECT COUNT(DISTINCT userid) FROM {course_completions} WHERE course = ?", array($course->id));
        if ($completed_users_count > $max_completed_users) {
            $max_completed_users = $completed_users_count;
            $max_completed_users_course = $course;
        }
    }

    // Return the course with the most users who have completed the course.
    return $max_completed_users_course;
}

/**
 * Devuelve una matriz con los 3 cursos con más usuarios que han completado el curso.
 * Esta función recorre todos los cursos en Moodle y cuenta el número de usuarios que han completado el curso. Luego devuelve una matriz con los 3 cursos con el recuento de finalización más alto.
 * @return array La matriz con los 3 cursos con más usuarios que han completado el curso.
 */
function get_top_courses_by_completion() {
    global $DB;

    // Obtener todos los cursos en Moodle.
    $courses = $DB->get_records('course');

    // Inicializar una matriz para realizar un seguimiento de los 3 cursos con más usuarios que han completado el curso.
    $top_courses = array();

    // Iterar a través de todos los cursos y contar el número de usuarios que han completado el curso.
    foreach ($courses as $course) {
        $completion_count = $DB->count_records_sql("SELECT COUNT(DISTINCT u.id) FROM {user} u JOIN {user_enrolments} ue ON ue.userid = u.id JOIN {course_completions} cc ON cc.userid = u.id AND cc.course = ue.courseid WHERE ue.courseid = ?", array($course->id));
        $top_courses[$course->id] = $completion_count;
    }

    // Ordenar la matriz en orden descendente según el recuento de finalización.
    arsort($top_courses);

    // Obtener los 3 cursos con más usuarios que han completado el curso.
    $top_courses = array_slice($top_courses, 0, 3, true);

    // Obtener los objetos de curso correspondientes a los 3 cursos con más usuarios que han completado el curso.
    $top_courses_objects = array();
    foreach ($top_courses as $courseid => $completion_count) {
        $course_object = $DB->get_record('course', array('id' => $courseid));
        $top_courses_objects[] = $course_object;
    }

    // Devolver la matriz con los 3 cursos con más usuarios que han completado el curso.
    return $top_courses_objects;
}


function suggested_table() {

    global $DB, $USER;
    $current_user_id = $USER->id;
    $categoryid = 1;
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
        'categoryid' => $categoryid
    ];

    $results = $DB->get_records_sql($sql, $params);
    if (count($results) > 3) {
        shuffle($results);
        $results = array_slice($results, 0, 3);
    }
    return $results;
}
