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
 * Main code for My mentors block.
 *
 * @package   block_mymentors
 * @copyright  2012 Nathan Robbins (https://github.com/nrobbins)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_mymentors extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_mymentors');
    }

    public function applicable_formats() {
        return array('all' => true);
    }

    public function specialization() {
        $this->title = isset($this->config->title) ? $this->config->title : get_string('pluginname', 'block_mymentors');
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function get_content() {
        global $CFG, $USER, $DB, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';

        if (isloggedin() && !isguestuser()) {
            $userid = $USER->id;
            $mentors = $DB->get_records_sql('SELECT ra.userid, c.instanceid, u.id, u.firstname, u.lastname, u.lastaccess,
                                             u.picture, u.imagealt, u.email
                                             FROM {role_assignments} ra, {context} c, {user} u
                                             WHERE c.instanceid = ?
                                               AND u.id = ra.userid
                                               AND ra.contextid = c.id
                                               AND c.contextlevel = '.CONTEXT_USER, array($userid));

            $timetoshowusers = 300;
            $timefrom = 100 * floor((time()-$timetoshowusers) / 100);

            $canshowmsgicon = false;

            if (has_capability('moodle/site:sendmessage', $this->page->context) && !empty($CFG->messaging)) {
                $canshowmsgicon = true;
            }

            foreach ($mentors as $record) {
                $this->content->text .= '<div class="mymentors_mentor">';
                $this->content->text .= '<div class="mymentors_pic">'.$OUTPUT->user_picture($record, array('size'=>50)).'</div>';
                $this->content->text .= '<div class="mymentors_name">'.fullname($record).'</div>';
                $this->content->text .= '<div>';
                if ($canshowmsgicon) {
                    $anchortagcontents = '<img class="iconsmall" src="'.$OUTPUT->pix_url('t/message') . '" alt="'.
                                         get_string('messageselectadd') .'" />';
                    $anchortag = '<a class="mymentors_msg" href="'.$CFG->wwwroot.'/message/index.php?id='.$record->userid.
                                 '" title="'.get_string('messageselectadd').'">'.$anchortagcontents .'</a>';

                    $this->content->text .= $anchortag.' | ';
                }
                if ($record->lastaccess > $timefrom) {
                    $this->content->text .= '<span class="mymentors_online">'.get_string('online', 'block_mymentors').'</span>';
                } else {
                    $this->content->text .= '<span class="mymentors_offline">'.get_string('offline', 'block_mymentors').'</span>';
                }
                $this->content->text .= '</div></div>';
            }
        }

        $this->content->footer = '';

        return $this->content;
    }
}
