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

defined('MOODLE_INTERNAL') || die;

// if ($hassiteconfig) {
    if ($ADMIN->fulltree) {

        $settings->add(
            new admin_setting_configcheckbox(
                'block_recommender_allowhtml',
                get_string('allowhtml', 'block_recommender'),
                get_string('allowhtml_desc', 'block_recommender'),
                0)
        );

        $settings->add(
            new admin_setting_configtext(
                'block_recommender_apikey',
            new lang_string('apikey', 'block_recommender'),
            new lang_string('apikey_des', 'block_recommender'), null, PARAM_TEXT),
        );
        $settings->add(
            new admin_setting_configtext(
                'block_recommender_apikey',
            new lang_string('email', 'block_recommender'),
            new lang_string('email_des', 'block_recommender'), null, PARAM_TEXT),
        );
        // $settings->add(new admin_setting_configcheckbox(
        //     'block_testblock/showcourses', 
        //     get_string('showcourses', 'block_testblock'), get_string('coursesinstead', 'block_testblock'), 0
        // ));
    }

// $ADMIN->add('blockrecommender', $settingspage);
// }
