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
 * Restore structure steps for the HLS Player activity module.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Structure step to restore one hlsplayer activity.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_hlsplayer_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines structure of path elements to be processed during the restore.
     *
     * @return array of restore_path_element
     */
    protected function define_structure() {
        $paths    = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('hlsplayer', '/activity/hlsplayer');
        if ($userinfo) {
            $paths[] = new restore_path_element('hlsplayer_progress', '/activity/hlsplayer/progresses/progress');
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process the hlsplayer data.
     *
     * @param array $data The backup data.
     */
    protected function process_hlsplayer($data) {
        global $DB;

        $data               = (object) $data;
        $oldid              = $data->id;
        $data->course       = $this->get_courseid();
        $data->timecreated  = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('hlsplayer', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process the hlsplayer_progress data.
     *
     * @param array $data The backup data.
     */
    protected function process_hlsplayer_progress($data) {
        global $DB;

        $data               = (object) $data;
        $oldid              = $data->id;
        $data->hlsplayerid  = $this->get_new_parentid('hlsplayer');
        $data->userid       = $this->get_mappingid('user', $data->userid);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $DB->insert_record('hlsplayer_progress', $data);
    }

    /**
     * Post-execution actions - restore files.
     */
    protected function after_execute() {
        $this->add_related_files('mod_hlsplayer', 'intro', null);
        $this->add_related_files('mod_hlsplayer', 'content', null);
    }
}
