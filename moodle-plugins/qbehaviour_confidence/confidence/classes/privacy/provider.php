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
 * Privacy Subsystem implementation for qbehaviour_confidence.
 *
 * This plugin stores one row per answered question containing the
 * student's self-reported confidence level (1-3), tied directly to
 * userid/attemptid/questionattemptid. That is personal data, so this
 * MUST be a real plugin\provider, not a null_provider - an earlier
 * version of this file was a leftover copy of Moodle core's
 * qbehaviour_deferredfeedback null_provider (wrong namespace, and
 * incorrectly claimed no personal data was stored).
 *
 * @package    qbehaviour_confidence
 */

namespace qbehaviour_confidence\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\transform;

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'qbehaviour_confidence',
            [
                'attemptid' => 'privacy:metadata:qbehaviour_confidence:attemptid',
                'questionattemptid' => 'privacy:metadata:qbehaviour_confidence:questionattemptid',
                'userid' => 'privacy:metadata:qbehaviour_confidence:userid',
                'slot' => 'privacy:metadata:qbehaviour_confidence:slot',
                'confidence' => 'privacy:metadata:qbehaviour_confidence:confidence',
                'timecreated' => 'privacy:metadata:qbehaviour_confidence:timecreated',
            ],
            'privacy:metadata:qbehaviour_confidence'
        );

        return $collection;
    }

    /**
     * Every module-context quiz for which this user has a confidence rating.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $sql = "SELECT ctx.id
                  FROM {qbehaviour_confidence} qc
                  JOIN {quiz_attempts} qa ON qa.id = qc.attemptid
                  JOIN {modules} m ON m.name = 'quiz'
                  JOIN {course_modules} cm ON cm.instance = qa.quiz AND cm.module = m.id
                  JOIN {context} ctx ON ctx.instanceid = cm.id AND ctx.contextlevel = :contextmodule
                 WHERE qc.userid = :userid";

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, ['userid' => $userid, 'contextmodule' => CONTEXT_MODULE]);
        return $contextlist;
    }

    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if (!$context instanceof \context_module) {
            return;
        }
        $cm = get_coursemodule_from_id('quiz', $context->instanceid);
        if (!$cm) {
            return;
        }

        $sql = "SELECT qc.userid
                  FROM {qbehaviour_confidence} qc
                  JOIN {quiz_attempts} qa ON qa.id = qc.attemptid
                 WHERE qa.quiz = :quizid";
        $userlist->add_from_sql('userid', $sql, ['quizid' => $cm->instance]);
    }

    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id('quiz', $context->instanceid);
            if (!$cm) {
                continue;
            }

            $records = $DB->get_records_sql(
                "SELECT qc.attemptid, qc.slot, qc.confidence, qc.timecreated
                   FROM {qbehaviour_confidence} qc
                   JOIN {quiz_attempts} qa ON qa.id = qc.attemptid
                  WHERE qa.quiz = :quizid AND qc.userid = :userid
                  ORDER BY qc.attemptid, qc.slot",
                ['quizid' => $cm->instance, 'userid' => $user->id]
            );

            if (!$records) {
                continue;
            }

            $data = array_values(array_map(function($r) {
                return (object) [
                    'attemptid' => $r->attemptid,
                    'slot' => $r->slot,
                    'confidence' => $r->confidence,
                    'timecreated' => transform::datetime($r->timecreated),
                ];
            }, $records));

            writer::with_context($context)->export_data(
                [get_string('pluginname', 'qbehaviour_confidence')],
                (object) ['confidence_ratings' => $data]
            );
        }
    }

    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }
        $cm = get_coursemodule_from_id('quiz', $context->instanceid);
        if (!$cm) {
            return;
        }

        $DB->delete_records_select(
            'qbehaviour_confidence',
            "attemptid IN (SELECT id FROM {quiz_attempts} WHERE quiz = :quizid)",
            ['quizid' => $cm->instance]
        );
    }

    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id('quiz', $context->instanceid);
            if (!$cm) {
                continue;
            }

            $DB->delete_records_select(
                'qbehaviour_confidence',
                "userid = :userid AND attemptid IN (SELECT id FROM {quiz_attempts} WHERE quiz = :quizid)",
                ['userid' => $user->id, 'quizid' => $cm->instance]
            );
        }
    }

    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof \context_module) {
            return;
        }
        $cm = get_coursemodule_from_id('quiz', $context->instanceid);
        if (!$cm) {
            return;
        }

        $userids = $userlist->get_userids();
        if (!$userids) {
            return;
        }

        list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $inparams['quizid'] = $cm->instance;

        $DB->delete_records_select(
            'qbehaviour_confidence',
            "attemptid IN (SELECT id FROM {quiz_attempts} WHERE quiz = :quizid) AND userid $insql",
            $inparams
        );
    }
}
