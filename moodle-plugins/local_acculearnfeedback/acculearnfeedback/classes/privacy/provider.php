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
 * Privacy Subsystem implementation for local_acculearnfeedback.
 *
 * This plugin stores the most sensitive personal data in the AccuLearn
 * system: AI-generated feedback text describing a specific student's
 * errors, their mastery/CMI/GI scores, their personalized learning
 * track narrative, their paper pre-test responses, and any teacher
 * review notes attached to that data. None of this was previously
 * covered by a privacy provider at all, so a student's "export/delete
 * my data" request via Moodle's Data Privacy tool would silently miss
 * all of it.
 *
 * The underlying tables (acculearn_feedback, acculearn_learning_tracks,
 * acculearn_pretest_responses, acculearn_review_log) are legacy tables
 * created directly in the database WITHOUT the standard Moodle table
 * prefix (see db/install.xml for the full explanation), so this
 * provider queries them with their literal names via raw SQL rather
 * than the {tablename} placeholder syntax used for normal Moodle
 * tables (e.g. {quiz_attempts}).
 *
 * Known scope limitation: acculearn_review_log's reviewer_userid
 * (the teacher who approved/flagged an item) is treated as
 * incidental metadata about the item's *subject* (the student), not
 * exported as part of the reviewing teacher's own personal-data
 * export - consistent with how Moodle's own grading/feedback privacy
 * providers treat "marker" fields.
 *
 * @package    local_acculearnfeedback
 */

namespace local_acculearnfeedback\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\transform;

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'acculearn_feedback',
            [
                'attemptid' => 'privacy:metadata:acculearn_feedback:attemptid',
                'userid' => 'privacy:metadata:acculearn_feedback:userid',
                'quizname' => 'privacy:metadata:acculearn_feedback:quizname',
                'cm' => 'privacy:metadata:acculearn_feedback:cm',
                'cmi' => 'privacy:metadata:acculearn_feedback:cmi',
                'gi' => 'privacy:metadata:acculearn_feedback:gi',
                'feedback_json' => 'privacy:metadata:acculearn_feedback:feedback_json',
                'teacher_note' => 'privacy:metadata:acculearn_feedback:teacher_note',
                'timecreated' => 'privacy:metadata:acculearn_feedback:timecreated',
            ],
            'privacy:metadata:acculearn_feedback'
        );

        $collection->add_database_table(
            'acculearn_learning_tracks',
            [
                'userid' => 'privacy:metadata:acculearn_learning_tracks:userid',
                'track_json' => 'privacy:metadata:acculearn_learning_tracks:track_json',
                'teacher_note' => 'privacy:metadata:acculearn_learning_tracks:teacher_note',
                'generated_at' => 'privacy:metadata:acculearn_learning_tracks:generated_at',
            ],
            'privacy:metadata:acculearn_learning_tracks'
        );

        $collection->add_database_table(
            'acculearn_pretest_responses',
            [
                'userid' => 'privacy:metadata:acculearn_pretest_responses:userid',
                'questionid' => 'privacy:metadata:acculearn_pretest_responses:questionid',
                'competency_id' => 'privacy:metadata:acculearn_pretest_responses:competency_id',
                'confidence' => 'privacy:metadata:acculearn_pretest_responses:confidence',
                'is_correct' => 'privacy:metadata:acculearn_pretest_responses:is_correct',
                'entered_at' => 'privacy:metadata:acculearn_pretest_responses:entered_at',
            ],
            'privacy:metadata:acculearn_pretest_responses'
        );

        $collection->add_database_table(
            'acculearn_posttest_responses',
            [
                'userid' => 'privacy:metadata:acculearn_posttest_responses:userid',
                'questionid' => 'privacy:metadata:acculearn_posttest_responses:questionid',
                'competency_id' => 'privacy:metadata:acculearn_posttest_responses:competency_id',
                'confidence' => 'privacy:metadata:acculearn_posttest_responses:confidence',
                'is_correct' => 'privacy:metadata:acculearn_posttest_responses:is_correct',
                'entered_at' => 'privacy:metadata:acculearn_posttest_responses:entered_at',
            ],
            'privacy:metadata:acculearn_posttest_responses'
        );

        $collection->add_database_table(
            'acculearn_review_log',
            [
                'target_userid' => 'privacy:metadata:acculearn_review_log:target_userid',
                'reviewer_userid' => 'privacy:metadata:acculearn_review_log:reviewer_userid',
                'entitytype' => 'privacy:metadata:acculearn_review_log:entitytype',
                'action' => 'privacy:metadata:acculearn_review_log:action',
                'note' => 'privacy:metadata:acculearn_review_log:note',
                'timecreated' => 'privacy:metadata:acculearn_review_log:timecreated',
            ],
            'privacy:metadata:acculearn_review_log'
        );

        $collection->add_external_location_link('openai', [
            'summary' => 'privacy:metadata:openai:summary',
        ], 'privacy:metadata:openai');

        return $collection;
    }

    /**
     * acculearn_feedback rows resolve to the quiz's module context (same
     * pattern as qbehaviour_confidence); the other three tables carry no
     * course/module link at all, so they resolve to the user's own context.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM acculearn_feedback af
                  JOIN {quiz_attempts} qa ON qa.id = af.attemptid
                  JOIN {modules} m ON m.name = 'quiz'
                  JOIN {course_modules} cm ON cm.instance = qa.quiz AND cm.module = m.id
                  JOIN {context} ctx ON ctx.instanceid = cm.id AND ctx.contextlevel = :contextmodule
                 WHERE af.userid = :userid1";
        $contextlist->add_from_sql($sql, [
            'userid1' => $userid,
            'contextmodule' => CONTEXT_MODULE,
        ]);

        global $DB;
        // acculearn_learning_tracks/acculearn_pretest_responses are legacy
        // UNPREFIXED tables (see the class docblock), so they must be
        // checked with a literal-name raw query - record_exists_select()
        // always applies the site prefix and would look for a table that
        // doesn't exist. acculearn_review_log genuinely is prefixed, so
        // record_exists_select() is correct for it.
        $haspersonaldata =
            (bool) $DB->get_field_sql("SELECT COUNT(*) FROM acculearn_learning_tracks WHERE userid = ?", [$userid]) ||
            (bool) $DB->get_field_sql("SELECT COUNT(*) FROM acculearn_pretest_responses WHERE userid = ?", [$userid]) ||
            // Post-test IS a prefixed, Moodle-managed table, so unlike the
            // pre-test line above it can use record_exists_select().
            $DB->record_exists_select('acculearn_posttest_responses', 'userid = ?', [$userid]) ||
            $DB->record_exists_select('acculearn_review_log', 'target_userid = ?', [$userid]);

        if ($haspersonaldata) {
            $contextlist->add_user_context($userid);
        }

        return $contextlist;
    }

    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof \context_module) {
                self::export_feedback_for_module($context, $user->id);
            } else if ($context instanceof \context_user && (int)$context->instanceid === (int)$user->id) {
                self::export_tracks_and_pretest_and_reviews($context, $user->id);
            }
        }
    }

    private static function export_feedback_for_module(\context_module $context, int $userid): void {
        global $DB;

        $cm = get_coursemodule_from_id('quiz', $context->instanceid);
        if (!$cm) {
            return;
        }

        $rows = $DB->get_records_sql(
            "SELECT af.*
               FROM acculearn_feedback af
               JOIN {quiz_attempts} qa ON qa.id = af.attemptid
              WHERE qa.quiz = :quizid AND af.userid = :userid",
            ['quizid' => $cm->instance, 'userid' => $userid]
        );

        if (!$rows) {
            return;
        }

        $data = array_values(array_map(function($r) {
            return (object) [
                'quizname' => $r->quizname,
                'competency_mastery' => $r->cm,
                'confidence_misconception_index' => $r->cmi,
                'guessing_index' => $r->gi,
                'ai_feedback' => json_decode($r->feedback_json, true),
                'teacher_note' => $r->teacher_note,
                'flagged' => (bool) $r->flagged,
                'timecreated' => transform::datetime($r->timecreated),
            ];
        }, $rows));

        writer::with_context($context)->export_data(
            [get_string('pluginname', 'local_acculearnfeedback')],
            (object) ['ai_feedback_records' => $data]
        );
    }

    private static function export_tracks_and_pretest_and_reviews(\context_user $context, int $userid): void {
        global $DB;

        $track = $DB->get_record_sql("SELECT * FROM acculearn_learning_tracks WHERE userid = ?", [$userid]);
        if ($track) {
            writer::with_context($context)->export_data(
                [get_string('pluginname', 'local_acculearnfeedback'), 'learning_track'],
                (object) [
                    'track' => json_decode($track->track_json, true),
                    'teacher_note' => $track->teacher_note,
                    'flagged' => (bool) $track->flagged,
                    'generated_at' => transform::datetime($track->generated_at),
                ]
            );
        }

        $pretest = $DB->get_records_sql(
            "SELECT * FROM acculearn_pretest_responses WHERE userid = ? ORDER BY entered_at",
            [$userid]
        );
        if ($pretest) {
            $data = array_values(array_map(function($r) {
                return (object) [
                    'questionid' => $r->questionid,
                    'competency_id' => $r->competency_id,
                    'confidence' => $r->confidence,
                    'is_correct' => (bool) $r->is_correct,
                    'entered_at' => transform::datetime($r->entered_at),
                ];
            }, $pretest));

            writer::with_context($context)->export_data(
                [get_string('pluginname', 'local_acculearnfeedback'), 'pretest_responses'],
                (object) ['responses' => $data]
            );
        }

        $posttest = $DB->get_records_sql(
            "SELECT * FROM {acculearn_posttest_responses} WHERE userid = ? ORDER BY entered_at",
            [$userid]
        );
        if ($posttest) {
            $data = array_values(array_map(function($r) {
                return (object) [
                    'questionid' => $r->questionid,
                    'competency_id' => $r->competency_id,
                    'confidence' => $r->confidence,
                    'is_correct' => (bool) $r->is_correct,
                    'entered_at' => transform::datetime($r->entered_at),
                ];
            }, $posttest));

            writer::with_context($context)->export_data(
                [get_string('pluginname', 'local_acculearnfeedback'), 'posttest_responses'],
                (object) ['responses' => $data]
            );
        }

        $reviews = $DB->get_records_sql(
            "SELECT * FROM {acculearn_review_log} WHERE target_userid = ? ORDER BY timecreated",
            [$userid]
        );
        if ($reviews) {
            $data = array_values(array_map(function($r) {
                return (object) [
                    'entitytype' => $r->entitytype,
                    'action' => $r->action,
                    'note' => $r->note,
                    'timecreated' => transform::datetime($r->timecreated),
                ];
            }, $reviews));

            writer::with_context($context)->export_data(
                [get_string('pluginname', 'local_acculearnfeedback'), 'review_history'],
                (object) ['reviews' => $data]
            );
        }
    }

    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if ($context instanceof \context_module) {
            $cm = get_coursemodule_from_id('quiz', $context->instanceid);
            if (!$cm) {
                return;
            }
            $feedbackids = $DB->get_fieldset_sql(
                "SELECT af.id FROM acculearn_feedback af
                  WHERE af.attemptid IN (SELECT id FROM {quiz_attempts} WHERE quiz = ?)",
                [$cm->instance]
            );
            if ($feedbackids) {
                list($insql, $inparams) = $DB->get_in_or_equal($feedbackids);
                $DB->execute("DELETE FROM {acculearn_review_log} WHERE entitytype = 'feedback' AND entityid $insql", $inparams);
                $DB->execute("DELETE FROM acculearn_feedback WHERE id $insql", $inparams);
            }
        } else if ($context instanceof \context_user) {
            self::delete_all_user_data($context->instanceid);
        }
    }

    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof \context_module) {
                self::delete_data_for_all_users_in_context($context);
            } else if ($context instanceof \context_user && (int)$context->instanceid === (int)$user->id) {
                self::delete_all_user_data($user->id);
            }
        }
    }

    /**
     * Deletes every AccuLearn record about this student: their feedback,
     * their learning track, their pre-test responses, and any review-log
     * entries where they are the subject. Deliberately does NOT delete
     * acculearn_review_log rows where this user is only the
     * reviewer_userid (a teacher's own review actions on OTHER students'
     * data are not this user's personal data to have erased).
     */
    private static function delete_all_user_data(int $userid): void {
        global $DB;

        // Review-log rows are keyed by (entitytype, entityid), so clear them
        // before deleting the feedback/track rows they point at, then clear
        // any remaining review-log rows where this user is the subject.
        $feedbackids = $DB->get_fieldset_sql("SELECT id FROM acculearn_feedback WHERE userid = ?", [$userid]);
        if ($feedbackids) {
            list($insql, $inparams) = $DB->get_in_or_equal($feedbackids);
            $DB->execute("DELETE FROM {acculearn_review_log} WHERE entitytype = 'feedback' AND entityid $insql", $inparams);
        }

        $trackids = $DB->get_fieldset_sql("SELECT id FROM acculearn_learning_tracks WHERE userid = ?", [$userid]);
        if ($trackids) {
            list($insql, $inparams) = $DB->get_in_or_equal($trackids);
            $DB->execute("DELETE FROM {acculearn_review_log} WHERE entitytype = 'track' AND entityid $insql", $inparams);
        }

        $DB->execute("DELETE FROM {acculearn_review_log} WHERE target_userid = ?", [$userid]);
        $DB->execute("DELETE FROM acculearn_feedback WHERE userid = ?", [$userid]);
        $DB->execute("DELETE FROM acculearn_learning_tracks WHERE userid = ?", [$userid]);
        $DB->execute("DELETE FROM acculearn_pretest_responses WHERE userid = ?", [$userid]);
        $DB->execute("DELETE FROM {acculearn_posttest_responses} WHERE userid = ?", [$userid]);
    }
}
