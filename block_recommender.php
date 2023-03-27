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

require_once("{$CFG->dirroot}/blocks/recommender/classes/event/organization.php");
require_once($CFG->dirroot."/blocks/recommender/classes/event/organization.php");
require_once($CFG->libdir."/accesslib.php");
require_once("{$CFG->libdir}/accesslib.php");
require_once($CFG->libdir."/blocklib.php");
require_once("{$CFG->libdir}/blocklib.php");
// require_once($CFG->dirroot."/blocks/recommender/classes/event/registerclick.php");
require_once(__DIR__.'/../../config.php');

// $PAGE->requires->jquery();
// $PAGE->requires->js('/blocks/recommender/amd/jquery.js');

class block_recommender extends block_base {

    /**
     * To initialize the block.
     * @return String $this->title. With the plugin title.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_recommender');
    }

    /**
     * To check if the block has config.
     */
    public function has_config() {
        return  true;
    }

    /**
     * To get the content for this block.
     * @return Mixed $this->content.
     */
    public function get_content() {
        global $DB, $CFG, $USER;

        $this->page->requires->jquery();
        $this->page->requires->js('/blocks/recommender/amd/src/register.js');


    
        if ($this->config->disabled) {
            return null;
        } else if ($this->content !== null) {
            return $this->content;
        }

        $content = '';
        $limit = $this->instance->defaultregion == 'content' ? 6 : 4;
        $courses = $DB->get_records_sql("SELECT * FROM {course} ORDER BY id LIMIT $limit");
    
        $instanceblock = $this->instance;
    
        if ($instanceblock->defaultregion == 'content') {
            $content .= '<div class="card-deck justify-content-center">';
        }
    
        foreach ($courses as $i => $course) {
            $words = strip_tags(mb_convert_encoding($course->summary, 'UTF-8', 'ISO-8859-1'));
            $words = str_word_count($words, 1); // convert the description into an array.
            $summary = implode(' ', array_slice($words, 0, 6)); // Join the 6 first words into a str.

            // $summary = implode(' ', array_slice(str_word_count(strip_tags(mb_convert_encoding($course->summary, 'UTF-8', 'ISO-8859-1')), 1), 0, 6)); 

            $card_class = $instanceblock->defaultregion == 'content' ? 'col-sm-3' : 'col-sm-12';

            $content .= '<div class="'.$card_class.'">';
            $content .= '<div class="card mb-3 h-100">';

            // $content .= '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'" style="text-decoration: none;" onClick="registerClick('.$USER->id.', '.$course->id.');">';
            $content .= '<a href="#" style="text-decoration: none;" onClick="registerClick('.$USER->id.', '.$course->id.');">';

            $content .= '<div class="card-body">';
            $content .= '<h5 class="card-title text-primary" >'.$course->fullname.'</h5>';
            $content .= '<p class="card-text text-dark">'.$summary.'</p>';
            $content .= '</a>';
            $content .= '</div>';
            $content .= '</div>';
            $content .= '</div>';
    
            if ($i == 3 && $instanceblock->defaultregion != 'content') {
                break;
            }
    
            if ($instanceblock->defaultregion == 'content' && ($i + 1) % 4 == 0) {
                $content .= '</div><div class="card-deck">';
            }
        }
    
        if ($instanceblock->defaultregion == 'content') {
            $content .= '</div>';
        }
    
        $this->content = new stdClass();
        $this->content->text = $content;
    
        return $this->content;
    }


/********* some notes  */
// $event = \block_my_courses\event\course_clicked::create(array(
//     'objectid' => $course->id,
//     'context' => $PAGE->context,
// ));
// $event->add_record_snapshot('course', $course);
// $event->trigger();



    /**
     * Get an array of all region names on this page where a block may appear
     *
     * @return array the internal names of the regions on this page where block may appear.
     */
    public function get_regions() {
        if (is_null($this->defaultregion)) {
            $this->page->initialise_theme_and_output();
        }
        return array_keys($this->regions);
    }
    /**
     * Get the region name of the region blocks are added to by default
     *
     * @return string the internal names of the region where new blocks are added
     * by default, and where any blocks from an unrecognised region are shown.
     * (Imagine that blocks were added with one theme selected, then you switched
     * to a theme with different block positions.)
     */
    public function get_default_region() {
        $this->page->initialise_theme_and_output();
        return $this->defaultregion;
    }

    /**
     * To change title of the block.
     */
    public function specialization() {
        if (isset($this->config)) {
            if (empty($this->config->title)) {
                $this->title = get_string('defaulttitle', 'block_recommender');
            } else {
                $this->title = $this->config->title;
            }

            if (empty($this->config->text)) {
                $this->config->text = get_string('defaulttext', 'block_recommender');
            }
        }
    }

    /**
     * To create more than one instance.
     * @return Boolean .
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Set the applicable formats for this block to all
     *
     * @return array
     */
    public function applicable_formats() : array {
        return ['all' => true];
    }

    /**
     * To Save configuration from settings.
     */
    public function instance_config_save($data, $nolongerused = false) {
        global $CFG;
        
        if (!empty($CFG->block_recomender_allowhtml)) {
            // && $CFG->block_helloworld_allowhtml == '1'
            $data->text = strip_tags($data->text);
        } 
    
        // Default implementation defined in the main class.
        return parent::instance_config_save($data,$nolongerused);
    }

    /**
     * To get the key from settings.
     */
    public function instance_config_key($data, $nolongerused = false) {
        global $CFG;
        
        if (!empty($CFG->block_recomender_apikey)) {
            // && $CFG->block_helloworld_allowhtml == '1'
            // $data->text = strip_tags($data->text);
            get_organization($data);
        } 
    
        // Default implementation defined in the main class.
        // return parent::instance_config_save($data,$nolongerused);
    }

    public function get_cover_images($id){
        $courseid = $id;
        $course = get_course($courseid);
        $coverurl = $course->get_formatted_intro(true, ['noclean' => true, 'para' => false, 'overflowdiv' => false]);
        preg_match_all('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $coverurl, $matches);
        $coverimg = $matches['src'][0];
        return $coverimg;
    }
}
