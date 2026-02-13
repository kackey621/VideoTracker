<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_hlsplayer_mod_form extends moodleform_mod {

    function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name', 'mod_hlsplayer'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', 'error', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        $mform->addElement('header', 'content', get_string('content', 'mod_hlsplayer'));

        $options = [
            'url' => get_string('sourcetype_url', 'mod_hlsplayer'),
            'file' => get_string('sourcetype_file', 'mod_hlsplayer'),
        ];
        $mform->addElement('select', 'sourcetype', get_string('sourcetype', 'mod_hlsplayer'), $options);
        $mform->setDefault('sourcetype', 'url');

        // Playback Speed Option
        $mform->addElement('checkbox', 'allowspeeds', get_string('allowspeeds', 'mod_hlsplayer'));
        $mform->setDefault('allowspeeds', 0);

        $mform->addElement('checkbox', 'allowseeking', get_string('allowseeking', 'mod_hlsplayer'));
        $mform->setDefault('allowseeking', 0); // Default to restricted
        $mform->addHelpButton('allowseeking', 'allowseeking', 'mod_hlsplayer');

        // URL Field
        $mform->addElement('text', 'videourl', get_string('videourl', 'mod_hlsplayer'), array('size'=>'64'));
        $mform->setType('videourl', PARAM_URL);
        $mform->hideIf('videourl', 'sourcetype', 'eq', 'file');

        // File Field
        $filemanageroptions = [];
        $filemanageroptions['accepted_types'] = ['.m3u8']; // Strict M3U8 only
        $filemanageroptions['maxbytes'] = 0;
        $filemanageroptions['maxfiles'] = -1; // Allow multiple files (segments)
        $filemanageroptions['mainfile'] = true; // Identify the m3u8 as main

        $mform->addElement('filemanager', 'videofile', get_string('videofile', 'mod_hlsplayer'), null, $filemanageroptions);
        $mform->hideIf('videofile', 'sourcetype', 'eq', 'url');

        // Completion settings
        $mform->addElement('header', 'completionheader', get_string('completion', 'mod_hlsplayer'));
        $mform->addElement('text', 'completionminview', get_string('completionminview', 'mod_hlsplayer'));
        $mform->setType('completionminview', PARAM_INT);
        $mform->setDefault('completionminview', 95);
        $mform->addHelpButton('completionminview', 'completionminview', 'mod_hlsplayer');
        
        $this->standard_grading_coursemodule_elements();
        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['sourcetype'] == 'url') {
            if (empty($data['videourl'])) {
                $errors['videourl'] = get_string('required');
            } else {
                $url = parse_url($data['videourl'], PHP_URL_PATH);
                if (substr($url, -5) !== '.m3u8') {
                    $errors['videourl'] = get_string('error_invalidurl', 'mod_hlsplayer');
                }
            }
        }

        return $errors;
    }

    public function data_preprocessing(&$default_values) {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('videofile');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_hlsplayer', 'content', 0, array('subdirs' => 0));
            $default_values['videofile'] = $draftitemid;
        }
    }
}
