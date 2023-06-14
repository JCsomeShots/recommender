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

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_login();

/**
 * To add hidden fields.
 */
class recommender_form extends moodleform {

    /**
     * Add elements to form.
     */
    public function definition() {
        
        $mform =& $this->_form;

        $mform->addElement('hidden', 'blockid');
        $mform->setType('blockid', PARAM_RAW);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_RAW);
        
        $mform->addElement(
            'header',
            'displayinfo',
            get_string('textfields', 'block_recommender')
        );
        $courses = array();
        $getcourse = get_courses();

        foreach ($getcourse as $course) {
            $courses[$course->id] = $course->fullname;
        }
        $courses[0] = get_string('courseselect', 'block_recommender');
        ksort($courses);

        $select = $mform->addElement('select', 'course1', get_string('courseone', 'block_recommender'), $courses);
        $select->setSelected(get_string('course1', 'block_recommender'));

        $mform->setType('course1', PARAM_INT);

        $select = $mform->addElement('select', 'course2', get_string('coursetwo', 'block_recommender'), $courses);
        $select->setSelected(get_string('course2', 'block_recommender'));

        $mform->setType('course2', PARAM_INT);

        $select = $mform->addElement('select', 'course3', get_string('coursethree', 'block_recommender'), $courses);
        $select->setSelected(get_string('course3', 'block_recommender'));

        $mform->setType('course3', PARAM_INT);

        $this->add_action_buttons();

    }

}