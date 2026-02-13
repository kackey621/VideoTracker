<?php

namespace mod_hlsplayer\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy API provider for the HLS Player plugin.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Returns meta data about this plugin.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this plugin.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('hlsplayer_progress', [
            'userid' => 'privacy:metadata:hlsplayer_progress:userid',
            'progress' => 'privacy:metadata:hlsplayer_progress:progress',
            'percentage' => 'privacy:metadata:hlsplayer_progress:percentage',
            'lastposition' => 'privacy:metadata:hlsplayer_progress:lastposition',
            'timemodified' => 'privacy:metadata:hlsplayer_progress:timemodified',
        ], 'privacy:metadata:hlsplayer_progress');

        return $collection;
    }

    /**
     * Get the list of contexts where a user has stored data.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts for the user.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $contextlist->add_from_sql('SELECT c.id
                                      FROM {context} c
                                      JOIN {course_modules} cm ON cm.id = c.instanceid . AND c.contextlevel = :contextlevel
                                      JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                                      JOIN {hlsplayer} h ON h.id = cm.instance
                                      JOIN {hlsplayer_progress} hp ON hp.hlsplayerid = h.id
                                     WHERE hp.userid = :userid',
            [
                'contextlevel' => CONTEXT_MODULE,
                'modname' => 'hlsplayer',
                'userid' => $userid,
            ]
        );
        return $contextlist;
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
        $params = $contextparams + ['modname' => 'hlsplayer', 'contextlevel' => CONTEXT_MODULE, 'userid' => $user->id];

        $sql = "SELECT cm.id AS cmid, hp.progress, hp.percentage, hp.lastposition, hp.timemodified
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {hlsplayer} h ON h.id = cm.instance
                  JOIN {hlsplayer_progress} hp ON hp.hlsplayerid = h.id
                 WHERE c.id $contextsql AND hp.userid = :userid";

        $records = $DB->get_recordset_sql($sql, $params);

        foreach ($records as $record) {
            $context = \context_module::instance($record->cmid);
            $data = (object) [
                'progress' => $record->progress,
                'percentage' => $record->percentage . '%',
                'lastposition' => $record->lastposition,
                'timemodified' => transform::datetime($record->timemodified),
            ];
            writer::with_context($context)->export_data([get_string('pluginname', 'mod_hlsplayer')], $data);
        }
        $records->close();
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        if (!$cm = $DB->get_record('course_modules', ['id' => $context->instanceid])) {
            return;
        }

        if (!$hlsplayer = $DB->get_record('hlsplayer', ['id' => $cm->instance])) {
            return;
        }

        $DB->delete_records('hlsplayer_progress', ['hlsplayerid' => $hlsplayer->id]);
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
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }
            if (!$cm = $DB->get_record('course_modules', ['id' => $context->instanceid])) {
                continue;
            }
            if (!$hlsplayer = $DB->get_record('hlsplayer', ['id' => $cm->instance])) {
                continue;
            }
            $DB->delete_records('hlsplayer_progress', ['hlsplayerid' => $hlsplayer->id, 'userid' => $userid]);
        }
    }
}
