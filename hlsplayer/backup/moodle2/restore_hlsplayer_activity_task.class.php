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
 * Restore task for the HLS Player activity module.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/hlsplayer/backup/moodle2/restore_hlsplayer_stepslib.php');

/**
 * Provides the steps to perform one complete restore of the hlsplayer instance.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_hlsplayer_activity_task extends restore_activity_task {

    /**
     * No specific settings for this activity.
     */
    protected function define_my_settings() {
    }

    /**
     * Defines a restore step to restore the instance data from hlsplayer.xml.
     */
    protected function define_my_steps() {
        $this->add_step(new restore_hlsplayer_activity_structure_step('hlsplayer_structure', 'hlsplayer.xml'));
    }

    /**
     * Defines the contents in the activity that must be processed by the link decoder.
     *
     * @return array of restore_decode_content
     */
    public static function define_decode_contents() {
        $contents   = [];
        $contents[] = new restore_decode_content('hlsplayer', ['intro'], 'hlsplayer');
        return $contents;
    }

    /**
     * Defines the decoding rules for links belonging to the activity to be executed by the link decoder.
     *
     * @return array of restore_decode_rule
     */
    public static function define_decode_rules() {
        $rules   = [];
        $rules[] = new restore_decode_rule('HLSPLAYERVIEWBYID', '/mod/hlsplayer/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('HLSPLAYERINDEX', '/mod/hlsplayer/index.php?id=$1', 'course');
        return $rules;
    }

    /**
     * Defines the restore log rules.
     *
     * @return array of restore_log_rule
     */
    public static function define_restore_log_rules() {
        $rules   = [];
        $rules[] = new restore_log_rule('hlsplayer', 'add', 'view.php?id={course_module}', '{hlsplayer}');
        $rules[] = new restore_log_rule('hlsplayer', 'update', 'view.php?id={course_module}', '{hlsplayer}');
        $rules[] = new restore_log_rule('hlsplayer', 'view', 'view.php?id={course_module}', '{hlsplayer}');
        return $rules;
    }

    /**
     * Defines the restore log rules for course.
     *
     * @return array of restore_log_rule
     */
    public static function define_restore_log_rules_for_course() {
        $rules   = [];
        $rules[] = new restore_log_rule('hlsplayer', 'view all', 'index.php?id={course}', null);
        return $rules;
    }
}
