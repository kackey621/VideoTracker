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
 * Backup structure steps for the HLS Player activity module.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define all the backup steps that will be used by the backup_hlsplayer_activity_task.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_hlsplayer_activity_structure_step extends backup_activity_structure_step {
    /**
     * Defines the backup structure of the module.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        // Define each element separated.
        $hlsplayer = new backup_nested_element('hlsplayer', ['id'], [
            'name', 'intro', 'introformat', 'sourcetype', 'videourl',
            'allowspeeds', 'allowseeking', 'completionminview', 'grade',
            'timecreated', 'timemodified',
        ]);

        $progresses = new backup_nested_element('progresses');

        $progress = new backup_nested_element('progress', ['id'], [
            'userid', 'progress', 'percentage', 'lastposition', 'timemodified',
        ]);

        // Build the tree.
        $hlsplayer->add_child($progresses);
        $progresses->add_child($progress);

        // Define sources.
        $hlsplayer->set_source_table('hlsplayer', ['id' => backup::VAR_ACTIVITYID]);

        // Only include user progress if user info is being backed up.
        if ($this->get_setting_value('userinfo')) {
            $progress->set_source_table('hlsplayer_progress', ['hlsplayerid' => backup::VAR_PARENTID]);
        }

        // Define id annotations.
        $progress->annotate_ids('user', 'userid');

        // Define file annotations.
        $hlsplayer->annotate_files('mod_hlsplayer', 'intro', null);
        $hlsplayer->annotate_files('mod_hlsplayer', 'content', null);

        return $this->prepare_activity_structure($hlsplayer);
    }
}
