<?php
require('../../config.php');
require_once(__DIR__.'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID
$n  = optional_param('n', 0, PARAM_INT);  // HLSPlayer instance ID

if ($id) {
    $cm         = get_coursemodule_from_id('hlsplayer', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $hlsplayer  = $DB->get_record('hlsplayer', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $hlsplayer  = $DB->get_record('hlsplayer', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $hlsplayer->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('hlsplayer', $hlsplayer->id, $course->id, false, MUST_EXIST);
} else {
    print_error('missingidandn');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/hlsplayer:view', $context);

// Completion
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/hlsplayer/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($hlsplayer->name ?? ''));
$PAGE->set_heading(format_string($course->fullname ?? ''));

echo $OUTPUT->header();



// Determine Video Source
$streamUrl = '';
if ($hlsplayer->sourcetype === 'url') {
    $streamUrl = $hlsplayer->videourl;
} else {
    // File source
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_hlsplayer', 'content', 0, 'sortorder DESC, id ASC', false);
    
    // Find the .m3u8 file
    foreach ($files as $file) {
        $filename = $file->get_filename();
        if (substr($filename, -5) === '.m3u8') {
             $streamUrl = moodle_url::make_pluginfile_url(
                $context->id,
                'mod_hlsplayer',
                'content',
                0,
                '/',
                $filename
            )->out(false);
            break;
        }
    }
}

// Get current progress
$currentProgress = 0; // Max viewed time (for seek restriction)
$lastPosition = 0;   // Where to resume
$progressRecord = $DB->get_record('hlsplayer_progress', ['hlsplayerid' => $hlsplayer->id, 'userid' => $USER->id]);
if ($progressRecord) {
    $currentProgress = $progressRecord->progress;
    $lastPosition = $progressRecord->lastposition;
}

if ($streamUrl) {
    // Speed settings
    $playbackRates = $hlsplayer->allowspeeds ? [0.5, 1, 1.5, 2] : [];

    $templateContext = [
        'stream_url' => $streamUrl,
        'width' => '100%',
        'height' => 'auto',
        'id' => uniqid('hls-player-'),
        'playback_rates' => json_encode($playbackRates),
        'allowseeking' => $hlsplayer->allowseeking,
        'initial_progress' => $currentProgress, // Max viewed
        'initial_lastposition' => $lastPosition, // Resume point
        'cmid' => $cm->id,
        'hlsplayerid' => $hlsplayer->id,
        'wwwroot' => $CFG->wwwroot,
        'sesskey' => sesskey(),
    ];
    echo $OUTPUT->render_from_template('mod_hlsplayer/player', $templateContext);
} else {
    echo $OUTPUT->notification(get_string('novideo', 'mod_hlsplayer'), 'warning');
}

echo $OUTPUT->footer();
