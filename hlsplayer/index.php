<?php

require_once('../../config.php');
require_once(__DIR__.'/lib.php');

$id = required_param('id', PARAM_INT); // Course ID

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course);

$PAGE->set_url('/mod/hlsplayer/index.php', array('id' => $course->id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context(context_course::instance($course->id));

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('modulenameplural', 'mod_hlsplayer'));

if (! $hlsplayers = get_all_instances_in_course('hlsplayer', $course)) {
    notice(get_string('novideo', 'mod_hlsplayer'), new moodle_url('/course/view.php', array('id' => $course->id)));
}

$table = new html_table();

if ($course->format == 'weeks') {
    $table->head  = array(get_string('week'), get_string('name'));
    $table->align = array('center', 'left');
} else if ($course->format == 'topics') {
    $table->head  = array(get_string('topic'), get_string('name'));
    $table->align = array('center', 'left');
} else {
    $table->head  = array(get_string('name'));
    $table->align = array('left');
}

foreach ($hlsplayers as $hlsplayer) {
    if (!$hlsplayer->visible) {
        $link = html_writer::link(
            new moodle_url('/mod/hlsplayer/view.php', array('id' => $hlsplayer->coursemodule)),
            format_string($hlsplayer->name, true),
            array('class' => 'dimmed')
        );
    } else {
        $link = html_writer::link(
            new moodle_url('/mod/hlsplayer/view.php', array('id' => $hlsplayer->coursemodule)),
            format_string($hlsplayer->name, true)
        );
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array($hlsplayer->section, $link);
    } else {
        $table->data[] = array($link);
    }
}

echo html_writer::table($table);

echo $OUTPUT->footer();
