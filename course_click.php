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
 * Main file.
 *
 * @package   block_recommender
 * @author    2023 JuanCarlo Castillo <juancarlo.castillo20@gmail.com>
 * @copyright 2023 JuanCarlo Castillo & Eurecat.dev
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/formslib.php');

/**
 * To add hidden fields.
 */
class course_click extends moodleform {

    /**
     * Add elements to form.
     */
    public function definition() {

        $mform = $this->_form;
        $mform->addElement('hidden', 'user_id');
        $mform->settype('user_id', PARAM_INT);
        $mform->addElement('hidden', 'course_id');
        $mform->settype('course_id', PARAM_INT);
        $d = get_string('go', 'block_recommender');
        $mform->addElement('submit',
        'submitbutton',
        $d,
        ['class' => 'go_recommender m-1',
        'style' => "background-image: linear-gradient(to bottom left, #465f9b, #755794, #6d76ae)"]);
    }

    /**
     * to redirect to forum_review
     * @return Void .
     */
    public function redirect($course_id) {
        global $CFG;
        // redirect(new moodle_url($CFG->wwwroot.'/course/view.php?id='.$course_id));
        redirect( new moodle_url('/course/view.php', array('id' => $course_id)) );
    }

    /**
     * Save on db
     * @return Void .
     */
    public function save($iduser, $idcourse) {

        global $DB;
        $precheck = $DB->record_exists('block_recommender_clicks', array('userid' => $iduser));
        $record = new stdClass();
        $record->userid = $iduser;
        $record->courses = '';
        $record->timemodified = time();
        
        if (!$precheck) {
            $record->courses = $idcourse;
            $DB->insert_record('block_recommender_clicks', $record);
        } else {
            $clicks = $DB->get_record('block_recommender_clicks', array('userid' => $iduser), '*', IGNORE_MISSING);
            if (!empty($clicks->courses)) {
                $course_array = explode(',', $clicks->courses);
            } else {
                $course_array = array();
            }
            if (!in_array($idcourse, $course_array)) {
                $course_array[] = $idcourse;
            }
            $record->id = $clicks->id;
            $record->courses = implode(',', $course_array);
            $DB->update_record('block_recommender_clicks', $record);
        }
    }

}
