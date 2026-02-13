<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Define all the backup steps that will be used by the backup_hlsplayer_activity_task.
 */
class backup_hlsplayer_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the backup structure of the module.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        // Define each element separated.
        $hlsplayer = new backup_nested_element('hlsplayer', array('id'), array(
            'name', 'intro', 'introformat', 'sourcetype', 'videourl',
            'allowspeeds', 'allowseeking', 'completionminview', 'grade',
            'timecreated', 'timemodified'
        ));

        $progresses = new backup_nested_element('progresses');

        $progress = new backup_nested_element('progress', array('id'), array(
            'userid', 'progress', 'percentage', 'lastposition', 'timemodified'
        ));

        // Build the tree.
        $hlsplayer->add_child($progresses);
        $progresses->add_child($progress);

        // Define sources.
        $hlsplayer->set_source_table('hlsplayer', array('id' => backup::VAR_ACTIVITYID));

        // Only include user progress if user info is being backed up.
        if ($this->get_setting_value('userinfo')) {
            $progress->set_source_table('hlsplayer_progress', array('hlsplayerid' => backup::VAR_PARENTID));
        }

        // Define id annotations.
        $progress->annotate_ids('user', 'userid');

        // Define file annotations.
        $hlsplayer->annotate_files('mod_hlsplayer', 'intro', null);
        $hlsplayer->annotate_files('mod_hlsplayer', 'content', null);

        return $this->prepare_activity_structure($hlsplayer);
    }
}
