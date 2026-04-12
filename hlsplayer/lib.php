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
 * Library of functions for mod_hlsplayer.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Add a new HLS player instance.
 *
 * @param stdClass $hlsplayer The hlsplayer form data.
 * @param moodleform|null $mform The form object.
 * @return int The new instance id.
 */
function hlsplayer_add_instance($hlsplayer, $mform = null) {
    global $DB;

    $hlsplayer->timecreated  = time();
    $hlsplayer->timemodified = time();

    $id = $DB->insert_record('hlsplayer', $hlsplayer);
    $hlsplayer->id = $id;

    hlsplayer_process_file($hlsplayer);
    hlsplayer_grade_item_update($hlsplayer);

    return $id;
}

/**
 * Update an existing HLS player instance.
 *
 * @param stdClass $hlsplayer The hlsplayer form data.
 * @param moodleform|null $mform The form object.
 * @return bool True on success.
 */
function hlsplayer_update_instance($hlsplayer, $mform = null) {
    global $DB;

    $hlsplayer->timemodified = time();
    $hlsplayer->id           = $hlsplayer->instance;

    hlsplayer_process_file($hlsplayer);
    $result = $DB->update_record('hlsplayer', $hlsplayer);
    hlsplayer_grade_item_update($hlsplayer);

    return $result;
}

/**
 * Delete an HLS player instance.
 *
 * @param int $id The instance id.
 * @return bool True on success.
 */
function hlsplayer_delete_instance($id) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    if (!$hlsplayer = $DB->get_record('hlsplayer', ['id' => $id])) {
        return false;
    }

    $DB->delete_records('hlsplayer_progress', ['hlsplayerid' => $hlsplayer->id]);
    $DB->delete_records('hlsplayer', ['id' => $hlsplayer->id]);

    grade_update('mod/hlsplayer', $hlsplayer->course, 'mod', 'hlsplayer', $hlsplayer->id, 0, null, ['deleted' => 1]);

    return true;
}

/**
 * Returns the features supported by the hlsplayer module.
 *
 * @param string $feature FEATURE_xx constant for requested feature.
 * @return mixed True if module supports feature, null if doesn't know.
 */
function hlsplayer_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        default:
            return null;
    }
}

/**
 * Returns cached course module info for hlsplayer instances.
 * This is used by Moodle's completion API to populate CM customdata
 * with active custom completion rule values.
 *
 * @param stdClass $coursemodule The course module object.
 * @return cached_cm_info|false
 */
function hlsplayer_get_coursemodule_info($coursemodule) {
    global $DB;

    if (!$hlsplayer = $DB->get_record('hlsplayer', ['id' => $coursemodule->instance])) {
        return false;
    }

    $result       = new cached_cm_info();
    $result->name = $hlsplayer->name;

    if ($hlsplayer->completionminview > 0) {
        $result->customdata['customcompletionrules']['completionminview'] = $hlsplayer->completionminview;
    }

    return $result;
}

/**
 * Update grades for a given hlsplayer activity.
 *
 * @param stdClass $hlsplayer The activity record.
 * @param int $userid Specific user only, 0 means all users.
 * @param bool $nullifnone Return null if grade does not exist.
 */
function hlsplayer_update_grades($hlsplayer, $userid = 0, $nullifnone = false) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    if ($hlsplayer->grade == 0) {
        hlsplayer_grade_item_update($hlsplayer);
    } else if ($grades = hlsplayer_get_user_grades($hlsplayer, $userid)) {
        hlsplayer_grade_item_update($hlsplayer, $grades);
    } else if ($userid && $nullifnone) {
        $grade           = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = null;
        hlsplayer_grade_item_update($hlsplayer, $grade);
    } else {
        hlsplayer_grade_item_update($hlsplayer);
    }
}

/**
 * Return grade for given user or all users.
 *
 * @param stdClass $hlsplayer The activity record.
 * @param int $userid Optional user ID.
 * @return array Array of grade objects keyed by userid.
 */
function hlsplayer_get_user_grades($hlsplayer, $userid = 0) {
    global $DB;

    $params = ['hlsplayerid' => $hlsplayer->id];
    $sql    = "SELECT p.userid, p.percentage
                 FROM {hlsplayer_progress} p
                WHERE p.hlsplayerid = :hlsplayerid";

    if ($userid) {
        $params['userid'] = $userid;
        $sql .= " AND p.userid = :userid";
    }

    $progress = $DB->get_records_sql($sql, $params);
    $grades   = [];

    foreach ($progress as $p) {
        $grade         = new stdClass();
        $grade->userid = $p->userid;

        if ($hlsplayer->completionminview > 0 && $p->percentage >= $hlsplayer->completionminview) {
            $grade->rawgrade = $hlsplayer->grade;
        } else {
            $grade->rawgrade = 0;
        }
        $grades[$p->userid] = $grade;
    }

    return $grades;
}

/**
 * Update/create grade item for the hlsplayer activity.
 *
 * @param stdClass $hlsplayer The activity record.
 * @param mixed $grades Grade data or null.
 * @return int Grade update result.
 */
function hlsplayer_grade_item_update($hlsplayer, $grades = null) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $params = ['itemname' => $hlsplayer->name];
    if (isset($hlsplayer->coursemodule) && $hlsplayer->coursemodule !== '') {
        $params['idnumber'] = $hlsplayer->coursemodule;
    }

    if (!empty($hlsplayer->grade) && $hlsplayer->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $hlsplayer->grade;
        $params['grademin']  = 0;
    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades          = null;
    }

    return grade_update('mod/hlsplayer', $hlsplayer->course, 'mod', 'hlsplayer', $hlsplayer->id, 0, $grades, $params);
}

/**
 * Process uploaded file for the hlsplayer activity.
 *
 * @param stdClass $hlsplayer The activity record with form data.
 */
function hlsplayer_process_file($hlsplayer) {
    if (!isset($hlsplayer->coursemodule)) {
        return;
    }

    $context = context_module::instance($hlsplayer->coursemodule);

    if ($hlsplayer->sourcetype == 'file' && isset($hlsplayer->videofile)) {
        file_save_draft_area_files(
            $hlsplayer->videofile,
            $context->id,
            'mod_hlsplayer',
            'content',
            0,
            ['subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => -1]
        );
    }
}

/**
 * Serves the files from the hlsplayer file area.
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param context $context The context.
 * @param string $filearea The file area name.
 * @param array $args Extra arguments.
 * @param bool $forcedownload Force download flag.
 * @param array $options Additional options.
 * @return bool False if file not found, does not return on success.
 */
function hlsplayer_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    if ($filearea !== 'content') {
        return false;
    }

    $fs           = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath     = "/$context->id/mod_hlsplayer/$filearea/0/$relativepath";

    if (!($file = $fs->get_file_by_hash(sha1($fullpath))) || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, true, $options);
}

/**
 * Extend the settings navigation for the HLS Player activity.
 *
 * @param settings_navigation $settingsnav The settings navigation object.
 * @param navigation_node|null $hlsplayernode The module navigation node.
 */
function hlsplayer_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $hlsplayernode = null) {
    global $PAGE;

    $cm = $PAGE->cm;
    if (!$cm) {
        return;
    }

    $context = $cm->context;
    if (has_capability('mod/hlsplayer:viewreport', $context)) {
        $url = new moodle_url('/mod/hlsplayer/report.php', ['id' => $cm->id]);
        $hlsplayernode->add(get_string('viewreport', 'mod_hlsplayer'), $url, navigation_node::TYPE_SETTING);
    }
}
