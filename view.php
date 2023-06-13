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
 * Settings  the block.
 *
 * @package     block_recommender
 * @author      2023 JuanCarlo Castillo <juancarlo.castillo20@gmail.com>
 * @copyright   2023 JuanCarlo Castillo & Eurecat.dev
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('recommender_form.php');

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);
// Find Block id.
$blockid = required_param('blockid', PARAM_INT);
// If there more var.
$id = optional_param('id', 0, PARAM_INT);



// if(!$course = $DB->get_record('course', array('id' => $courseid))) {
//     print_error('invalidcourse', 'block_recommender', $courseid);
// }
require_login($course);

$PAGE->set_url('/blocks/recommender/view.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('edithtml', 'block_recommender'));


// Create a node. 
$settingsnode = $PAGE->settingsnav->add(get_string('recommendersettings', 
    'block_recommender'));
// Create a block URL with block id.
$editurl = new moodle_url('/blocks/recommender/view.php', 
    array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid));
// Add the node with the block url.
$editnode = $settingsnode->add(get_string('editpage', 'block_recommender'), $editurl);
// Active breadcums.
$editnode->make_active();

$selectcourse = new recommender_form();

$toform['blockid'] = $blockid;
$toform['courseid'] = $courseid;
$selectcourse->set_data($toform);


$action = optional_param('action', '', PARAM_TEXT);

if ($action == 'del') {
    $id = required_param('id', PARAM_TEXT);

    $DB->delete_records('block_recommender_suggested', array('id' => $id));
}


if ($selectcourse->is_cancelled()) {
    if ($PAGE->has_blocks() && $PAGE->pagelayout == 'mydashboard') {
        // El block está en el dashboard
        $dashboard_url = new moodle_url('/my/');
        redirect($dashboard_url);
    } else {
        // El block no está en el dashboard
        $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
        redirect($courseurl);
    }
} else if ($selectcourse->get_data() ) {

    // The real action to delete a message.
    $action = optional_param('action', '', PARAM_TEXT);

    echo $OUTPUT->header();

    $selectcourse->display();

    // Inicializar el arreglo $courses
    $courses = [];

    // Agregar cada variable al arreglo $courses solo si es diferente de cero
    foreach (["course1", "course2", "course3"] as $param) {
        $course = optional_param($param,null, PARAM_INT);
        if ($course != 0) $courses[] = $course;
    }

    // Obtener cursos únicos
    $courses = array_unique($courses);

    // Iterar sobre los cursos y verificar si existe en la tabla block_recommender_suggested
    foreach ($courses as $course) {

        // Consultar la base de datos para verificar si el curso ya está en sugerencias
        $existing_course = $DB->get_record('block_recommender_suggested', array('courseid' => $course), '*', IGNORE_MULTIPLE);

        // Verificar si se encontró el curso o no
        if ($existing_course != false) {

            // El curso ya existe en sugerencias, actualizar timemodified
            $existing_course->timemodified = time();
            $DB->update_record('block_recommender_suggested', $existing_course);

        } else {

            // No encontramos el curso en sugerencias, hacer un insert
            $suggested_course = new stdClass();
            $suggested_course->courseid = $course;
            $suggested_course->timecreated = time();

            $DB->insert_record('block_recommender_suggested', $suggested_course);
        }
    }
    
        $sql = "SELECT br.id, c.fullname, c.summary FROM {block_recommender_suggested} br JOIN {course} c WHERE c.id = br.courseid";
        $coursesuggested = $DB->get_records_sql($sql);

        print_course_suggested($coursesuggested);

    echo $OUTPUT->footer();



    
} else {
    $site = get_site();

    $sql = "SELECT br.id, c.fullname FROM {block_recommender_suggested} br JOIN {course} c WHERE c.id = br.courseid";
    $coursesuggested = $DB->get_records_sql($sql);

    echo $OUTPUT->header();
    $selectcourse->display();

    print_course_suggested($coursesuggested);
    echo $OUTPUT->footer();

}

function print_course_suggested($coursesuggested) {
    global $OUTPUT;

    echo $OUTPUT->box_start('card-columns');

    $sumary = '';
    foreach ($coursesuggested as $c) {

        if (!empty($c->sumary)) {
            $summary = $c->summary;
            $summary = preg_replace('/<[^>]*>/', '', $summary);
            if (mb_detect_encoding($summary) !== 'UTF-8') {
                $summary = mb_convert_encoding($summary, 'UTF-8', 'ISO-8859-1');
            }
            $summary = substr($summary, 0, strpos($summary, ' ', strpos($summary, ' ', strpos($summary, ' ') + 1) + 1));
        }
        $words = explode(" ", $c->fullname);
        $wordsReduce = array_slice($words, 0, 3);
        $titlereduce = implode(" ", $wordsReduce);
        echo html_writer::start_tag('div', array('class' => 'card'));
        echo html_writer::start_tag('div', array('class' => 'card-body'));
        echo html_writer::tag('p', format_text($titlereduce, FORMAT_PLAIN), array('class' => 'card-text'));
        echo html_writer::tag('p', format_text( $sumary, FORMAT_PLAIN), array('class' => 'card-text'));
        echo html_writer::start_tag('p', array('class' => 'card-footer text-center'));
        echo html_writer::link(
            new moodle_url(
                '/blocks/recommender/view.php',
                array('action' => 'del', 'blockid' => $blockid, 'courseid' => $courseid, 'id' => $c->id)
            ),
            $OUTPUT->pix_icon('t/delete', '') . get_string('delete')
        );
        echo html_writer::end_tag('p');

        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
    }
    echo $OUTPUT->box_end();

}