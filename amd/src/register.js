// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Display information about all the local_group_generator modules in the requested course.
 *
 * @package     local_group_generator
 * @author      2022 JuanCarlo Castillo <juancarlo.castillo20@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright   2022 JuanCa Castillo & Eurecat.dev
  */
//
// * Javascriptn & Jquery
// *


function registerClick(userid, courseid) {
    u = userid.toString();
    c = courseid.toString();
    alert([ u, c]);
    // $(document).ready(function() {
    //     $.ajax({
    //         // type: "POST",
    //         url: "blocks/recommender/classes/event/registerclick.php",
    //         data: { u, c},
    //         success: function(data) {
    //             console.log(data);
    //         },
    //     })
    // });
}


