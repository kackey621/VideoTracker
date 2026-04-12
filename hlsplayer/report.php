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
 * Student progress report for the HLS Player activity.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID.

$cm        = get_coursemodule_from_id('hlsplayer', $id, 0, false, MUST_EXIST);
$course    = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$hlsplayer = $DB->get_record('hlsplayer', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/hlsplayer:viewreport', $context);

$PAGE->set_url('/mod/hlsplayer/report.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($hlsplayer->name ?? '') . ': ' . get_string('report', 'mod_hlsplayer'));
$PAGE->set_heading(format_string($course->fullname ?? ''));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('report', 'mod_hlsplayer'));

// Check group mode.
$groupmode    = groups_get_activity_groupmode($cm, $course);
$currentgroup = groups_get_activity_group($cm, true);

// Get all users in the course/group.
$users = get_enrolled_users($context, '', $currentgroup, 'u.*', null, 0, 0, true);

// Get progress for these users.
$progressdata = [];
if ($users) {
    list($insql, $inparams) = $DB->get_in_or_equal(array_keys($users));
    $sql    = "SELECT userid, progress, percentage, timemodified
                 FROM {hlsplayer_progress}
                WHERE hlsplayerid = ? AND userid $insql";
    $params   = array_merge([$hlsplayer->id], $inparams);
    $records  = $DB->get_records_sql($sql, $params);

    foreach ($records as $record) {
        $progressdata[$record->userid] = $record;
    }
}

// Display table.
$table        = new html_table();
$table->head  = [
    get_string('name'),
    get_string('progress', 'mod_hlsplayer'),
    '%',
    get_string('lastaccess', 'mod_hlsplayer'),
];

foreach ($users as $user) {
    $progress   = 0;
    $percentage = 0;
    $lastaccess = '-';

    if (isset($progressdata[$user->id])) {
        $progress   = $progressdata[$user->id]->progress;
        $percentage = $progressdata[$user->id]->percentage;
        $lastaccess = userdate($progressdata[$user->id]->timemodified);
    }

    // Format progress (seconds to H:M:S).
    $formattedprogress = gmdate("H:i:s", $progress);

    $table->data[] = [
        fullname($user),
        $formattedprogress,
        $percentage . '%',
        $lastaccess,
    ];
}

echo html_writer::table($table);
echo $OUTPUT->footer();
