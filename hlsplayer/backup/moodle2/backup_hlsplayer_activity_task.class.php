<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/hlsplayer/backup/moodle2/backup_hlsplayer_stepslib.php');

/**
 * Provides the steps to perform one complete backup of the hlsplayer instance.
 */
class backup_hlsplayer_activity_task extends backup_activity_task {

    /**
     * No specific settings for this activity.
     */
    protected function define_my_settings() {
    }

    /**
     * Defines a backup step to store the instance data in the hlsplayer.xml file.
     */
    protected function define_my_steps() {
        $this->add_step(new backup_hlsplayer_activity_structure_step('hlsplayer_structure', 'hlsplayer.xml'));
    }

    /**
     * Encodes URLs to the index.php and view.php scripts.
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the content with the URLs encoded
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');

        // Link to the list of hlsplayers.
        $search = '/(' . $base . '\/mod\/hlsplayer\/index\.php\?id\=)([0-9]+)/';
        $content = preg_replace($search, '$@HLSPLAYERINDEX*$2@$', $content);

        // Link to hlsplayer view by moduleid.
        $search = '/(' . $base . '\/mod\/hlsplayer\/view\.php\?id\=)([0-9]+)/';
        $content = preg_replace($search, '$@HLSPLAYERVIEWBYID*$2@$', $content);

        return $content;
    }
}
