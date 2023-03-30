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
require_once("{$CFG->libdir}/accesslib.php");
require_once("{$CFG->libdir}/blocklib.php");
require_once("{$CFG->dirroot}/blocks/recommender/classes/event/registerclick.php");
require_once("{$CFG->dirroot}/blocks/recommender/course_click.php");
require_once(__DIR__.'/../../config.php');
// require_once(__DIR__.'/classes/course.php');
// require_once($CFG->libdir . '/formslib.php');



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
        global $DB, $CFG, $USER, $PAGE, $COURSE;

        $instanceblock = $this->instance;
        $region = $instanceblock->defaultregion == 'content';
        $limit = $region ? 6 : 3;
        $heightlimit = 'height: 5px';
        $courses = $DB->get_records('course', null, 'RAND()', '*', 0, $limit);
        

        $content = '';
        if ($region) {
            $content .= '<div class="card-columns">';
        }

        $clickform = new course_click();
        $param = new stdClass();


        foreach ($courses as $i => $course) {
            $summary = $course->summary;
            $summary = preg_replace('/<[^>]*>/', '', $summary);
            if (mb_detect_encoding($summary) !== 'UTF-8') {
                $summary = mb_convert_encoding($summary, 'UTF-8', 'ISO-8859-1');
            }
            $summary = substr($summary, 0, strpos($summary, ' ', strpos($summary, ' ', strpos($summary, ' ') + 1) + 1));

            $content .= '<div class="card mb-3 h-100">';

            // $content .= '<a href="' . new moodle_url('/course/view.php', array('id' => $course->id)) . '">';

            $content .= '<div class="card-img dashboard-card-img " style="background-image: linear-gradient(to bottom left, #465f9b, #755794, #6d76ae); '.$heightlimit.'">';
            $content .= '</div>';
            
            $content .= '<div class="card-body">';
            $content .= '<h5 class="card-title text-primary" >'.$course->fullname.'</h5>';
            $card_style = $instanceblock->defaultregion == 'content' ? 'style="height:40px;"' : '';
            $content .= '<p class="card-text text-dark"'.$card_style.'>'.$summary.'</p>';
            $content .= '</div>';

            // $content .= '<div class="card-footer">';
            // $content .= '<button type="button" class="rounded text-white" style="background-image: linear-gradient(to bottom left, #465f9b, #755794, #6d76ae);" onclick="registerClick('.$USER->id.','.$course->id.')">'. get_string('go', 'block_recommender').'</button>';
            // $content .= '</div>';

            $content .= '<div class="card-footer">';
            $param->user_id = $USER->id;
            $param->course_id = $course->id;
            $clickform->set_data($param);
            $clickbutton = $clickform->render();
            $content .= $clickbutton;
            $content .= '</div>';

            if ($region) {
                $content .= '<div class="card-img dashboard-card-img " style="background-image: linear-gradient(to bottom left, #465f9b, #755794, #6d76ae); '.$heightlimit.'">';
                $content .= '</div>';
            }

            // $content .= '</a>';
            $content .= '</div>';

        }

        if ($fromform = $clickform->get_data()) {
            require_sesskey();
            $clickform->save($fromform->user_id, $fromform->course_id);
            $clickform->set_data($param);
            $clickbutton = $clickform->render();
            // $clickform->reset($course->id);
        }
    
        if ($instanceblock->defaultregion == 'content') {
            $content .= '</div>';
        }
    
        $this->content = new stdClass();
        $this->content->text = $content;
    
        return $this->content;
    }



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
        
        // if (!empty($CFG->block_recomender_allowhtml)) {
        //     $data->text = strip_tags($data->text);
        // } 
        // Default implementation defined in the main class.
        // return parent::instance_config_save($data,$nolongerused);
        if ( $this->instance->defaultregion != 'content') {
        } else {
            $instanceconfig = $this->instance_config();
            $instanceconfig->defaultweight = 0;
        }
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
