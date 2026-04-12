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
 * Activity module form for HLS Player.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * HLS Player module form class.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_hlsplayer_mod_form extends moodleform_mod {
    /**
     * Define the form elements.
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name', 'mod_hlsplayer'), ['size' => '64']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', 'mod_hlsplayer', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        $mform->addElement('header', 'content', get_string('content', 'mod_hlsplayer'));

        $options = [
            'url'  => get_string('sourcetype_url', 'mod_hlsplayer'),
            'file' => get_string('sourcetype_file', 'mod_hlsplayer'),
        ];
        $mform->addElement('select', 'sourcetype', get_string('sourcetype', 'mod_hlsplayer'), $options);
        $mform->setDefault('sourcetype', 'url');

        // Playback Speed Option.
        $mform->addElement('checkbox', 'allowspeeds', get_string('allowspeeds', 'mod_hlsplayer'));
        $mform->setDefault('allowspeeds', 0);

        $mform->addElement('checkbox', 'allowseeking', get_string('allowseeking', 'mod_hlsplayer'));
        $mform->setDefault('allowseeking', 0);
        $mform->addHelpButton('allowseeking', 'allowseeking', 'mod_hlsplayer');

        // URL Field.
        $mform->addElement('text', 'videourl', get_string('videourl', 'mod_hlsplayer'), ['size' => '64']);
        $mform->setType('videourl', PARAM_URL);
        $mform->hideIf('videourl', 'sourcetype', 'eq', 'file');

        // File Field.
        $filemanageroptions                   = [];
        $filemanageroptions['accepted_types'] = ['.m3u8', '.ts', '.aac', '.mp4', '.key'];
        $filemanageroptions['maxbytes']       = 0;
        $filemanageroptions['maxfiles']       = -1;
        $filemanageroptions['mainfile']       = true;

        $mform->addElement('filemanager', 'videofile', get_string('videofile', 'mod_hlsplayer'), null, $filemanageroptions);
        $mform->hideIf('videofile', 'sourcetype', 'eq', 'url');

        $this->standard_grading_coursemodule_elements();
        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    /**
     * Add completion rules to the form.
     *
     * @return array List of element names added.
     */
    public function add_completion_rules() {
        $mform = $this->_form;

        $mform->addElement('text', 'completionminview', get_string('completionminview', 'mod_hlsplayer'), ['size' => 3]);
        $mform->setType('completionminview', PARAM_INT);
        $mform->addHelpButton('completionminview', 'completionminview', 'mod_hlsplayer');

        return ['completionminview'];
    }

    /**
     * Determine if the completion rule is enabled for given form data.
     *
     * @param array $data The submitted form data.
     * @return bool True if the completion rule is enabled.
     */
    public function completion_rule_enabled($data) {
        return !empty($data['completionminview']);
    }

    /**
     * Validate the form data.
     *
     * @param array $data The submitted form data.
     * @param array $files The submitted files.
     * @return array List of errors keyed by field name.
     */
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

    /**
     * Prepare form data before setting defaults.
     *
     * @param array $defaultvalues The default values.
     */
    public function data_preprocessing(&$defaultvalues) {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('videofile');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_hlsplayer', 'content', 0, ['subdirs' => 0]);
            $defaultvalues['videofile'] = $draftitemid;
        }
    }
}
