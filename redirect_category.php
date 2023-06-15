<?php

require_once(__DIR__.'/../../config.php');
global $DB, $USER, $PAGE, $OUTPUT;

// Obtener el ID de usuario
$user_id = $USER->id;

// Obtener el ID de categoría (si se pasa como parámetro)
$categoryid = optional_param('category', null, PARAM_INT);
$categoryid = $_GET['category'];
$id = $_GET['category'];
$courseid = 10;

// Obtener los cursos de una categoría en particular
$category_courses = $DB->get_records('course', array('category' => $categoryid));

$courses = array();

// Para cada curso de esta categoría, verificar si el usuario está matriculado en él
foreach ($category_courses as $course) {
    $enrolment = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'));
    if ($enrolment) {
        // Verificar si el usuario está matriculado en el curso
        $is_user_enrolled = $DB->get_record('user_enrolments', array('enrolid' => $enrolment->id, 'userid' => $user_id));
        if ($is_user_enrolled) {
            // Agregar el curso a la lista
            $courses[] = $course->id;
        }
    }
}

// Crear la URL de redirección
// $redirectUrl = new moodle_url('/course/view.php', array('categoryid' => $categoryid, 'courses' => implode(',', $courses)));
$redirectUrl = new moodle_url('/course/index.php', array('categoryid' => $categoryid, 'courseid' => 10));

// Redirigir al usuario
// redirect($redirectUrl);
$response = array('redirectUrl' => $redirectUrl->out(false));
echo json_encode($response);
