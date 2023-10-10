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
 * Most popular
 *
 * @package     block_recommender
 * @author      2023 JuanCarlo Castillo <juancarlo.castillo20@gmail.com>
 * @copyright   2023 JuanCarlo Castillo & Eurecat.dev
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



function best_ratingcourse() {
    global $DB;

    // Obtener los 3 cursos con más estudiantes inscritos
    // -- $sql = "SELECT c.id AS course_id, c.fullname, c.summary
    $sql = "SELECT COUNT(DISTINCT ue.userid) AS num_students, c.id AS courseid, c.fullname, c.summary
    FROM {user_enrolments} ue
    JOIN {enrol} e ON ue.enrolid = e.id
    JOIN {course} c ON c.id = e.courseid
    WHERE c.visible = 1
    GROUP BY c.id
    ORDER BY num_students DESC
    LIMIT 3";
    $courses = $DB->get_records_sql($sql);

    return $courses;

}

function get_courses_sorted_by_enrollment() {
    global $DB;

    // Obtener todos los cursos
    // $courses = $DB->get_records('course');
    $sql = "SELECT * FROM {course}";
    // -- WHERE category = :categoryid"
    
    // $params  =  [
    //     'categoryid' => 1,
    //     'categoryid' => 42
    // ];

    // $courses = $DB->get_records_sql($sql, $params);
    $courses = $DB->get_records_sql($sql);
    // var_dump($courses);
    

    // Inicializar el arreglo de recuento de usuarios
    $course_user_counts = array();

    foreach ($courses as $course) {
        // Obtener el contexto del curso
        $course_context = context_course::instance($course->id);

        // Contar usuarios inscritos en el curso
        $users = get_enrolled_users($course_context);
        $course_user_counts[] = array(
            'id' => $course->id,
            'fullname' => $course->fullname, 
            'summary' => $course->summary,
            'enrollment_count' => count($users)
        );
    }

    // Ordenar cursos por número de usuarios inscritos en orden descendente
    usort($course_user_counts, function ($a, $b) {
        return $b['enrollment_count'] - $a['enrollment_count'];
    });

    // print_object($course_user_counts);
    return $course_user_counts;
}



