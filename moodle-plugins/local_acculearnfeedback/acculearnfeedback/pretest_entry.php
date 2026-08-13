<?php
require_once(__DIR__ . '/../../config.php');
// Required for local_acculearnfeedback_log_usage(). This page previously used
// no lib.php helpers at all, so the include was missing - adding the usage-log
// call without it made saving pre-test responses fail with "Call to undefined
// function".
require_once(__DIR__ . '/lib.php');
require_login();

global $DB, $OUTPUT, $PAGE, $SESSION;

$context = context_system::instance();
require_capability('local/acculearnfeedback:managesequence', $context);

$PAGE->set_url('/local/acculearnfeedback/pretest_entry.php');
$PAGE->set_context($context);
$PAGE->set_title('Enter Pre/Post-Test Results');
// Boost paints $PAGE->set_heading() in the full-width page-header region,
// which is a different element from the $OUTPUT->heading() printed at the top
// of the content column - so passing the page's own title to both rendered it
// twice, one above the other. The page heading is meant to name the SITE.
$PAGE->set_heading($SITE->fullname);

// Which student/competency is currently being worked on lives in the
// session, not the URL. The previous version carried userid/competencyid
// as GET query parameters, so every reload, bookmark, browser-history
// entry, and screen-share put a specific student's database ID in the
// visible address bar. Storing it server-side instead means this page's
// URL is always just /local/acculearnfeedback/pretest_entry.php, no
// matter which student is selected.
if (optional_param('clearselection', 0, PARAM_BOOL)) {
    unset($SESSION->acculearn_pretest_userid);
    unset($SESSION->acculearn_pretest_competencyid);
    // Which phase is being entered is deliberately NOT cleared: staff work
    // through a whole stack of post-test papers in one sitting, so resetting
    // to pre-test between students would be a trap.
    redirect($PAGE->url);
}

// This page previously opened its OWN raw PDO connection here, reading the
// database password straight off disk with
// file_get_contents('/var/www/html/acculearn-backend/.dbpass'). That was
// unnecessary and risky: Moodle's $DB connection is already available and
// already authenticated, so a web-facing page had no reason to touch the
// credential file at all. Any PDO exception thrown during connection can
// include the DSN (and, in some PHP/driver configurations, connection
// details) in its message and stack trace, so reading secrets in a request
// path that can render errors to a browser is a needless disclosure risk -
// and it also bypassed Moodle's transaction and read-replica handling.
// All queries below now go through $DB, using the legacy tables' literal
// unprefixed names (see db/install.xml for why those aren't prefixed).

if (data_submitted() && confirm_sesskey()) {
    $formaction = optional_param('formaction', '', PARAM_ALPHA);

    if ($formaction === 'select') {
        // Just updates which student/competency/phase is "current" - not itself
        // a data-changing action, but still POST+sesskey rather than a
        // GET query string, so it never appears in the URL.
        $SESSION->acculearn_pretest_userid = optional_param('userid', 0, PARAM_INT);
        $SESSION->acculearn_pretest_competencyid = optional_param('competencyid', 0, PARAM_INT);
        $SESSION->acculearn_pretest_phase = local_acculearnfeedback_test_phase(
            optional_param('phase', 'pre', PARAM_ALPHA))->key;
        redirect($PAGE->url);
    }

    if ($formaction === 'saveresponses') {
        $selecteduserid = $SESSION->acculearn_pretest_userid ?? 0;
        $competencyid = $SESSION->acculearn_pretest_competencyid ?? 0;
        // Re-resolved from the session, never from the submitted form, so a
        // tampered or stale POST cannot write post-test answers into the
        // pre-test baseline table (or the reverse).
        $phase = local_acculearnfeedback_test_phase($SESSION->acculearn_pretest_phase ?? 'pre');

        if ($selecteduserid && $competencyid) {
            $questionids = required_param_array('questionid', PARAM_INT);
            $now = time();

            $saved = 0;
            foreach ($questionids as $qid) {
                $confidence = optional_param("confidence_$qid", 0, PARAM_INT);
                $correct = optional_param("correct_$qid", -1, PARAM_INT);
                if ($confidence > 0 && $correct >= 0) {
                    // {$phase->table} is a fixed string from
                    // local_acculearnfeedback_test_phases(), never user input,
                    // and already carries the right prefix convention for the
                    // phase - see that function for why the two differ.
                    $DB->execute("
                        INSERT INTO {$phase->table}
                            (userid, questionid, competency_id, confidence, is_correct, entered_at)
                        VALUES (?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            confidence = VALUES(confidence),
                            is_correct = VALUES(is_correct),
                            entered_at = VALUES(entered_at)
                    ", [$selecteduserid, $qid, $competencyid, $confidence, $correct, $now]);
                    $saved++;
                }
            }
            // Logged against the STUDENT, not the staff member, because this
            // is an event in that student's participation record - but the
            // detail names who actually entered it, so the audit trail is
            // still complete.
            $compname = $DB->get_field_sql(
                "SELECT name FROM acculearn_competencies WHERE id = ?", [$competencyid]);
            local_acculearnfeedback_log_usage($selecteduserid, $phase->logaction, 'competency',
                $competencyid,
                "{$phase->label} results entered for {$compname}: {$saved} item(s), recorded by "
                    . fullname($USER), null);

            redirect($PAGE->url, "Saved $saved {$phase->label} responses.", null,
                \core\output\notification::NOTIFY_SUCCESS);
        }
    }
}

$selecteduserid = $SESSION->acculearn_pretest_userid ?? 0;
$competencyid = $SESSION->acculearn_pretest_competencyid ?? 0;
$phase = local_acculearnfeedback_test_phase($SESSION->acculearn_pretest_phase ?? 'pre');

echo $OUTPUT->header();
echo $OUTPUT->heading('Enter ' . $phase->label . ' Results');

echo html_writer::start_div('al-page');

// Student selector. Same non-staff predicate as the worklist below, and for
// the same reason: this previously required a 'student' role assignment, so a
// newly created account that had not been enrolled yet could not be selected
// at all - meaning staff physically could not enter the pre-test for the
// student the system was telling to go ask them about it.
$students = [];
foreach ($DB->get_records_sql("
    SELECT u.id, u.firstname, u.lastname
    FROM {user} u
    WHERE u.deleted = 0
      AND u.suspended = 0
      AND u.username <> 'guest'
    ORDER BY u.lastname, u.firstname
") as $cand) {
    if (has_capability('local/acculearnfeedback:reviewaioutput', $context, $cand->id)) {
        continue;
    }
    $students[$cand->id] = $cand;
}

// Who is still waiting. Students see a banner telling them their pre-test
// hasn't been recorded, so staff need the matching worklist - otherwise the
// only way to find out who is missing was to click through every student in
// the dropdown one at a time. A missing pre-test is not cosmetic: it is the
// baseline the student's whole personalised path is computed from, and it
// also feeds the study's pre/post comparison.
// Deliberately NOT keyed on "has a student role assignment". Accounts are
// created by an administrator before enrolment (Process Rule 1), so there is
// a real window where a genuine new student exists with no role yet - and
// that student is exactly who this feature is for. A role-based query missed
// them, which produced the worst possible mismatch: the student sees a banner
// saying "ask your teacher about the pre-test" while being invisible on the
// teacher's worklist.
//
// Instead this mirrors the banner's own condition exactly - not staff, and no
// pre-test on record - so the two can never disagree. The capability check has
// to happen in PHP rather than SQL, which is fine at this cohort size (the
// study runs with ~25 students).
$candidates = $DB->get_records_sql("
    SELECT u.id, u.firstname, u.lastname
    FROM {user} u
    WHERE u.deleted = 0
      AND u.suspended = 0
      AND u.username <> 'guest'
      AND NOT EXISTS (SELECT 1 FROM {$phase->table} p WHERE p.userid = u.id)
    ORDER BY u.lastname, u.firstname
");

$awaiting = [];
foreach ($candidates as $cand) {
    if (has_capability('local/acculearnfeedback:reviewaioutput', $context, $cand->id)) {
        continue; // Staff - they enter the results, they don't sit the pre-test.
    }
    $awaiting[$cand->id] = $cand;
}

if ($awaiting) {
    echo html_writer::start_div('al-card al-card--flag-needs', ['style' => 'margin-bottom:16px;']);
    echo html_writer::tag('h4',
        '<i class="fa fa-clipboard-list" aria-hidden="true"></i>Awaiting ' . strtolower($phase->label)
        . ' entry (' . count($awaiting) . ')',
        ['style' => 'margin-top:0;']);
    echo html_writer::tag('p',
        $phase->key === 'pre'
            ? 'These students have no pre-test responses recorded yet, so AccuLearn cannot compute a '
                . 'personalised learning path for them. They are currently seeing a prompt asking them to '
                . 'speak to you about the pre-test.'
            : 'These students have no post-test responses recorded yet. The post-test is the study\'s '
                . 'end-point measure, so a missing one leaves that student out of the pre/post '
                . 'comparison. Nothing is shown to the student about this.',
        ['style' => 'color:var(--al-ink-500); font-size:0.9em;']);
    $names = [];
    foreach ($awaiting as $a) {
        $names[] = html_writer::tag('span', s($a->lastname . ', ' . $a->firstname),
            ['class' => 'al-badge al-badge--neutral', 'style' => 'margin:0 6px 6px 0; display:inline-block;']);
    }
    echo html_writer::div(implode('', $names));
    echo html_writer::end_div();
} else {
    echo html_writer::div(
        '<i class="fa fa-circle-check" aria-hidden="true"></i> Every enrolled student has '
        . strtolower($phase->label) . ' responses recorded.',
        'al-card al-card--flag-mastered',
        ['style' => 'margin-bottom:16px;']);
}

echo html_writer::start_div('al-card', ['style' => 'display:flex; flex-wrap:wrap; align-items:center; gap:20px;']);
echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url, 'style' => 'display:flex; flex-wrap:wrap; align-items:center; gap:20px;']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'formaction', 'value' => 'select']);

// Which paper is being transcribed. First control on the bar, because it
// decides which table everything below it reads and writes - a member of staff
// must never be able to type a stack of post-test papers into the pre-test
// baseline without noticing.
echo html_writer::tag('span', 'Assessment:', ['style' => 'font-weight:600; color:var(--al-ink-700);']);
echo html_writer::start_tag('select', ['name' => 'phase', 'onchange' => 'this.form.submit()']);
foreach (local_acculearnfeedback_test_phases() as $p) {
    $sel = ($p->key === $phase->key) ? ['selected' => 'selected'] : [];
    echo html_writer::tag('option', $p->label, array_merge(['value' => $p->key], $sel));
}
echo html_writer::end_tag('select');

echo html_writer::tag('span', 'Student:', ['style' => 'font-weight:600; color:var(--al-ink-700);']);
echo html_writer::start_tag('select', ['name' => 'userid', 'onchange' => 'this.form.submit()']);
echo html_writer::tag('option', '-- select student --', ['value' => 0]);
foreach ($students as $s) {
    $sel = ($s->id == $selecteduserid) ? ['selected' => 'selected'] : [];
    // s() is required here: student names are user-supplied data, so an
    // unescaped name containing markup would be stored XSS against every
    // admin who opens this page.
    echo html_writer::tag('option', s($s->lastname . ', ' . $s->firstname), array_merge(['value' => $s->id], $sel));
}
echo html_writer::end_tag('select');

echo html_writer::tag('span', 'Competency:', ['style' => 'font-weight:600; color:var(--al-ink-700);']);
$competencies = $DB->get_records_sql("SELECT * FROM acculearn_competencies ORDER BY sequence");
echo html_writer::start_tag('select', ['name' => 'competencyid', 'onchange' => 'this.form.submit()']);
echo html_writer::tag('option', '-- select competency --', ['value' => 0]);
foreach ($competencies as $c) {
    $sel = ($c->id == $competencyid) ? ['selected' => 'selected'] : [];
    echo html_writer::tag('option', s($c->name), array_merge(['value' => $c->id], $sel));
}
echo html_writer::end_tag('select');
echo html_writer::end_tag('form');

if ($selecteduserid || $competencyid) {
    echo html_writer::link(new moodle_url($PAGE->url, ['clearselection' => 1]), 'Clear selection',
        ['class' => 'al-btn al-btn-sm al-btn-secondary']);
}
echo html_writer::end_div();

if ($selecteduserid && $competencyid) {
    // Just the ten items that were on the paper, not the whole question bank -
    // see local_acculearnfeedback_pretest_items() for why the bank maps 80-140
    // rows to each competency.
    $questionrows = local_acculearnfeedback_pretest_items($competencyid);

    $existingmap = [];
    $existingrows = $DB->get_records_sql(
        "SELECT id, questionid, confidence, is_correct FROM {$phase->table}
          WHERE userid = ? AND competency_id = ?",
        [$selecteduserid, $competencyid]
    );
    foreach ($existingrows as $e) {
        $existingmap[$e->questionid] = $e;
    }

    // The other phase's count, so staff can see at a glance whether this
    // student's pre/post pair is complete without switching back and forth.
    $otherphase = local_acculearnfeedback_test_phase($phase->key === 'pre' ? 'post' : 'pre');
    $othercount = (int) $DB->get_field_sql(
        "SELECT COUNT(*) FROM {$otherphase->table} WHERE userid = ? AND competency_id = ?",
        [$selecteduserid, $competencyid]);

    echo html_writer::start_div('al-card al-card--accent', ['style' => 'display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;']);
    $answered = 0;
    foreach ($questionrows as $q) {
        if (isset($existingmap[$q->id])) {
            $answered++;
        }
    }
    echo html_writer::tag('div',
        html_writer::tag('span', $phase->label, ['class' => 'al-badge al-badge--pill',
            'style' => 'margin-right:8px;']) .
        'The ' . count($questionrows) . ' items from the paper ' . strtolower($phase->label)
        . ', in paper order. '
        . html_writer::tag('strong', $answered . ' of ' . count($questionrows))
        . ' recorded for this student. '
        . html_writer::tag('span',
            $otherphase->label . ': ' . $othercount . ' of ' . count($questionrows) . ' recorded.',
            ['style' => 'color:var(--al-ink-500);']),
        ['style' => 'margin:0;']);

    // Track generation belongs to the PRE-test only. The initial track is the
    // student's baseline placement; the post-test is an end-point measurement
    // taken after instruction, and re-deriving a "personalised path" from it
    // would rewrite a student's guidance at the very moment the study is
    // measuring them. The button is therefore hidden entirely in post mode
    // rather than left to be clicked by mistake.
    if ($phase->key === 'pre') {
        // A real POST+sesskey action (not a plain <a href> link) - this
        // triggers a live OpenAI call and writes learning-track data, so it
        // must not be triggerable by a bare GET request (which browsers issue
        // automatically for things like <img>/<link> tags on any page, making
        // an un-protected GET side effect a CSRF risk). It also reads the
        // target student from the session rather than a query parameter, so
        // it never needs to appear in generate_pretest_track.php's URL either.
        echo html_writer::start_tag('form', ['method' => 'post', 'action' => new moodle_url('/local/acculearnfeedback/generate_pretest_track.php')]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
        echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => 'Generate Initial Track for this Student', 'class' => 'al-btn al-btn-success']);
        echo html_writer::end_tag('form');
    }
    echo html_writer::end_div();

    echo html_writer::div($phase->blurb, 'al-card',
        ['style' => 'font-size:0.9em; color:var(--al-ink-700); padding:12px 16px;']);

    echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'formaction', 'value' => 'saveresponses']);

    echo html_writer::start_div('al-table-wrap');
    echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'width:100%']);
    echo html_writer::tag('tr',
        html_writer::tag('th', 'Question') .
        html_writer::tag('th', 'Text') .
        html_writer::tag('th', 'Correct?') .
        html_writer::tag('th', 'Confidence')
    );

    // Numbered 1..10 down the page. The question bank's own names run
    // 1-5 then 11-15, and printing those made a ten-row form appear to skip
    // five items - a transcription screen should count the way the person
    // transcribing counts. The bank name is kept as a tooltip so a row can
    // still be traced back to its question when checking the mapping; the
    // hidden questionid is what actually gets saved, so renumbering the label
    // cannot move a response onto the wrong question.
    $position = 0;
    foreach ($questionrows as $q) {
        $position++;
        $prevconf = $existingmap[$q->id]->confidence ?? 0;
        $prevcorrect = isset($existingmap[$q->id]) ? $existingmap[$q->id]->is_correct : -1;

        echo html_writer::start_tag('tr');
        echo html_writer::tag('td',
            html_writer::tag('span', 'Question ' . $position,
                ['title' => 'Question bank item: ' . s($q->name)]) .
            html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'questionid[]', 'value' => $q->id])
        );
        echo html_writer::tag('td', s(strip_tags($q->questiontext)), ['style' => 'max-width:350px;']);

        echo html_writer::start_tag('td');
        echo html_writer::start_div('al-radio-pills');
        foreach (['1' => 'Correct', '0' => 'Incorrect'] as $val => $label) {
            $id = "correct_{$q->id}_{$val}";
            $checked = ($prevcorrect == $val) ? ['checked' => 'checked'] : [];
            echo html_writer::empty_tag('input', array_merge(
                ['type' => 'radio', 'name' => "correct_{$q->id}", 'id' => $id, 'value' => $val], $checked
            ));
            echo html_writer::label($label, $id, true);
        }
        echo html_writer::end_div();
        echo html_writer::end_tag('td');

        echo html_writer::start_tag('td');
        echo html_writer::start_div('al-radio-pills');
        foreach (['1' => 'Low', '2' => 'Medium', '3' => 'High'] as $val => $label) {
            $id = "confidence_{$q->id}_{$val}";
            $checked = ($prevconf == $val) ? ['checked' => 'checked'] : [];
            echo html_writer::empty_tag('input', array_merge(
                ['type' => 'radio', 'name' => "confidence_{$q->id}", 'id' => $id, 'value' => $val], $checked
            ));
            echo html_writer::label($label, $id, true);
        }
        echo html_writer::end_div();
        echo html_writer::end_tag('td');

        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('table');
    echo html_writer::end_div();
    echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => 'Save Responses', 'class' => 'al-btn al-btn-primary', 'style' => 'margin-top:16px;']);
    echo html_writer::end_tag('form');
}

echo html_writer::end_div();

echo $OUTPUT->footer();
