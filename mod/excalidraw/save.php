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

if ($submission) {
    // Update existing submission.
    $submission->content = $content;
    $submission->timemodified = $now;
    $DB->update_record('excalidraw_submissions', $submission);
} else {
    // Create new submission.
    $submission = new stdClass();
    $submission->excalidrawid = $excalidrawid;
    $submission->userid = $USER->id;
    $submission->content = $content;
    $submission->timecreated = $now;
    $submission->timemodified = $now;
    $submission->id = $DB->insert_record('excalidraw_submissions', $submission);
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'submissionid' => $submission->id,
    'timemodified' => $submission->timemodified
]);
