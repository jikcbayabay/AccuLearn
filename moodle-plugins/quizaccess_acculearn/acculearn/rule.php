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

        return false;
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

    public function description() {
        return get_string('pluginname', 'quizaccess_acculearn');
    }
}
