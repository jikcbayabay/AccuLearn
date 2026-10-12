<?php
/**
 * One-off: replace every AccuLearn quiz bank with the reformatted question
 * banks supplied on 2026-10-05 (AccuLearn_CO1/CO2_Question_Bank.docx,
 * CO3..CO7 Reformatted.docx), transcribed into question_banks_20261005.json.
 *
 *   sudo -u www-data php local/acculearnfeedback/cli/replace_banks.php          dry run
 *   sudo -u www-data php local/acculearnfeedback/cli/replace_banks.php --apply
 *
 * Runs after difficulty_pools.php, which created the Easy/Medium/Hard pools.
 * Each formative bank gets 10 Easy, 10 Medium and 5 Hard questions; each
 * summative bank 20 of each - so no pool is short any more.
 *
 * The questions in the pools before this run are RETIRED, not deleted:
 * students' past attempts (the study's included) point at them, and the
 * mastery engine reads those attempts. They move to a "Retired" category in
 * the same bank, which no quiz slot draws from, and are marked hidden. The
 * paper pre/post-test is unaffected: it picks its items by name
 * ("Question 1".."Question 15") among the oldest copies, and the new
 * questions are named "Easy 01", "Medium 01", "Hard 01" and so on.
 *
 * One key was corrected from the source document: CO7 Formative 2, Easy 10
 * ("The Statement of Financial Position is dated as of:") was keyed B, "Only
 * the first day of the accounting period"; it is stored as C, "A specific
 * point in time". The other 673 questions whose wording matches the previous
 * bank agree with its answers, and all 78 calculation questions were checked
 * by hand.
 *
 * Re-running is safe: questions already present by name are skipped.
 */
define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/questionlib.php');

use local_acculearnfeedback\question_pools;
use core_question\local\bank\question_version_status;

[$options] = cli_get_params(['apply' => false, 'help' => false], ['h' => 'help']);
if ($options['help']) {
    echo "Dry run by default; pass --apply to make the changes.\n";
    exit(0);
}
$apply = $options['apply'];
\core\session\manager::set_user(get_admin());

$banks = json_decode(file_get_contents(__DIR__ . '/question_banks_20261005.json'), true, 512, JSON_THROW_ON_ERROR);
const RETIRED_IDNUMBER = 'acculearn_retired';

// ---------------------------------------------------------------- plan --

$plan = [];
foreach ($banks as $catid => $levels) {
    $bank = $DB->get_record('question_categories', ['id' => $catid], '*', MUST_EXIST);
    $pools = question_pools::pools_for_bank($bank);
    if (count($pools) !== 3) {
        cli_error("Bank $catid ({$bank->name}) has no Easy/Medium/Hard pools - run difficulty_pools.php first.");
    }
    $retire = [];
    foreach ($pools as $level => $pool) {
        $names = array_map(fn($i) => ucfirst($level) . ' ' . sprintf('%02d', $i), range(1, count($levels[$level])));
        [$in, $p] = $DB->get_in_or_equal($names, SQL_PARAMS_QM, 'param', false);
        // Entries in the pool that are not one of the new questions.
        $retire[$level] = $DB->get_fieldset_sql("
            SELECT qbe.id
              FROM {question_bank_entries} qbe
              JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
              JOIN {question} q ON q.id = qv.questionid
             WHERE qbe.questioncategoryid = ?
               AND qv.version = (SELECT MAX(version) FROM {question_versions} WHERE questionbankentryid = qbe.id)
               AND q.name $in", array_merge([$pool->id], $p));
        foreach ($levels[$level] as $i => $q) {
            if (count($q['opts']) !== 4 || !isset($q['correct'])) {
                cli_error("Bank $catid $level " . ($i + 1) . ': malformed');
            }
        }
    }
    $plan[$catid] = ['bank' => $bank, 'pools' => $pools, 'retire' => $retire];
    printf("%-3s %-62s retire %2d/%2d/%2d   new %2d/%2d/%2d\n", $catid, substr($bank->name, 0, 62),
        count($retire['easy']), count($retire['medium']), count($retire['hard']),
        count($levels['easy']), count($levels['medium']), count($levels['hard']));
}
$total = array_sum(array_map(fn($b) => count($b['easy']) + count($b['medium']) + count($b['hard']), $banks));
$retiring = array_sum(array_map(fn($p) => array_sum(array_map('count', $p['retire'])), $plan));
echo "\n$total new questions, $retiring current questions to retire.\n";

if (!$apply) {
    echo "Dry run. Pass --apply to make these changes.\n";
    exit(0);
}

// --------------------------------------------------------------- apply --

$qtype = question_bank::get_qtype('multichoice');
foreach ($plan as $catid => $p) {
    $bank = $p['bank'];
    $transaction = $DB->start_delegated_transaction();

    // 1. Retire the current pool questions.
    $retired = $DB->get_record('question_categories', ['contextid' => $bank->contextid, 'idnumber' => RETIRED_IDNUMBER]);
    if (!$retired) {
        $retired = (object) [
            'name' => 'Retired (before 2026-10-05)',
            'contextid' => $bank->contextid,
            'info' => 'Questions used before the question banks were replaced on 2026-10-05. Kept because past '
                . 'attempts refer to them. No quiz draws from this category.',
            'infoformat' => FORMAT_HTML,
            'stamp' => make_unique_id_code(),
            'parent' => $bank->id,
            'sortorder' => 99,
            'idnumber' => RETIRED_IDNUMBER,
        ];
        $retired->id = $DB->insert_record('question_categories', $retired);
    }
    $entries = array_merge(...array_values($p['retire']));
    if ($entries) {
        [$in, $inparams] = $DB->get_in_or_equal($entries);
        // Same context, so no files move with the questions.
        $DB->execute("UPDATE {question_bank_entries} SET questioncategoryid = ? WHERE id $in",
            array_merge([$retired->id], $inparams));
        $DB->execute("UPDATE {question_versions} SET status = ? WHERE questionbankentryid $in",
            array_merge([question_version_status::QUESTION_STATUS_HIDDEN], $inparams));
    }

    // 2. Add the new questions, with the presentation settings the bank already used.
    $model = $DB->get_record_sql("
        SELECT o.* FROM {qtype_multichoice_options} o
          JOIN {question_versions} qv ON qv.questionid = o.questionid
          JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
         WHERE qbe.questioncategoryid = ?
      ORDER BY o.id", [$retired->id], IGNORE_MULTIPLE);
    $added = 0;
    foreach ($p['pools'] as $level => $pool) {
        foreach ($banks[$catid][$level] as $i => $q) {
            $name = ucfirst($level) . ' ' . sprintf('%02d', $i + 1);
            if ($DB->record_exists_sql("
                    SELECT 1 FROM {question} qq
                      JOIN {question_versions} qv ON qv.questionid = qq.id
                      JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                     WHERE qbe.questioncategoryid = ? AND qq.name = ?", [$pool->id, $name])) {
                continue;
            }
            $form = (object) [
                'category' => $pool->id . ',' . $pool->contextid,
                'name' => $name,
                // The source is plain text stored in an HTML-format field, so
                // characters such as "&" must be escaped.
                'questiontext' => ['text' => s($q['stem']), 'format' => FORMAT_HTML],
                'generalfeedback' => ['text' => '', 'format' => FORMAT_HTML],
                'defaultmark' => 1,
                'penalty' => 0.3333333,
                'status' => question_version_status::QUESTION_STATUS_READY,
                'single' => 1,
                'shuffleanswers' => $model->shuffleanswers ?? 1,
                'answernumbering' => $model->answernumbering ?? 'abc',
                'showstandardinstruction' => $model->showstandardinstruction ?? 0,
                'shownumcorrect' => $model->shownumcorrect ?? 1,
                'correctfeedback' => ['text' => $model->correctfeedback ?? '', 'format' => FORMAT_HTML],
                'partiallycorrectfeedback' => ['text' => $model->partiallycorrectfeedback ?? '', 'format' => FORMAT_HTML],
                'incorrectfeedback' => ['text' => $model->incorrectfeedback ?? '', 'format' => FORMAT_HTML],
                'answer' => [], 'fraction' => [], 'feedback' => [],
            ];
            foreach ($q['opts'] as $j => $opt) {
                $form->answer[] = ['text' => s($opt), 'format' => FORMAT_HTML];
                $form->fraction[] = $j === $q['correct'] ? '1.0' : '0.0';
                $form->feedback[] = ['text' => '', 'format' => FORMAT_HTML];
            }
            $question = (object) ['category' => $pool->id, 'qtype' => 'multichoice'];
            $qtype->save_question($question, $form);
            $added++;
        }
    }
    $transaction->allow_commit();

    $tagged = question_pools::tag_untagged($bank->contextid);
    $slots = question_pools::rebalance_context($bank->contextid);
    printf("%-3s retired %2d, added %2d, tagged %2d, slots retargeted %2d\n", $catid, count($entries), $added, $tagged, $slots);
}

cache::make('core', 'questiondata')->purge();
echo "Done.\n";
