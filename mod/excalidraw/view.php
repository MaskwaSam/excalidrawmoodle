<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID.
$e = optional_param('e', 0, PARAM_INT);   // Excalidraw instance ID.

if ($id) {
    $cm = get_coursemodule_from_id('excalidraw', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $excalidraw = $DB->get_record('excalidraw', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $excalidraw = $DB->get_record('excalidraw', ['id' => $e], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $excalidraw->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('excalidraw', $excalidraw->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/excalidraw:view', $context);

// Completion and view logging.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Set up the page.
$PAGE->set_url('/mod/excalidraw/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($excalidraw->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Load existing submission if any.
$submission = $DB->get_record('excalidraw_submissions', [
    'excalidrawid' => $excalidraw->id,
    'userid' => $USER->id
]);

$drawingdata = '';
if ($submission && $submission->content) {
    $drawingdata = $submission->content;
}

// Add JavaScript and CSS.
$PAGE->requires->css(new moodle_url('/mod/excalidraw/styles/excalidraw.css'));
$PAGE->requires->js(new moodle_url('/mod/excalidraw/amd/src/excalidraw-bundle.js'), true);
$PAGE->requires->js_call_amd('mod_excalidraw/app', 'init', [
    'contextid' => $context->id,
    'cmid' => $cm->id,
    'excalidrawid' => $excalidraw->id,
    'drawingdata' => $drawingdata,
    'sesskey' => sesskey(),
    'strings' => [
        'saved' => get_string('saved', 'excalidraw'),
        'saveerror' => get_string('saveerror', 'excalidraw')
    ]
]);

echo $OUTPUT->header();

// Display intro if set.
if (trim(strip_tags($excalidraw->intro))) {
    echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
    echo format_module_intro('excalidraw', $excalidraw, $cm->id);
    echo $OUTPUT->box_end();
}

// Main content container.
echo '<div id="excalidraw-container" style="width: 100%; height: 80vh; border: 1px solid #ddd;"></div>';

// Save button and status.
echo '<div style="margin-top: 10px;">';
echo '<button id="excalidraw-save-btn" class="btn btn-primary">'.get_string('save', 'excalidraw').'</button>';
echo '<span id="excalidraw-status" style="margin-left: 10px;"></span>';
echo '</div>';

echo $OUTPUT->footer();
