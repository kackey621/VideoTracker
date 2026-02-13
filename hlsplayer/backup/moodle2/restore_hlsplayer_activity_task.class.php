<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/hlsplayer/backup/moodle2/restore_hlsplayer_stepslib.php');

/**
 * Provides the steps to perform one complete restore of the hlsplayer instance.
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
    static public function define_decode_contents() {
        $contents = array();
        $contents[] = new restore_decode_content('hlsplayer', array('intro'), 'hlsplayer');
        return $contents;
    }

    /**
     * Defines the decoding rules for links belonging to the activity to be executed by the link decoder.
     *
     * @return array of restore_decode_rule
     */
    static public function define_decode_rules() {
        $rules = array();
        $rules[] = new restore_decode_rule('HLSPLAYERVIEWBYID', '/mod/hlsplayer/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('HLSPLAYERINDEX', '/mod/hlsplayer/index.php?id=$1', 'course');
        return $rules;
    }

    /**
     * Defines the restore log rules.
     *
     * @return array of restore_log_rule
     */
    static public function define_restore_log_rules() {
        $rules = array();
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
    static public function define_restore_log_rules_for_course() {
        $rules = array();
        $rules[] = new restore_log_rule('hlsplayer', 'view all', 'index.php?id={course}', null);
        return $rules;
    }
}
