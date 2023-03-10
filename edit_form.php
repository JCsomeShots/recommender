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
 * Edit the block.
 *
 * @package     block_recommender
 * @author      2023 JuanCarlo Castillo <juancarlo.castillo20@gmail.com>
 * @copyright   2023 JuanCarlo Castillo & Eurecat.dev
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_recommender_edit_form extends block_edit_form {

    protected function specific_definition($mform) {

        // Select a configuration param.
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

        // To create a param for block title.
        $mform->addElement('text', 'config_title', get_string('blocktitle', 'block_recommender'));
        $mform->setDefault('config_title', get_string('blocktitle', 'block_recommender'));
        $mform->setType('config_title', PARAM_TEXT);

        // A string to display.
        $mform->addElement('text', 'config_text', get_string('blockstring', 'block_recommender'));
        $mform->setDefault('config_text', 'default value');
        $mform->setType('config_text', PARAM_RAW);

        // To hide the block.
        $mform->addElement(
        'advcheckbox',
        'config_disabled',
        get_string('blockdisabled', 'block_recommender'),
        get_string('blockdis', 'block_recommender'),
        );
    }

}

