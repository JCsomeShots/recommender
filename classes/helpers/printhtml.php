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

function save_clicks_anchor($iduser, $idcourse) {

    global $DB, $PAGE;
    $precheck  = precheck($iduser, $idcourse);
    $enrol = enrol($iduser, $idcourse);

    $record = new stdClass();
    $record->userid = $iduser;
    $record->courseid = $idcourse;
    $record->timecreated = time();

    switch (true) {
        case ($precheck && empty($enrol)):
            $existing_record = $DB->get_record('block_recommender_clicks', array('userid' => $iduser, 'courseid' => $idcourse));
            $record->id = $existing_record->id;
            $record->timemodified = time();
            $record->clickscount = $existing_record->clickscount + 1;
            $DB->update_record('block_recommender_clicks', $record);
            break;
            
        default:
            $DB->insert_record('block_recommender_clicks', $record);
            break;
    }

    // Get the URL of the course
    $url = new moodle_url('/course/view.php', array('id' => $idcourse));

    // Redirect to the course using the current Moodle page
    $PAGE->set_url($url);
    redirect($url);

}

function redirect_course($courseid) {

    var_dump($courseid);
    redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
}


function countthreewords($texto) {
    // Separar el texto en palabras usando como delimitador el espacio (' ')
    $palabras = explode(' ', $texto);
    
        // Contar las primeras tres palabras
        $n_palabras = min(count($palabras), 3);
    
        // Armar un nuevo arreglo con las primeras tres palabras
        $primeras_palabras = array_slice($palabras, 0, $n_palabras);
    
        // Unir las palabras en un string
    $texto_cortado = implode(' ', $primeras_palabras);

    // Devolver el texto cortado
    return $texto_cortado;
} 

// Helper functions.

function get_summary($summary) {
    if (!empty($summary)) {
        $summary = preg_replace('/<[^>]*>/', '', $summary);
        if (mb_detect_encoding($summary) !== 'UTF-8') {
            $summary = mb_convert_encoding($summary, 'UTF-8', 'ISO-8859-1');
        }
        $summary = substr($summary, 0, strpos($summary, ' ', strpos($summary, ' ', strpos($summary, ' ', strpos($summary, ' ', strpos($summary, ' ') + 1) + 1) + 1) + 1)) . ' ...';
    } else {
        $summary = '';
    }
    return $summary;
}

function get_card($course, $summary, $clickform, $USER, &$check, &$click_saved, $iconimg, $bgcolor) {
    var_dump($course->courseid );

        $card = '<div class="card mb-3 rounded border border-primary mr-3';
        $card .= ' card-sm'; 
        $card .= '">';
        $card .= '<div class="card-body border rounded-top" style="background-color:'.$bgcolor.';">';
        $iconsize = 'fa-3x';
        $card .= '<div style="background-color:'.$bgcolor.';">';
        $icon = '<i class="fa '.$iconsize.' m-2 ml-3 '.$iconimg.'" style="opacity:0.2; color:white;"></i>';
        $card .= $icon;
        $card .= '<h5 class="card-title text-white text-center">'.countthreewords($course->fullname).'</h5>';
        $card .= '<p class="card-text text-center text-white  ">'.$summary.'</p>' ;
        $card .= '</div>';
        $card .= '</div>';

        $param = new stdClass();
        $param->user_id = $USER->id;
        $param->course_id = $course->courseid;
        $clickform->set_data($param);
        $card .= $clickform->render();
        $card .= '</div>';

        if (!$click_saved && $fromform = $clickform->get_data()) {
            var_dump($fromform->course_id);
            if (!$check) {
                require_sesskey();
                $clickform->save_clicks($fromform->user_id, $fromform->course_id);
                $clickform->redirect($fromform->course_id);
                $check = true;
                $click_saved = true;
            }
        }
        return $card;
}

function get_list_course($course, $USER, $iconimg) {
    var_dump($course->courseid );


    $iduser = $USER->id;
    $idcourse = $course->courseid;
    $url = new moodle_url('/course/view.php', array('id' => $course->id));
    $iconsize = 'fa-sm';
    $icon = '<i class="fa '.$iconsize.' mr-2 ml-3 '.$iconimg.'" style="opacity:0.8; color:black;"></i>';
    $text = '';
    $text .= '<a href="'.$url.'" >';
    // $text .= '<a href="#" onclick="save_clicks_anchor(\''.$iduser.'\',\''.$idcourse.'\')">';
    $text .= '<li class="d-flex align-item-center justify-content-start">';
    $text .= $icon;
    $text .= '<p class="card-title text-center" style="font-size:12px;">'.countthreewords($course->fullname).'</p>';
    $text .= '</li>';
    $text .= '</a>';

    $text .= '<style>';
    $text .= 'a.text-dark:hover {color: white; background-color: #007bff; text-decoration: none;}';
    $text .= '</style>';
    return $text;
}
