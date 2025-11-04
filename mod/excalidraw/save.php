<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$excalidrawid = required_param('excalidrawid', PARAM_INT);
$content = required_param('content', PARAM_RAW);

require_sesskey();

$excalidraw = $DB->get_record('excalidraw', ['id' => $excalidrawid], '*', MUST_EXIST);
$course = $DB->get_record('course', ['id' => $excalidraw->course], '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('excalidraw', $excalidraw->id, $course->id, false, MUST_EXIST);

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/excalidraw:submit', $context);

// Validate JSON.
$contentdata = json_decode($content);
if (json_last_error() !== JSON_ERROR_NONE) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    die();
}

// Check if submission exists.
$submission = $DB->get_record('excalidraw_submissions', [
    'excalidrawid' => $excalidrawid,
    'userid' => $USER->id
]);

$now = time();
$fs = get_file_storage();

if ($submission) {
    // Update existing submission.
    $submission->timemodified = $now;
    $DB->update_record('excalidraw_submissions', $submission);

    // Delete old file if it exists.
    $oldfiles = $fs->get_area_files($context->id, 'mod_excalidraw', 'submissions', $submission->id, 'timemodified DESC', false);
    foreach ($oldfiles as $oldfile) {
        $oldfile->delete();
    }
} else {
    // Create new submission.
    $submission = new stdClass();
    $submission->excalidrawid = $excalidrawid;
    $submission->userid = $USER->id;
    $submission->timecreated = $now;
    $submission->timemodified = $now;
    $submission->id = $DB->insert_record('excalidraw_submissions', $submission);
}

// Save drawing data to file storage (for proper file management and exports).
$fileinfo = [
    'contextid' => $context->id,
    'component' => 'mod_excalidraw',
    'filearea' => 'submissions',
    'itemid' => $submission->id,
    'filepath' => '/',
    'filename' => 'drawing_' . $USER->id . '_' . time() . '.excalidraw',
    'userid' => $USER->id
];

$file = $fs->create_file_from_string($fileinfo, $content);

// Also store JSON in database for quick loading (keep both methods).
$submission->content = $content;
$DB->update_record('excalidraw_submissions', $submission);

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'submissionid' => $submission->id,
    'timemodified' => $submission->timemodified,
    'fileid' => $file->get_id()
]);
