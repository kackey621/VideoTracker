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
 * View page for the HLS Player activity module.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once(__DIR__ . '/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID.
$n  = optional_param('n', 0, PARAM_INT);  // HLSPlayer instance ID.

if ($id) {
    $cm        = get_coursemodule_from_id('hlsplayer', $id, 0, false, MUST_EXIST);
    $course    = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $hlsplayer = $DB->get_record('hlsplayer', ['id' => $cm->instance], '*', MUST_EXIST);
} else if ($n) {
    $hlsplayer = $DB->get_record('hlsplayer', ['id' => $n], '*', MUST_EXIST);
    $course    = $DB->get_record('course', ['id' => $hlsplayer->course], '*', MUST_EXIST);
    $cm        = get_coursemodule_from_instance('hlsplayer', $hlsplayer->id, $course->id, false, MUST_EXIST);
} else {
    throw new \moodle_exception('missingidandn');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/hlsplayer:view', $context);

// Trigger course module viewed event.
$event = \mod_hlsplayer\event\course_module_viewed::create([
    'objectid' => $hlsplayer->id,
    'context'  => $context,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('hlsplayer', $hlsplayer);
$event->trigger();

// Completion: mark as viewed.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/hlsplayer/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($hlsplayer->name ?? ''));
$PAGE->set_heading(format_string($course->fullname ?? ''));

// Load Video.js from CDN.
$PAGE->requires->css(new moodle_url('https://vjs.zencdn.net/8.10.0/video-js.css'));
$PAGE->requires->js(new moodle_url('https://vjs.zencdn.net/8.10.0/video.min.js'), true);

echo $OUTPUT->header();

// Determine Video Source.
$streamurl = '';
if ($hlsplayer->sourcetype === 'url') {
    $streamurl = $hlsplayer->videourl;
} else {
    // File source.
    $fs    = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_hlsplayer', 'content', 0, 'sortorder DESC, id ASC', false);

    // Find the .m3u8 file.
    foreach ($files as $file) {
        $filename = $file->get_filename();
        if (substr($filename, -5) === '.m3u8') {
            $streamurl = moodle_url::make_pluginfile_url(
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

// Get current progress.
$currentprogress = 0; // Max viewed time (for seek restriction).
$lastposition    = 0; // Where to resume.
$progressrecord  = $DB->get_record('hlsplayer_progress', [
    'hlsplayerid' => $hlsplayer->id,
    'userid'      => $USER->id,
]);
if ($progressrecord) {
    $currentprogress = $progressrecord->progress;
    $lastposition    = $progressrecord->lastposition;
}

if ($streamurl) {
    // Speed settings.
    $playbackrates = $hlsplayer->allowspeeds ? [0.5, 1, 1.5, 2] : [1];

    $templatecontext = [
        'stream_url'            => $streamurl,
        'id'                    => uniqid('hls-player-'),
        'playback_rates'        => json_encode($playbackrates),
        'allowseeking'          => (bool) $hlsplayer->allowseeking,
        'initial_progress'      => (int) $currentprogress,
        'initial_lastposition'  => (int) $lastposition,
        'cmid'                  => $cm->id,
        'hlsplayerid'           => $hlsplayer->id,
    ];
    echo $OUTPUT->render_from_template('mod_hlsplayer/player', $templatecontext);
} else {
    echo $OUTPUT->notification(get_string('novideo', 'mod_hlsplayer'), 'warning');
}

echo $OUTPUT->footer();
