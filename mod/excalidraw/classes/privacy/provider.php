<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace mod_excalidraw\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem implementation for mod_excalidraw.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The collection to add metadata to.
     * @return collection The collection with metadata added.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'excalidraw_submissions',
            [
                'userid' => 'privacy:metadata:excalidraw_submissions:userid',
                'content' => 'privacy:metadata:excalidraw_submissions:content',
                'grade' => 'privacy:metadata:excalidraw_submissions:grade',
                'timecreated' => 'privacy:metadata:excalidraw_submissions:timecreated',
                'timemodified' => 'privacy:metadata:excalidraw_submissions:timemodified',
            ],
            'privacy:metadata:excalidraw_submissions'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $sql = "SELECT c.id
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {excalidraw} e ON e.id = cm.instance
            INNER JOIN {excalidraw_submissions} es ON es.excalidrawid = e.id
                 WHERE es.userid = :userid";

        $params = [
            'modname' => 'excalidraw',
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
        ];

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $sql = "SELECT es.userid
                  FROM {course_modules} cm
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {excalidraw} e ON e.id = cm.instance
            INNER JOIN {excalidraw_submissions} es ON es.excalidrawid = e.id
                 WHERE cm.id = :cmid";

        $params = [
            'modname' => 'excalidraw',
            'cmid' => $context->instanceid,
        ];

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT cm.id AS cmid,
                       es.content,
                       es.grade,
                       es.timecreated,
                       es.timemodified
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid
            INNER JOIN {excalidraw} e ON e.id = cm.instance
            INNER JOIN {excalidraw_submissions} es ON es.excalidrawid = e.id
                 WHERE c.id {$contextsql}
                   AND es.userid = :userid
              ORDER BY cm.id";

        $params = ['userid' => $user->id] + $contextparams;

        $submissions = $DB->get_recordset_sql($sql, $params);
        foreach ($submissions as $submission) {
            $context = \context_module::instance($submission->cmid);
            $contextdata = writer::with_context($context);
            $contextdata->export_data([], (object)[
                'content' => $submission->content,
                'grade' => $submission->grade,
                'timecreated' => \core_privacy\local\request\transform::datetime($submission->timecreated),
                'timemodified' => \core_privacy\local\request\transform::datetime($submission->timemodified),
            ]);
        }
        $submissions->close();
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }

        if ($cm = get_coursemodule_from_id('excalidraw', $context->instanceid)) {
            $DB->delete_records('excalidraw_submissions', ['excalidrawid' => $cm->instance]);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);
            $DB->delete_records('excalidraw_submissions', ['excalidrawid' => $instanceid, 'userid' => $userid]);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('excalidraw', $context->instanceid);

        if (!$cm) {
            return;
        }

        $userids = $userlist->get_userids();

        list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        $select = "excalidrawid = :excalidrawid AND userid $usersql";
        $params = ['excalidrawid' => $cm->instance] + $userparams;

        $DB->delete_records_select('excalidraw_submissions', $select, $params);
    }
}
