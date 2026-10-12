<?php
namespace local_acculearnfeedback;

defined('MOODLE_INTERNAL') || die();

/**
 * Difficulty pools behind the AccuLearn formative and summative quizzes.
 *
 * Every quiz draws all of its questions at random from one shared question
 * bank (a mod_qbank instance on the front page, one bank per quiz). Since
 * 2026-10-05 each bank's question category holds three subcategories, Easy,
 * Medium and Hard, recognised by their idnumber (acculearn_difficulty_*), and
 * each random slot draws from exactly one of them:
 *
 *   formative quiz  - 5 slots:  2 easy,  2 medium,  1 hard
 *   summative quiz  - 30 slots: 10 easy, 10 medium, 10 hard  (50-minute limit)
 *
 * Moodle refuses to start an attempt if a random slot's category has fewer
 * questions than the slots drawing from it, so a pool that is too small has
 * its missing draws moved to a neighbouring difficulty (see SPILL) instead.
 * The slot COUNT is never changed: every slot is worth one mark and the
 * study's past attempts are graded against the quiz's existing total
 * (5 or 30), so adding or removing slots would rescale historical grades.
 * Only which pool each slot points at changes, which affects new attempts
 * only - an attempt already started keeps the questions it drew.
 *
 * rebalance_context() runs whenever a question in a bank is created, edited,
 * moved, hidden or deleted, so a teacher adding a Hard question to a pool that
 * had none takes effect on the next attempt without anyone touching the quiz.
 */
class question_pools {

    /** Level key => label, in the order slots are laid out. */
    const LEVELS = ['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard'];

    /** idnumber prefix that marks a bank subcategory as a difficulty pool. */
    const IDNUMBER_PREFIX = 'acculearn_difficulty_';

    /** Draws per attempt for each kind of quiz. */
    const TARGETS = [
        'formative' => ['easy' => 2, 'medium' => 2, 'hard' => 1],
        'summative' => ['easy' => 10, 'medium' => 10, 'hard' => 10],
    ];

    /** Summative attempt time limit, in seconds (50 minutes). */
    const SUMMATIVE_TIMELIMIT = 3000;

    /**
     * Where a short pool's missing draws go, in order of preference. A missing
     * Hard question becomes an extra Medium one (the fallback chosen for the
     * formative quizzes that have no Hard question yet), and so on outwards.
     */
    const SPILL = [
        'easy' => ['medium', 'hard'],
        'medium' => ['easy', 'hard'],
        'hard' => ['medium', 'easy'],
    ];

    /** Formative or summative, from the quiz name ("Formative Quiz 2", "Summative Quiz"). */
    public static function quiz_kind($quizname) {
        return stripos($quizname, 'summative') !== false ? 'summative' : 'formative';
    }

    /**
     * Number of questions Moodle could draw from a category: entries with at
     * least one 'ready' version. Mirrors core_question random_question_loader,
     * which picks the newest ready version, so an entry whose newest version is
     * hidden still counts while an older version of it is ready.
     */
    public static function count_drawable($categoryid) {
        global $DB;
        return (int) $DB->count_records_sql("
            SELECT COUNT(1)
              FROM {question_bank_entries} qbe
             WHERE qbe.questioncategoryid = ?
               AND EXISTS (SELECT 1
                             FROM {question_versions} qv
                             JOIN {question} q ON q.id = qv.questionid
                            WHERE qv.questionbankentryid = qbe.id
                              AND qv.status = 'ready'
                              AND q.parent = 0)", [$categoryid]);
    }

    /**
     * The difficulty subcategories of a bank category, keyed by level.
     *
     * @return \stdClass[] level => question_categories row (missing levels absent)
     */
    public static function pools_for_bank($bankcategory) {
        global $DB;
        $pools = [];
        foreach (array_keys(self::LEVELS) as $level) {
            $cat = $DB->get_record('question_categories', [
                'contextid' => $bankcategory->contextid,
                'parent' => $bankcategory->id,
                'idnumber' => self::IDNUMBER_PREFIX . $level,
            ]);
            if ($cat) {
                $pools[$level] = $cat;
            }
        }
        return $pools;
    }

    /**
     * Spreads $total draws over the pools: each level gets min(target, size),
     * and any shortfall moves to the next level in SPILL that still has
     * undrawn questions.
     *
     * @param array $targets level => wanted draws
     * @param array $sizes level => questions available
     * @return array level => draws (sums to fewer than the targets only if
     *               the bank as a whole is too small)
     */
    public static function allocate(array $targets, array $sizes) {
        $alloc = [];
        foreach ($targets as $level => $want) {
            $alloc[$level] = min($want, $sizes[$level] ?? 0);
        }
        foreach ($targets as $level => $want) {
            $missing = $want - $alloc[$level];
            foreach (self::SPILL[$level] as $to) {
                if ($missing <= 0) {
                    break;
                }
                $spare = ($sizes[$to] ?? 0) - $alloc[$to];
                $take = min($missing, max(0, $spare));
                $alloc[$to] += $take;
                $missing -= $take;
            }
        }
        return $alloc;
    }

    /**
     * The random slots of a quiz with their decoded filter, in slot order.
     * Fixed (non-random) slots are left out.
     */
    private static function random_slots($quizid) {
        global $DB;
        $rows = $DB->get_records_sql("
            SELECT qsr.id, qs.slot, qsr.filtercondition, qsr.questionscontextid
              FROM {quiz_slots} qs
              JOIN {question_set_references} qsr
                ON qsr.itemid = qs.id AND qsr.component = 'mod_quiz' AND qsr.questionarea = 'slot'
             WHERE qs.quizid = ?
          ORDER BY qs.slot", [$quizid]);
        foreach ($rows as $row) {
            $row->filter = json_decode($row->filtercondition, true);
            $row->categoryid = (int) ($row->filter['filter']['category']['values'][0] ?? 0);
        }
        return $rows;
    }

    /**
     * The bank category a quiz draws from: the parent of the difficulty pool
     * its slots point at, or the category itself before pools existed.
     */
    public static function bank_for_quiz($quizid) {
        global $DB;
        foreach (self::random_slots($quizid) as $slot) {
            $cat = $DB->get_record('question_categories', ['id' => $slot->categoryid]);
            if (!$cat) {
                continue;
            }
            if (strpos((string) $cat->idnumber, self::IDNUMBER_PREFIX) === 0) {
                return $DB->get_record('question_categories', ['id' => $cat->parent]);
            }
            return $cat;
        }
        return null;
    }

    /**
     * Everything the teacher page and rebalance need to know about one quiz.
     *
     * @return \stdClass|null null if the quiz is not drawing from difficulty pools
     */
    public static function quiz_state($quiz) {
        $bank = self::bank_for_quiz($quiz->id);
        if (!$bank) {
            return null;
        }
        $pools = self::pools_for_bank($bank);
        if (count($pools) !== count(self::LEVELS)) {
            return null;
        }
        $slots = self::random_slots($quiz->id);
        $kind = self::quiz_kind($quiz->name);

        // Leave alone any quiz that was built with a different number of
        // slots than the standard 5 or 30 - there is no target to aim at.
        $targets = self::TARGETS[$kind];
        if (array_sum($targets) !== count($slots)) {
            return null;
        }

        $sizes = [];
        $current = array_fill_keys(array_keys(self::LEVELS), 0);
        $levelbycat = [];
        foreach ($pools as $level => $cat) {
            $sizes[$level] = self::count_drawable($cat->id);
            $levelbycat[$cat->id] = $level;
        }
        foreach ($slots as $slot) {
            if (isset($levelbycat[$slot->categoryid])) {
                $current[$levelbycat[$slot->categoryid]]++;
            }
        }

        return (object) [
            'quiz' => $quiz,
            'kind' => $kind,
            'bank' => $bank,
            'pools' => $pools,
            'sizes' => $sizes,
            'targets' => $targets,
            'current' => $current,
            'alloc' => self::allocate($targets, $sizes),
            'slots' => $slots,
        ];
    }

    /**
     * Points each random slot of a quiz at the pool allocate() picks for it:
     * the first slots draw Easy, then Medium, then Hard (the quizzes shuffle
     * question order, so this layout is not what students see). Writes only
     * the slots whose pool actually changes.
     *
     * @return int number of slots retargeted
     */
    public static function rebalance_quiz($quiz) {
        global $DB;
        $state = self::quiz_state($quiz);
        if (!$state) {
            return 0;
        }

        $plan = [];
        foreach ($state->alloc as $level => $n) {
            for ($i = 0; $i < $n; $i++) {
                $plan[] = $state->pools[$level];
            }
        }
        // A bank too small to fill every slot: leave the leftover slots
        // pointing at the largest pool. Moodle will refuse new attempts until
        // a teacher adds questions, and the teacher page says so - better than
        // silently drawing duplicates.
        $largest = array_keys($state->sizes, max($state->sizes))[0];
        while (count($plan) < count($state->slots)) {
            $plan[] = $state->pools[$largest];
        }

        $changed = 0;
        $i = 0;
        foreach ($state->slots as $slot) {
            $cat = $plan[$i++];
            if ($slot->categoryid == $cat->id) {
                continue;
            }
            $filter = $slot->filter;
            $filter['filter']['category']['values'] = [(int) $cat->id];
            $filter['filter']['category']['filteroptions']['includesubcategories'] = false;
            $filter['cat'] = $cat->id . ',' . $cat->contextid;
            $DB->update_record('question_set_references', (object) [
                'id' => $slot->id,
                'filtercondition' => json_encode($filter),
                'questionscontextid' => $cat->contextid,
            ]);
            $changed++;
        }
        return $changed;
    }

    /** Rebalances every quiz drawing from a bank in the given context. */
    public static function rebalance_context($contextid) {
        global $DB;
        $quizzes = $DB->get_records_sql("
            SELECT DISTINCT q.id, q.name, q.course
              FROM {question_set_references} qsr
              JOIN {quiz_slots} qs ON qs.id = qsr.itemid
              JOIN {quiz} q ON q.id = qs.quizid
             WHERE qsr.component = 'mod_quiz' AND qsr.questionarea = 'slot'
               AND qsr.questionscontextid = ?", [$contextid]);
        $changed = 0;
        foreach ($quizzes as $quiz) {
            $changed += self::rebalance_quiz($quiz);
        }
        return $changed;
    }

    /**
     * Gives every question in a bank context a competency in
     * acculearn_question_competency, which the mastery engine, the quiz gate
     * and the reports all join on by exact question id.
     *
     * This matters because Moodle 5 stores every edit as a new question row
     * (a new version with a new id): without this, a teacher correcting a
     * typo would quietly drop that question out of every student's mastery
     * score. A new version inherits its previous version's competency; a
     * brand-new question takes the competency the rest of its bank is tagged
     * with (each bank belongs to exactly one competency).
     *
     * @return int rows added
     */
    public static function tag_untagged($contextid) {
        global $DB;

        // Unprefixed legacy table - see the acculearn table-prefix note in
        // db/upgrade.php.
        $untagged = $DB->get_records_sql("
            SELECT q.id, qv.questionbankentryid
              FROM {question_categories} qc
              JOIN {question_bank_entries} qbe ON qbe.questioncategoryid = qc.id
              JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
              JOIN {question} q ON q.id = qv.questionid
         LEFT JOIN acculearn_question_competency t ON t.questionid = q.id
             WHERE qc.contextid = ? AND q.parent = 0 AND t.id IS NULL", [$contextid]);
        if (!$untagged) {
            return 0;
        }

        $bankcompetency = $DB->get_field_sql("
            SELECT t.competency_id
              FROM {question_categories} qc
              JOIN {question_bank_entries} qbe ON qbe.questioncategoryid = qc.id
              JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
              JOIN acculearn_question_competency t ON t.questionid = qv.questionid
             WHERE qc.contextid = ?
          GROUP BY t.competency_id
          ORDER BY COUNT(1) DESC", [$contextid], IGNORE_MULTIPLE);

        $added = 0;
        foreach ($untagged as $q) {
            $competency = $DB->get_field_sql("
                SELECT t.competency_id
                  FROM {question_versions} qv
                  JOIN acculearn_question_competency t ON t.questionid = qv.questionid
                 WHERE qv.questionbankentryid = ?
              ORDER BY qv.version DESC", [$q->questionbankentryid], IGNORE_MULTIPLE) ?: $bankcompetency;
            if (!$competency) {
                continue;   // Not an AccuLearn bank.
            }
            $DB->execute("INSERT INTO acculearn_question_competency (questionid, competency_id) VALUES (?, ?)",
                [$q->id, $competency]);
            $added++;
        }
        return $added;
    }
}
