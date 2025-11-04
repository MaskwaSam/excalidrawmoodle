<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT); // Course ID.

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

require_login($course);

$coursecontext = context_course::instance($course->id);

$PAGE->set_url('/mod/excalidraw/index.php', ['id' => $id]);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($coursecontext);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'excalidraw'));

$excalidraws = get_all_instances_in_course('excalidraw', $course);

if (empty($excalidraws)) {
    notice(get_string('noexcalidraws', 'excalidraw'), new moodle_url('/course/view.php', ['id' => $course->id]));
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';
$table->head = [
    get_string('name'),
    get_string('intro', 'excalidraw')
];
$table->align = ['left', 'left'];

foreach ($excalidraws as $excalidraw) {
    $link = html_writer::link(
        new moodle_url('/mod/excalidraw/view.php', ['id' => $excalidraw->coursemodule]),
        format_string($excalidraw->name)
    );

    $intro = format_module_intro('excalidraw', $excalidraw, $excalidraw->coursemodule);

    $table->data[] = [$link, $intro];
}

echo html_writer::table($table);
echo $OUTPUT->footer();
