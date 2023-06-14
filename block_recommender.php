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
        global $USER, $COURSE, $PAGE;
        $PAGE->requires->css(new \moodle_url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'));

        $instanceblock = $this->instance;
        $region = $instanceblock->defaultregion == 'content';
        $limit = $region ? 3 : 2;
        $heightlimit = 'height: 5px';
    
        $coursesrand = notenrol();
        $coursespopular = best_ratingcourse();
        $coursessuggested = suggested_table();
        $query = recommenderpython();
        // var_dump($query);
        $clickform = new course_click();
        $param = new stdClass();
        $check = false;
        $click_saved = false;
    
        $content = '';
    
        // Suggested courses section
        $content .= '<div><h5>Suggested courses</h5></div>';
        $content .= '<div><h6>Subtitle</h6></div>';
    
        if ($region) {
            // $content .= '<div class="card-columns">';
            $content .= '<div class="card-deck">';
        }
    
        $card_img = '<div class="card-img dashboard-card-img " style="background-image: linear-gradient(to bottom left, #465f9b, #755794, #6d76ae); '.$heightlimit.'"></div>';
    
        foreach (array_slice($coursessuggested, 0, $limit) as $course) {
            $summary = get_summary($course->summary);
            $icon = '<i class="fa fa-3x m-2 ml-3 fa-bolt" style="opacity:0.2; color:white;"></i>';
            $bgcolor = '#6E81BE';
            $content .= get_card($course, $summary, $card_img, $clickform, $USER, $check, $click_saved, $region, $icon, $bgcolor);
        }
    
        $content .= $region ? '</div>' : '';
    
        // Most popular section
        $content .= '<div mt-2><h5>Most popular</h5></div>';
        $content .= '<div><h6>Subtitle</h6></div>';

    
        if ($region) {
            $content .= '<div class="card-deck d-flex justify-content-between">';
        }
    
        foreach (array_slice($coursespopular, 0, $limit) as $course) {
            $summary = get_summary($course->summary);
            var_dump($course->courseid);
            $icon = '<i class="fa fa-3x m-2 ml-3 fa-thumbs-up" style="opacity:0.2; color:white;"></i>';
            $bgcolor = '#9CCF65';

            $content .= get_card($course, $summary, $card_img, $clickform, $USER, $check, $click_saved, $region, $icon, $bgcolor);
        }
    
        $content .= $region ? '</div>' : '';
    
        // Specials for you section
        $content .= '<div mt-2><h5>Specials for you</h5></div>';
        $content .= '<div><h6>Subtitle</h6></div>';

    
        if ($region) {
            $content .= '<div class="card-deck">';
        }
    
        foreach (array_slice($coursesrand, 0, $limit) as $course) {
            $summary = get_summary($course->summary);
            $icon = '<i class="fa fa-3x m-2 ml-3 fa-star" style="opacity:0.2; color:white;"></i>';
            $bgcolor = '#C65D52';
            $content .= get_card($course, $summary, $card_img, $clickform, $USER, $check, $click_saved, $region, $icon, $bgcolor);
        }
    
        $content .= $region ? '</div>' : '';
    
        $this->content = new stdClass();
        $this->content->text = $content;
    
        if (is_siteadmin()) {
            $url = new moodle_url('/blocks/recommender/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id));
            $this->content->footer = html_writer::link($url, get_string('addpage', 'block_recommender'));
        }
    
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
            // $instanceconfig = $this->instance_config();
            // $instanceconfig->defaultweight = -3;

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
        // $summary = $summary;
        $summary = preg_replace('/<[^>]*>/', '', $summary);
        if (mb_detect_encoding($summary) !== 'UTF-8') {
            $summary = mb_convert_encoding($summary, 'UTF-8', 'ISO-8859-1');
        }
        // $summary = substr($summary, 0, strpos($summary, ' ', strpos($summary, ' ', strpos($summary, ' ') + 1) + 1));
        $summary = substr($summary, 0, strpos($summary, ' ', strpos($summary, ' ', strpos($summary, ' ', strpos($summary, ' ', strpos($summary, ' ') + 1) + 1) + 1) + 1)) . ' ...';

    } else {
        $summary = '';
    }
    return $summary;
}

function get_card($course, $summary, $card_img, $clickform, $USER, &$check, &$click_saved, $region, $icon, $bgcolor) {
    $card = '<div class="card mb-3 rounded border border-primary mr-3">';

    $card .= '<div class="card-body border rounded-top" style="background-color:'.$bgcolor.';">';

    $card .= '<div style="background-color:'.$bgcolor.';">';
    $card .= $icon;
    $card .= '<h5 class="card-title text-white text-center">'.countthreewords($course->fullname).'</h5>';
    $card .= $region ? '<p class="card-text text-center text-white  ">'.$summary.'</p>' : '';

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