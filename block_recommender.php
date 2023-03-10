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
 * @author      2023 JuanCarlo Castillo <juancarlo.castillo20@gmail.com>
 * @copyright   2023 JuanCarlo Castillo & Eurecat.dev
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot. '/blocks/recommender/classes/event/organization.php');

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
    public function  has_config() {
        return  true;
    }

    /**
     * To get the content for this block.
     * @return Mixed $this->content.
     */
    public function get_content() {
        if ($this->config->disabled) {
            return null;
        } else if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {
            $this->content->text = '<b>Recommender</b> desde Moodle!';
        }
        $this->content->footer = '<i>All rights reserved</i>';

        return $this->content;
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
}
