<?php
use mod_quiz\local\access_rule_base;
use mod_quiz\quiz_settings;

/**
 * Blocks a student from starting a new quiz attempt if their current
 * AccuLearn learning pathway (LP) for the quiz's associated competency
 * is "Prerequisite Review" (LP = 1), meaning an earlier competency has
 * not yet been mastered.
 *
 * Handles both fixed-question slots (via question_references) and
 * random-draw-from-category slots (via question_set_references), since
 * this Moodle install's quizzes use the latter.
 *
 * Teachers/managers (anyone with mod/quiz:manage) are never blocked.
 */
class quizaccess_acculearn extends access_rule_base {

    public static function make(quiz_settings $quizobj, $timenow, $canignoretimelimits) {
        return new self($quizobj, $timenow);
    }

    public function prevent_new_attempt($numprevattempts, $lastattempt) {
        global $USER, $DB;

        $context = $this->quizobj->get_context();
        if (has_capability('mod/quiz:manage', $context)) {
            return false; // Teachers/admins are never gated.
        }

        $quizid = $this->quizobj->get_quizid();
        $competencyid = $this->resolve_quiz_competency($quizid);

        if (!$competencyid) {
            return false; // Quiz not mapped to any AccuLearn competency, don't gate it.
        }

        // Admins can disable gating for a specific competency from the
        // Sequence & Visibility page (admin_sequence.php) - e.g. to let
        // students into a competency immediately without earlier mastery.
        $gatingenabled = $DB->get_field_sql(
            "SELECT gating_enabled FROM acculearn_competencies WHERE id = ?", [$competencyid]);
        if (!$gatingenabled) {
            return false;
        }

        $backendpath = '/var/www/html/acculearn-backend/mastery_engine.php';
        if (!file_exists($backendpath)) {
            return false; // Backend unavailable, fail open rather than blocking everyone.
        }

        require_once($backendpath);
        $engine = new MasteryEngine();
        $result = $engine->computeLearningPathway($USER->id, $competencyid, 'quiz');

        if ($result['lp'] === 1) {
            return get_string('prerequisiteblocked', 'quizaccess_acculearn');
        }

        // Summative gate: the summative assessment for a competency stays
        // locked until EVERY formative quiz in that same module has been
        // passed. Formatives are the practice gates (80%); the summative is
        // the mastery decision (85%), so sitting it before the practice is
        // complete defeats the point of the sequence.
        //
        // Matched on quiz name, consistent with how the competency's quizzes
        // are named across all seven courses. Fails OPEN for a quiz that
        // isn't recognised as summative, so an unexpected name can never
        // accidentally lock a formative.
        if (stripos($this->quiz->name, 'summative') !== false) {
            $status = $engine->competencyQuizStatus($USER->id, $competencyid);
            if (!$status['formatives_passed']) {
                $outstanding = [];
                foreach ($status['quizzes'] as $q) {
                    if (!$q['is_summative'] && !$q['passed']) {
                        $outstanding[] = $q['name'];
                    }
                }
                $a = new stdClass();
                $a->competency = $this->get_competency_name($competencyid);
                $a->outstanding = $outstanding ? implode(', ', $outstanding) : 'the formative quizzes';
                $a->trackurl = (new moodle_url('/local/acculearnfeedback/my_track.php'))->out(false);
                return get_string('summativeblocked', 'quizaccess_acculearn', $a);
            }
        }

        // Restudy prompt: the student has used all their attempts (the native
        // numattempts rule enforces the 3-attempt cap) and still has not
        // PASSED THIS QUIZ.
        //
        // Now keyed on this quiz's own pass mark rather than on whether the
        // whole competency is mastered. Under the old test a student who had
        // passed this quiz but not yet the summative would still be told to
        // restudy it, which was simply wrong. Every quiz now carries a real
        // gradepass (formatives 80%, summative 85%), so "failed the retake
        // sequence" has a precise meaning it did not have before.
        //
        // The message directs them to their teacher, who can grant extra
        // attempts via Moodle's user override - the escape hatch that stops
        // one quiz permanently freezing a student out of the whole course.
        $maxattempts = (int)$this->quiz->attempts;
        $thisquizpassed = false;
        foreach ($engine->competencyQuizStatus($USER->id, $competencyid)['quizzes'] as $q) {
            if ((int)$q['id'] === (int)$quizid) {
                $thisquizpassed = $q['passed'];
                break;
            }
        }
        if ($maxattempts > 0 && $numprevattempts >= $maxattempts && !$thisquizpassed) {
            $a = new stdClass();
            $a->attempts = $maxattempts;
            $a->competency = $this->get_competency_name($competencyid);
            $a->trackurl = (new moodle_url('/local/acculearnfeedback/my_track.php'))->out(false);
            return get_string('restudyprompt', 'quizaccess_acculearn', $a);
        }

        return false;
    }

    /**
     * Human-readable name of an AccuLearn competency, for the restudy prompt.
     * Uses the prefixless custom table directly (no {braces}).
     */
    private function get_competency_name($competencyid) {
        global $DB;
        $name = $DB->get_field_sql(
            "SELECT name FROM acculearn_competencies WHERE id = ?", [$competencyid]);
        return $name ?: 'this competency';
    }

    /**
     * Determines which AccuLearn competency a quiz belongs to. Tries fixed
     * question_references first, then falls back to random-draw category
     * resolution via question_set_references, since this install's quizzes
     * use random selection from a single category per quiz.
     */
    private function resolve_quiz_competency($quizid) {
        global $DB;

        // Attempt 1: fixed per-slot questions.
        $competencyid = $DB->get_field_sql("
            SELECT qc.competency_id
            FROM {quiz_slots} qs
            JOIN {question_references} qr
                ON qr.itemid = qs.id AND qr.component = 'mod_quiz' AND qr.questionarea = 'slot'
            JOIN {question_versions} qv ON qv.questionbankentryid = qr.questionbankentryid
            JOIN acculearn_question_competency qc ON qc.questionid = qv.questionid
            WHERE qs.quizid = ?
            GROUP BY qc.competency_id
            ORDER BY COUNT(*) DESC
            LIMIT 1
        ", [$quizid]);

        if ($competencyid) {
            return $competencyid;
        }

        // Attempt 2: random-draw-from-category slots.
        $filterconditions = $DB->get_records_sql("
            SELECT qsr.id, qsr.filtercondition
            FROM {quiz_slots} qs
            JOIN {question_set_references} qsr
                ON qsr.itemid = qs.id AND qsr.component = 'mod_quiz' AND qsr.questionarea = 'slot'
            WHERE qs.quizid = ?
        ", [$quizid]);

        foreach ($filterconditions as $row) {
            $decoded = json_decode($row->filtercondition, true);
            $categoryid = $decoded['filter']['category']['values'][0] ?? null;
            if (!$categoryid) {
                continue;
            }

            $competencyid = $DB->get_field_sql("
                SELECT qc.competency_id
                FROM {question_bank_entries} qbe
                JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
                JOIN acculearn_question_competency qc ON qc.questionid = qv.questionid
                WHERE qbe.questioncategoryid = ?
                GROUP BY qc.competency_id
                ORDER BY COUNT(*) DESC
                LIMIT 1
            ", [$categoryid]);

            if ($competencyid) {
                return $competencyid;
            }
        }

        return null;
    }

    /**
     * Deliberately silent on the quiz view page.
     *
     * Moodle prints every enabled rule's description() in the quiz information
     * list, next to "Attempts allowed" and "Grade to pass" - whether or not the
     * rule is actually restricting THIS student. This one used to return the
     * plugin name, so every student saw the bare phrase "AccuLearn prerequisite
     * gating" on the page they visit to check their grade: internal jargon,
     * shown even to students who were never gated, and telling them nothing
     * they could act on.
     *
     * Returning '' opts out entirely - access_manager::accumulate_messages()
     * skips empty strings, so no blank list item is left behind. This is the
     * documented default from access_rule_base ("not all rules consider
     * themselves important enough to output a description").
     *
     * NOTE: this hides only the passive notice. It does NOT weaken the gate.
     * A student who is actually blocked still gets the full explanation from
     * prevent_new_attempt() above - prerequisiteblocked, summativeblocked or
     * restudyprompt - each of which names the competency and links to My
     * Learning Track.
     *
     * @return string always empty.
     */
    public function description() {
        return '';
    }
}
