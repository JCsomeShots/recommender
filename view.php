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



if(!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_recommender', $courseid);
}
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



if ($selectcourse->is_cancelled()) {
    $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
    redirect($courseurl);
} else if ($selectcourse->get_data() ) {
    
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
    
        $sql = "SELECT br.id, c.fullname FROM {block_recommender_suggested} br JOIN {course} c WHERE c.id = br.courseid";
        $toview = $DB->get_records_sql($sql);

    echo $OUTPUT->box_start('card-columns');

    foreach ($toview as $c) {
        echo html_writer::start_tag('div', array('class' => 'card'));
        echo html_writer::start_tag('div', array('class' => 'card-body'));
        echo html_writer::tag('p', format_text($c->fullname, FORMAT_PLAIN), array('class' => 'card-text'));
        echo html_writer::tag('p', format_text($c->id, FORMAT_PLAIN), array('class' => 'card-text'));
        echo html_writer::start_tag('p', array('class' => 'card-footer text-center'));
        echo html_writer::link(
            new moodle_url(
                '/blocks/recommender/view.php', array('blockid' => $blockid, 'courseid' => $courseid),
                array('action' => 'del', 'id' => $c->id , 'sesskey' => sesskey())
            ),
            $OUTPUT->pix_icon('t/delete', ''), array('role' => 'button', 'aria-label' => get_string('delete'), 'title' => get_string('delete'))
        );
        echo html_writer::end_tag('p');

        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
    }
    echo $OUTPUT->box_end();

    echo $OUTPUT->footer();



    
} else {
    $site = get_site();
    echo $OUTPUT->header();
    $selectcourse->display();
    echo $OUTPUT->footer();

}