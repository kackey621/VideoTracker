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
 * Activity custom completion subclass for the HLS Player activity.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hlsplayer\completion;

use core_completion\activity_custom_completion;

defined('MOODLE_INTERNAL') || die();

/**
 * Activity custom completion subclass for the HLS Player activity.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_completion extends activity_custom_completion {

    /**
     * Fetches the completion state for a given completion rule.
     *
     * @param string $rule The completion rule.
     * @return int The completion state.
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);

        $hlsplayer = $DB->get_record('hlsplayer', ['id' => $this->cm->instance], '*', MUST_EXIST);
        $progress  = $DB->get_record('hlsplayer_progress', [
            'hlsplayerid' => $hlsplayer->id,
            'userid'      => $this->userid,
        ]);

        if ($rule === 'completionminview') {
            if (!$progress) {
                return COMPLETION_INCOMPLETE;
            }
            if ($hlsplayer->completionminview > 0 && $progress->percentage >= $hlsplayer->completionminview) {
                return COMPLETION_COMPLETE;
            }
            return COMPLETION_INCOMPLETE;
        }

        return COMPLETION_INCOMPLETE;
    }

    /**
     * Fetch the list of custom completion rules that this module defines.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return ['completionminview'];
    }

    /**
     * Returns an associative array of the descriptions of custom completion rules.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        global $DB;

        $hlsplayer         = $DB->get_record('hlsplayer', ['id' => $this->cm->instance], '*', MUST_EXIST);
        $completionminview = $hlsplayer->completionminview;

        if (empty($completionminview)) {
            return [];
        }

        return [
            'completionminview' => get_string('completionminview_desc', 'mod_hlsplayer', $completionminview),
        ];
    }

    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return [
            'completionview',
            'completionminview',
            'completiongrade',
        ];
    }
}
