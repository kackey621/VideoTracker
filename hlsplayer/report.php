<?php
require('../../config.php');
require_once(__DIR__.'/lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID

$cm = get_coursemodule_from_id('hlsplayer', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$hlsplayer = $DB->get_record('hlsplayer', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/hlsplayer:viewreport', $context);

$PAGE->set_url('/mod/hlsplayer/report.php', array('id' => $cm->id));
$PAGE->set_title(format_string($hlsplayer->name ?? '') . ': ' . get_string('report', 'mod_hlsplayer'));
$PAGE->set_heading(format_string($course->fullname ?? ''));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('report', 'mod_hlsplayer'));

// Check group mode
$groupmode = groups_get_activity_groupmode($cm, $course);
$currentgroup = groups_get_activity_group($cm, true);

// Get all users in the course/group
$users = get_enrolled_users($context, '', $currentgroup, 'u.*', null, 0, 0, true);

// Get progress for these users
$progressData = [];
if ($users) {
    list($insql, $inparams) = $DB->get_in_or_equal(array_keys($users));
    $sql = "SELECT userid, progress, percentage, timemodified 
            FROM {hlsplayer_progress} 
            WHERE hlsplayerid = ? AND userid $insql";
    $params = array_merge([$hlsplayer->id], $inparams);
    $records = $DB->get_records_sql($sql, $params);
    
    foreach ($records as $record) {
        $progressData[$record->userid] = $record;
    }
}

// Display table
$table = new html_table();
$table->head = [
    get_string('name'), 
    get_string('progress', 'mod_hlsplayer'), 
    '%',
    get_string('lastaccess', 'mod_hlsplayer')
];

foreach ($users as $user) {
    $progress = 0;
    $percentage = 0;
    $lastAccess = '-';
    
    if (isset($progressData[$user->id])) {
        $progress = $progressData[$user->id]->progress;
        $percentage = $progressData[$user->id]->percentage;
        $lastAccess = userdate($progressData[$user->id]->timemodified);
    }
    
    // Format progress (seconds to H:M:S)
    $formattedProgress = gmdate("H:i:s", $progress);

    $table->data[] = [
        fullname($user),
        $formattedProgress,
        $percentage . '%',
        $lastAccess
    ];
}

echo html_writer::table($table);

echo $OUTPUT->footer();
