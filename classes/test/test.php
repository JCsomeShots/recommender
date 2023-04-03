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


defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/classes/test/advanced_testcase.php');


function testPrecheck() {
    // Initialize the global $DB variable
    global $DB;

    // Create a test user and course
    $user = $this->getDataGenerator()->create_user();
    $course = $this->getDataGenerator()->create_course();

    // Check that the user and course are not pre-checked
    $prechecked = precheck($user->id, $course->id);
    $this->assertFalse($prechecked);

    // Simulate a pre-check by adding a record to the block_recommender_clicks table
    $data = new stdClass();
    $data->userid = $user->id;
    $data->courseid = $course->id;
    $DB->insert_record('block_recommender_clicks', $data);

    // Check that the user and course are now pre-checked
    $prechecked = precheck($user->id, $course->id);
    $this->assertTrue($prechecked);
}

function testEnrol() {
    // Initialize the global $DB variable
    global $DB;

    // Create a test user and course
    $user = $this->getDataGenerator()->create_user();
    $course = $this->getDataGenerator()->create_course();

    // Enrol the user in the course
    $this->getDataGenerator()->enrol_user($user->id, $course->id);

    // Call the enrol function and check the returned value
    $enrolment = enrol($user->id, $course->id);
    $this->assertInstanceOf(stdClass::class, $enrolment);
    $this->assertEquals($user->id, $enrolment->userid);
    $this->assertEquals($course->fullname, $enrolment->fullname);
}

class block_recommender_testcase extends advanced_testcase {
    public function testInit() {
        // Create an instance of the block_recommender class
        $block_recommender = new block_recommender();

        // Call the init function
        $block_recommender->init();

        // Check that the title property has been set correctly
        $this->assertEquals('My Plugin Title', $block_recommender->title);
    }
}

