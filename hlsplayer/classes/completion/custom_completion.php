<?php

namespace mod_hlsplayer\completion;

use core_completion\activity_custom_completion;

defined('MOODLE_INTERNAL') || die();

/**
 * Activity custom completion subclass for the HLS Player activity.
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
        $progress = $DB->get_record('hlsplayer_progress', [
            'hlsplayerid' => $hlsplayer->id,
            'userid' => $this->userid,
        ]);

        if ($rule == 'completionminview') {
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

        $hlsplayer = $DB->get_record('hlsplayer', ['id' => $this->cm->instance], '*', MUST_EXIST);
        $completionminview = $hlsplayer->completionminview;

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
