<?php
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

class mod_hlsplayer_external extends external_api {

    public static function submit_progress_parameters() {
        return new external_function_parameters([
            'hlsplayerid' => new external_value(PARAM_INT, 'The HLS Player instance ID'),
            'progress' => new external_value(PARAM_INT, 'The progress in seconds (max viewed)'),
            'percentage' => new external_value(PARAM_INT, 'The progress viewed percentage', VALUE_DEFAULT, 0),
            'lastposition' => new external_value(PARAM_INT, 'The current playback position', VALUE_DEFAULT, 0),
        ]);
    }

    public static function submit_progress($hlsplayerid, $progress, $percentage = 0, $lastposition = 0) {
        global $DB, $USER, $CFG;
        require_once($CFG->libdir . '/completionlib.php');

        $params = self::validate_parameters(self::submit_progress_parameters(), [
            'hlsplayerid' => $hlsplayerid,
            'progress' => $progress,
            'percentage' => $percentage,
            'lastposition' => $lastposition,
        ]);

        $cm = get_coursemodule_from_instance('hlsplayer', $params['hlsplayerid']);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/hlsplayer:view', $context);
        
        $hlsplayer = $DB->get_record('hlsplayer', ['id' => $params['hlsplayerid']], '*', MUST_EXIST);

        $record = $DB->get_record('hlsplayer_progress', [
            'hlsplayerid' => $params['hlsplayerid'],
            'userid' => $USER->id,
        ]);

        $updated = false;
        if ($record) {
            if ($params['progress'] > $record->progress) {
                $record->progress = $params['progress'];
                $updated = true;
            }
            if ($params['percentage'] > $record->percentage) {
                $record->percentage = $params['percentage'];
                $updated = true;
            }
            // Always update last position
            if ($params['lastposition'] > 0) {
                $record->lastposition = $params['lastposition'];
                $updated = true;
            }
            
            if ($updated) {
                $record->timemodified = time();
                $DB->update_record('hlsplayer_progress', $record);
            }
        } else {
            $record = new stdClass();
            $record->hlsplayerid = $params['hlsplayerid'];
            $record->userid = $USER->id;
            $record->progress = $params['progress'];
            $record->percentage = $params['percentage'];
            $record->lastposition = $params['lastposition'];
            $record->timemodified = time();
            $DB->insert_record('hlsplayer_progress', $record);
            $updated = true;
        }

        // Check completion and Grading
        if ($updated && $hlsplayer->completionminview > 0) {
            if ($record->percentage >= $hlsplayer->completionminview) {
                $completion = new completion_info($DB->get_record('course', ['id' => $cm->course]));
                if ($completion->is_enabled($cm)) {
                    $completion->update_state($cm, COMPLETION_COMPLETE, $USER->id);
                }
                
                // Trigger grade update
                require_once($CFG->dirroot . '/mod/hlsplayer/lib.php');
                hlsplayer_update_grades($hlsplayer, $USER->id);
            }
        }

        return ['status' => 'ok'];
    }

    public static function submit_progress_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Status of the operation'),
        ]);
    }
}
