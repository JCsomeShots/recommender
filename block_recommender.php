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
require_once("{$CFG->dirroot}/blocks/recommender/classes/query/courserating.php");
require_once("{$CFG->dirroot}/blocks/recommender/classes/query/userenrol.php");
require_once("{$CFG->dirroot}/blocks/recommender/classes/query/coursesrelated.php");
require_once("{$CFG->dirroot}/blocks/recommender/classes/helpers/printhtml.php");
require_once("{$CFG->dirroot}/blocks/recommender/course_click.php");
require_once("{$CFG->dirroot}/blocks/recommender/model_recommender.php");
require_once(__DIR__.'/../../config.php');


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
        global $USER, $COURSE;

        $instanceblock = $this->instance;
        $region = $instanceblock->defaultregion == 'content';
        $limit = $region ? 3 : 3;
        $heightlimit = 'height: 5px';

        recommenderpython();
        $coursesrelated =  get_related_courses(); 
        // $coursesrelated =  notenrol(); 
        $coursesnotenrol = notenrol();   
        // $coursespopular = best_ratingcourse();
        $coursespopular = get_courses_sorted_by_enrollment();
        $coursessuggested = suggested_table();
        $clickform = new course_click();
        $param = new stdClass();
        $check = false;
        $click_saved = false;
    
        $content = '';
    
        if (!empty($coursessuggested)) {
            $num_courses = count($coursessuggested); 
            // var_dump($coursessuggested);


            // Suggested courses section
            if ($region) {
                $content .= '<div mt-2><h5>'.get_string('title1', 'block_recommender').'</h5></div>';
                $content .= '<div><h6>'.get_string('subtitle1', 'block_recommender').'</h6></div>';
                $content .= '<div class="card-deck d-flex justify-content-between">';
            } else {
                $content .= '<div class="pb-2"><h5>'.get_string('title1', 'block_recommender').'</h5>';
                // $content .= '<p>'.get_string('subtitle1', 'block_recommender').'</p>';
                $content .= '</div>';
                $content .= '<ul class="mb-0">';
            }

            foreach (array_slice($coursessuggested, 0, $limit) as $course) {
                $summary = get_summary($course->summary);
                $iconimg = 'fa-bolt';
                $bgcolor = '#6E81BE';
                if ($region) {
                    $content .= get_card($course, $summary, $clickform, $USER, $check, $click_saved, $iconimg, $bgcolor);
                } else {
                    $content .= get_list_course($course, $USER, $iconimg);
                }
            }
            // if ($num_courses < $limit) { // Verificar si hay menos de limit cursos
            //     $difference = $limit - $num_courses;

            //     foreach (array_slice($coursesnotenrol, 0, $difference) as $course) {
            //         $summary = get_summary($course->summary);
            //         $iconimg = 'fa-bolt';
            //         $bgcolor = '#6E81BE';
            //         if ($region) {
            //             $content .= get_card($course, $summary, $clickform, $USER, $check, $click_saved, $iconimg, $bgcolor);
            //         } else {
            //             $content .= get_list_course($course, $USER, $iconimg);
            //         }
            //     }
            // }
            

            $content .= $region ? '</div>' : '</ul>';

        }

        if (!empty($coursespopular)) {
            $num_courses = count($coursespopular); 

            // Most popular section
            
            if ($region) {
                $content .= '<div mt-2><h5>'.get_string('title2', 'block_recommender').'</h5></div>';
                $content .= '<div><h6>'.get_string('subtitle2', 'block_recommender').'</h6></div>';
                $content .= '<div class="card-deck d-flex justify-content-between w-100">';
            } else {
                $content .= '<div class="pb-2" ><h5>'.get_string('title2', 'block_recommender').'</h5>';
                // $content .= '<p>'.get_string('subtitle2', 'block_recommender').'</p>';
                $content .= '</div>';
                $content .= '<ul class="mb-0">';
            }

            foreach (array_slice($coursespopular, 0, $limit) as $course) {
                // var_dump($coursespopular);
                // var_dump($course);
                // var_dump($course->summary);
                $courseA = new stdClass;
                $courseA->courseid = $course['id'];
                $courseA->fullname = $course['fullname'];

                $summary = get_summary($course['summary']);
                $iconimg = 'fa-thumbs-up';
                $bgcolor = '#9CCF65';
                if ($region) {
                    $content .= get_card($courseA, $summary, $clickform, $USER, $check, $click_saved, $iconimg, $bgcolor);
                } else {
                    $content .= get_list_course($courseA, $USER, $iconimg);
                }
            }
            // if ($num_courses < $limit) { // Verificar si hay menos de limit cursos
            //     $difference = $limit - $num_courses;

            //     foreach (array_slice($coursesnotenrol, 0, $difference) as $course) {
            //         $summary = get_summary($course->summary);
            //         $iconimg = 'fa-thumbs-up';
            //         $bgcolor = '#9CCF65';
            //         if ($region) {
            //             $content .= get_card($course, $summary, $clickform, $USER, $check, $click_saved, $iconimg, $bgcolor);
            //         } else {
            //             $content .= get_list_course($course, $USER, $iconimg);
            //         }
            //     }
            // }

            $content .= $region ? '</div>' : '</ul>';
        }
    
        // if (!empty($coursesnotenrol)) {
        if (!empty($coursesrelated)) {
            $num_courses = count($coursesrelated); 


            if ($region) {
                $content .= '<div mt-2><h5>'.get_string('title3', 'block_recommender').'</h5></div>';
                $content .= '<div><h6>'.get_string('subtitle3', 'block_recommender').'</h6></div>';
                $content .= '<div class="card-deck d-flex justify-content-between">';
            } else {
                $content .= '<div class="pb-2" ><h5>'.get_string('title3', 'block_recommender').'</h5>';
                // $content .= '<p>'.get_string('subtitle3', 'block_recommender').'</p>';
                $content .= '</div>';
                $content .= '<ul class="mb-0"">';
            }

            foreach (array_slice($coursesrelated, 0, $limit) as $course) {
            // foreach (array_slice($coursesnotenrol, 0, $limit) as $course) {
                $summary = get_summary($course->summary);
                $iconimg = 'fa-star';
                $bgcolor = '#C65D52';
                if ($region) {
                    $content .= get_card($course, $summary, $clickform, $USER, $check, $click_saved, $iconimg, $bgcolor);
                } else {
                    $content .= get_list_course($course, $USER, $iconimg);
                }
            }
            // if ($num_courses < $limit) { // Verificar si hay menos de limit cursos
            //     $difference = $limit - $num_courses;

            //     foreach (array_slice($coursesnotenrol, 0, $difference) as $course) {
            //         $summary = get_summary($course->summary);
            //         $iconimg = 'fa-star';
            //         $bgcolor = '#C65D52';
            //         if ($region) {
            //             $content .= get_card($course, $summary, $clickform, $USER, $check, $click_saved, $iconimg, $bgcolor);
            //         } else {
            //             $content .= get_list_course($course, $USER, $iconimg);
            //         }
            //     }
            // }

            $content .= $region ? '</div>' : '</ul>';

            $this->content = new stdClass();
            $this->content->text = $content;

            if (is_siteadmin()) {
                $url = new moodle_url('/blocks/recommender/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id));
                $this->content->footer = html_writer::link($url, get_string('addpage', 'block_recommender'));
            }

            return $this->content;
        }
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


    public function get_cover_images($id){
        $courseid = $id;
        $course = get_course($courseid);
        $coverurl = $course->get_formatted_intro(true, ['noclean' => true, 'para' => false, 'overflowdiv' => false]);
        preg_match_all('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $coverurl, $matches);
        $coverimg = $matches['src'][0];
        return $coverimg;
    }
}
