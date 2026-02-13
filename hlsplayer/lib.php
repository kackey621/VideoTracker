<?php
defined('MOODLE_INTERNAL') || die();

function hlsplayer_add_instance($hlsplayer, $mform = null) {
    global $DB;

    $hlsplayer->timecreated = time();
    $hlsplayer->timemodified = time();

    $id = $DB->insert_record('hlsplayer', $hlsplayer);
    $hlsplayer->id = $id;

    hlsplayer_process_file($hlsplayer);

    return $id;
}

function hlsplayer_update_instance($hlsplayer, $mform = null) {
    global $DB;

    $hlsplayer->timemodified = time();
    $hlsplayer->id = $hlsplayer->instance;

    hlsplayer_process_file($hlsplayer);

    return $DB->update_record('hlsplayer', $hlsplayer);
}

function hlsplayer_delete_instance($id) {
    global $DB;

    if (!$hlsplayer = $DB->get_record('hlsplayer', ['id' => $id])) {
        return false;
    }

    $DB->delete_records('hlsplayer', ['id' => $hlsplayer->id]);

    return true;
}

function hlsplayer_supports($feature) {
    switch($feature) {
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
        case FEATURE_COMPLETION_HAS_RULES: // Enable custom completion rules
            return true;

        default:
            return null;
    }
}

/**
 * Update grades for a given hlsplayer activity. 
 *
 * @param stdClass $hlsplayer The activity record.
 * @param int $userid Specific user only, 0 means all users.
 * @param bool $nullifnone Return null if grade does not exist.
 */
function hlsplayer_update_grades($hlsplayer, $userid=0, $nullifnone=false) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    if ($hlsplayer->grade == 0) {
        hlsplayer_grade_item_update($hlsplayer);
    } else if ($grades = hlsplayer_get_user_grades($hlsplayer, $userid)) {
        hlsplayer_grade_item_update($hlsplayer, $grades);
    } else if ($userid && $nullifnone) {
        $grade = new stdClass();
        $grade->userid = $userid;
        $grade->rawgrade = null;
        hlsplayer_grade_item_update($hlsplayer, $grade);
    } else {
        hlsplayer_grade_item_update($hlsplayer);
    }
}

/**
 * Return grade for given user or all users.
 *
 * @param stdClass $hlsplayer
 * @param int $userid optional
 * @return array
 */
function hlsplayer_get_user_grades($hlsplayer, $userid=0) {
    global $DB;

    $params = ['hlsplayerid' => $hlsplayer->id];
    $sql = "SELECT p.userid, p.percentage 
              FROM {hlsplayer_progress} p
             WHERE p.hlsplayerid = :hlsplayerid";
             
    if ($userid) {
        $params['userid'] = $userid;
        $sql .= " AND p.userid = :userid";
    }

    $progress = $DB->get_records_sql($sql, $params);
    $grades = [];

    foreach ($progress as $p) {
        // If they met the requirement, give them full points.
        // Otherwise, no grade (or 0). 
        // Here we implement: if percentage >= minview, 100% of grade. Else 0?
        // Or if grade == 0, we delete grade?
        
        $grade = new stdClass();
        $grade->userid = $p->userid;
        
        if ($hlsplayer->completionminview > 0 && $p->percentage >= $hlsplayer->completionminview) {
             // Met requirement: Maximum grade
             $grade->rawgrade = $hlsplayer->grade; 
        } else {
             // Did not meet requirement yet
             $grade->rawgrade = 0; // Or null? usually 0 if we want to show they started.
        }
        $grades[$p->userid] = $grade;
    }

    return $grades;
}

/**
 * Update/create grade item for course module.
 *
 * @param stdClass $hlsplayer
 * @param mixed $grades
 * @return int
 */
function hlsplayer_grade_item_update($hlsplayer, $grades=null) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $params = array('itemname' => $hlsplayer->name, 'idnumber' => $hlsplayer->coursemodule);

    if ($hlsplayer->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $hlsplayer->grade;
        $params['grademin']  = 0;
    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/hlsplayer', $hlsplayer->course, 'mod', 'hlsplayer', $hlsplayer->id, 0, $grades, $params);
}

/**
 * Obtain the completion state for this module.
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param int $userid The user ID.
 * @param bool $type Type of check.
 * @return bool True if completed, false if not.
 */
function hlsplayer_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    $hlsplayer = $DB->get_record('hlsplayer', ['id' => $cm->instance], '*', MUST_EXIST);

    if ($type == COMPLETION_AND) {
        // We only support 'minview' rule for now via this custom check if Moodle calls it,
        // but Moodle's standard custom rule handling typically uses get_completion_state return
        // to determine if the *custom* part is met.
        
        // However, standard Moodle completion API for 'custom rules' usually involves 
        // passing specific rule checks. 
        // Simpler approach for custom completion 'view percentage':
        // We will trigger completion update event externally when condition is met.
        // But we also need to report status if asked.
        
        $progress = $DB->get_record('hlsplayer_progress', ['hlsplayerid' => $hlsplayer->id, 'userid' => $userid]);
        if (!$progress) {
            return false;
        }
        
        if ($hlsplayer->completionminview > 0) {
            if ($progress->percentage < $hlsplayer->completionminview) {
                return false;
            }
        }
        
        return true;
    }
    
    return false;
}

/**
 * Returns all other standard capabilities for this module.
 * @return array
 */
function hlsplayer_get_completion_active_rule_descriptions($cm) {
    global $DB;
    $hlsplayer = $DB->get_record('hlsplayer', ['id' => $cm->instance], '*', MUST_EXIST);
    
    $info = [];
    if ($hlsplayer->completionminview > 0) {
        $info['completionminview'] = get_string('completionminview_desc', 'mod_hlsplayer', $hlsplayer->completionminview);
    }
    return $info;
}

function hlsplayer_process_file($hlsplayer) {
    global $DB;

    if (!isset($hlsplayer->coursemodule)) {
        // Should not happen if called via standard add_moduleinfo
        return;
    }

    $context = context_module::instance($hlsplayer->coursemodule);

    if ($hlsplayer->sourcetype == 'file' && isset($hlsplayer->videofile)) {
        $fs = get_file_storage();
        
        // Save the draft area files to the permanent area
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
 * Serves the files from the hlsplayer file area
 */
function hlsplayer_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    if ($filearea !== 'content') {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_hlsplayer/$filearea/0/$relativepath";

    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, true, $options);
}
