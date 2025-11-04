<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

/**
 * Add excalidraw instance.
 *
 * @param stdClass $excalidraw
 * @return int new excalidraw instance id
 */
function excalidraw_add_instance($excalidraw) {
    global $DB;

    $excalidraw->timecreated = time();
    $excalidraw->timemodified = time();

    return $DB->insert_record('excalidraw', $excalidraw);
}

/**
 * Update excalidraw instance.
 *
 * @param stdClass $excalidraw
 * @return bool true
 */
function excalidraw_update_instance($excalidraw) {
    global $DB;

    $excalidraw->timemodified = time();
    $excalidraw->id = $excalidraw->instance;

    return $DB->update_record('excalidraw', $excalidraw);
}

/**
 * Delete excalidraw instance.
 *
 * @param int $id
 * @return bool true
 */
function excalidraw_delete_instance($id) {
    global $DB;

    if (!$excalidraw = $DB->get_record('excalidraw', ['id' => $id])) {
        return false;
    }

    $cm = get_coursemodule_from_instance('excalidraw', $id);
    if ($cm) {
        $context = context_module::instance($cm->id);
        $fs = get_file_storage();

        // Delete all submission files.
        $submissions = $DB->get_records('excalidraw_submissions', ['excalidrawid' => $excalidraw->id]);
        foreach ($submissions as $submission) {
            $fs->delete_area_files($context->id, 'mod_excalidraw', 'submissions', $submission->id);
        }

        // Delete intro files.
        $fs->delete_area_files($context->id, 'mod_excalidraw', 'intro');
    }

    // Delete all submissions.
    $DB->delete_records('excalidraw_submissions', ['excalidrawid' => $excalidraw->id]);

    // Delete the instance.
    $DB->delete_records('excalidraw', ['id' => $excalidraw->id]);

    return true;
}

/**
 * Returns the information if the module supports a feature.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function excalidraw_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        default:
            return null;
    }
}

/**
 * Serve the files from the excalidraw file areas.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool false if file not found, does not return if found - just send the file
 */
function excalidraw_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $DB, $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);

    $fileareas = ['intro', 'submissions'];
    if (!in_array($filearea, $fileareas)) {
        return false;
    }

    $itemid = (int)array_shift($args);

    if ($filearea === 'submissions') {
        // Check if user owns this submission or has grading capability.
        $submission = $DB->get_record('excalidraw_submissions', ['id' => $itemid], '*', MUST_EXIST);
        if ($submission->userid != $USER->id && !has_capability('mod/excalidraw:grade', $context)) {
            return false;
        }
    }

    $fs = get_file_storage();
    $filename = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';

    $file = $fs->get_file($context->id, 'mod_excalidraw', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }

    send_stored_file($file, null, 0, $forcedownload, $options);
}

/**
 * Update grades in the gradebook.
 *
 * @param stdClass $excalidraw
 * @param int $userid specific user only, 0 means all
 * @param bool $nullifnone
 */
function excalidraw_update_grades($excalidraw, $userid = 0, $nullifnone = true) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    if ($excalidraw->grade == 0) {
        excalidraw_grade_item_update($excalidraw);
    } else if ($grades = excalidraw_get_user_grades($excalidraw, $userid)) {
        excalidraw_grade_item_update($excalidraw, $grades);
    } else if ($userid && $nullifnone) {
        $grade = new stdClass();
        $grade->userid = $userid;
        $grade->rawgrade = null;
        excalidraw_grade_item_update($excalidraw, $grade);
    } else {
        excalidraw_grade_item_update($excalidraw);
    }
}

/**
 * Create or update grade item.
 *
 * @param stdClass $excalidraw
 * @param mixed $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function excalidraw_grade_item_update($excalidraw, $grades = null) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $params = ['itemname' => $excalidraw->name];

    if ($excalidraw->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax'] = $excalidraw->grade;
        $params['grademin'] = 0;
    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/excalidraw', $excalidraw->course, 'mod', 'excalidraw',
                        $excalidraw->id, 0, $grades, $params);
}

/**
 * Get user grades.
 *
 * @param stdClass $excalidraw
 * @param int $userid
 * @return array
 */
function excalidraw_get_user_grades($excalidraw, $userid = 0) {
    global $DB;

    $params = ['excalidrawid' => $excalidraw->id];
    if ($userid) {
        $params['userid'] = $userid;
    }

    $submissions = $DB->get_records('excalidraw_submissions', $params);
    $grades = [];

    foreach ($submissions as $submission) {
        if ($submission->grade !== null) {
            $grades[$submission->userid] = (object)[
                'userid' => $submission->userid,
                'rawgrade' => $submission->grade,
            ];
        }
    }

    return $grades;
}

/**
 * Get the latest file for a submission.
 *
 * @param int $contextid Context ID
 * @param int $submissionid Submission ID
 * @return stored_file|false The file or false if not found
 */
function excalidraw_get_submission_file($contextid, $submissionid) {
    $fs = get_file_storage();
    $files = $fs->get_area_files($contextid, 'mod_excalidraw', 'submissions', $submissionid, 'timemodified DESC', false);

    if (!empty($files)) {
        return reset($files); // Get the first (most recent) file.
    }

    return false;
}

/**
 * Get all files for a submission.
 *
 * @param int $contextid Context ID
 * @param int $submissionid Submission ID
 * @return array Array of stored_file objects
 */
function excalidraw_get_submission_files($contextid, $submissionid) {
    $fs = get_file_storage();
    return $fs->get_area_files($contextid, 'mod_excalidraw', 'submissions', $submissionid, 'timemodified DESC', false);
}
