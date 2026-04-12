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
 * External API functions for HLS Player.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hlsplayer;

use completion_info;
use context_module;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use stdClass;

/**
 * External functions for the HLS Player module.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {
    /**
     * Returns description of submit_progress() parameters.
     *
     * @return external_function_parameters
     */
    public static function submit_progress_parameters() {
        return new external_function_parameters([
            'hlsplayerid'  => new external_value(PARAM_INT, 'The HLS Player instance ID'),
            'progress'     => new external_value(PARAM_INT, 'The progress in seconds (max viewed)'),
            'percentage'   => new external_value(PARAM_INT, 'The progress viewed percentage', VALUE_DEFAULT, 0),
            'lastposition' => new external_value(PARAM_INT, 'The current playback position', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Submit user progress for an HLS Player activity.
     *
     * @param int $hlsplayerid The HLS player instance ID.
     * @param int $progress The max viewed time in seconds.
     * @param int $percentage The max viewed percentage.
     * @param int $lastposition The current playback position.
     * @return array Status result.
     */
    public static function submit_progress($hlsplayerid, $progress, $percentage = 0, $lastposition = 0) {
        global $DB, $USER, $CFG;
        require_once($CFG->libdir . '/completionlib.php');

        $params = self::validate_parameters(self::submit_progress_parameters(), [
            'hlsplayerid'  => $hlsplayerid,
            'progress'     => $progress,
            'percentage'   => $percentage,
            'lastposition' => $lastposition,
        ]);

        $cm      = get_coursemodule_from_instance('hlsplayer', $params['hlsplayerid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/hlsplayer:view', $context);

        $hlsplayer = $DB->get_record('hlsplayer', ['id' => $params['hlsplayerid']], '*', MUST_EXIST);

        $record = $DB->get_record('hlsplayer_progress', [
            'hlsplayerid' => $params['hlsplayerid'],
            'userid'      => $USER->id,
        ]);

        $updated = false;
        if ($record) {
            if ($params['progress'] > $record->progress) {
                $record->progress = $params['progress'];
                $updated          = true;
            }
            if ($params['percentage'] > $record->percentage) {
                $record->percentage = $params['percentage'];
                $updated            = true;
            }
            // Always update last position.
            if ($params['lastposition'] > 0) {
                $record->lastposition = $params['lastposition'];
                $updated              = true;
            }

            if ($updated) {
                $record->timemodified = time();
                $DB->update_record('hlsplayer_progress', $record);
            }
        } else {
            $record               = new stdClass();
            $record->hlsplayerid  = $params['hlsplayerid'];
            $record->userid       = $USER->id;
            $record->progress     = $params['progress'];
            $record->percentage   = $params['percentage'];
            $record->lastposition = $params['lastposition'];
            $record->timemodified = time();
            $DB->insert_record('hlsplayer_progress', $record);
            $updated = true;
        }

        // Check completion and grading.
        if ($updated && $hlsplayer->completionminview > 0) {
            if ($record->percentage >= $hlsplayer->completionminview) {
                $completion = new completion_info($DB->get_record('course', ['id' => $cm->course]));
                if ($completion->is_enabled($cm)) {
                    $completion->update_state($cm, COMPLETION_COMPLETE, $USER->id);
                }

                // Trigger grade update.
                require_once($CFG->dirroot . '/mod/hlsplayer/lib.php');
                hlsplayer_update_grades($hlsplayer, $USER->id);
            }
        }

        return ['status' => 'ok'];
    }

    /**
     * Returns description of submit_progress() result value.
     *
     * @return external_single_structure
     */
    public static function submit_progress_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Status of the operation'),
        ]);
    }
}
