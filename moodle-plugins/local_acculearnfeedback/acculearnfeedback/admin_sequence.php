<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_login();

global $DB, $OUTPUT, $PAGE;

$context = context_system::instance();
require_capability('local/acculearnfeedback:managesequence', $context);

$PAGE->set_url('/local/acculearnfeedback/admin_sequence.php');
$PAGE->set_context($context);
$PAGE->set_title('Competency Sequence & Visibility');
// Boost paints \$PAGE->set_heading() in the full-width page-header region,
// which is a different element from the \$OUTPUT->heading() printed at the top
// of the content column - so passing the page's own title to both rendered it
// twice, one above the other. The page heading is meant to name the SITE.
$PAGE->set_heading($SITE->fullname);

/**
 * Every AccuLearn competency, in current sequence order. This same table
 * (acculearn_competencies) is read directly by the backend's MasteryEngine
 * (checkPrerequisite()) and by quizaccess_acculearn's rule.php, so changes
 * made here take effect for real LP/gating decisions immediately - this
 * page is not a cosmetic front end over separate config.
 */
function local_acculearnfeedback_get_competencies_ordered() {
    global $DB;
    return $DB->get_records_sql("SELECT * FROM acculearn_competencies ORDER BY sequence");
}

$notice = null;
$noticetype = \core\output\notification::NOTIFY_SUCCESS;

if (data_submitted() && confirm_sesskey()) {
    // formaction is either the literal 'save', or 'moveup_<id>'/'movedown_<id>'
    // for the per-row reorder buttons. Those buttons must live INSIDE the
    // same <form> as the prerequisite/gating dropdowns below (nested <form>
    // elements are invalid HTML and browsers silently mishandle them), so
    // each row's arrow is a submit button whose own name=value pair carries
    // both the action and the target row - not a separate form or a plain
    // <a href> link. A previous version used <a href> GET links gated
    // behind this same "data_submitted() && confirm_sesskey()" check;
    // data_submitted() only ever returns true for a POST request
    // (see lib/weblib.php), so those links could never actually reach this
    // code - the Move Up/Down buttons did nothing at all.
    $formaction = required_param('formaction', PARAM_ALPHANUMEXT);
    $competencies = local_acculearnfeedback_get_competencies_ordered();

    if (preg_match('/^(moveup|movedown)_(\d+)$/', $formaction, $matches)) {
        $moveaction = $matches[1];
        $competencyid = (int) $matches[2];
        $ids = array_values(array_map(fn($c) => $c->id, $competencies));
        $pos = array_search($competencyid, $ids, true);

        if ($pos !== false) {
            $swapwith = ($moveaction === 'moveup') ? $pos - 1 : $pos + 1;
            if ($swapwith >= 0 && $swapwith < count($ids)) {
                $a = $competencies[$competencyid];
                $b = $competencies[$ids[$swapwith]];
                $DB->execute("UPDATE acculearn_competencies SET sequence = ? WHERE id = ?", [$b->sequence, $a->id]);
                $DB->execute("UPDATE acculearn_competencies SET sequence = ? WHERE id = ?", [$a->sequence, $b->id]);
            }
        }
        redirect($PAGE->url);
    }

    if ($formaction === 'save') {
        $errors = [];
        $prereqs = [];
        $gating = [];

        foreach ($competencies as $c) {
            $prereqs[$c->id] = optional_param("prerequisite_{$c->id}", 0, PARAM_INT);
            $gating[$c->id] = optional_param("gating_{$c->id}", 0, PARAM_BOOL);
        }

        foreach ($prereqs as $id => $prereqid) {
            if ($prereqid === $id) {
                $errors[] = $competencies[$id]->name . ': a competency cannot be its own prerequisite.';
            } else if ($prereqid !== 0 && !isset($competencies[$prereqid])) {
                $errors[] = $competencies[$id]->name . ': selected prerequisite does not exist.';
            }
        }

        if ($errors) {
            $notice = implode(' ', $errors);
            $noticetype = \core\output\notification::NOTIFY_ERROR;
        } else {
            foreach ($competencies as $c) {
                $newprereq = $prereqs[$c->id] ?: null;
                $newgating = $gating[$c->id] ? 1 : 0;
                $DB->execute(
                    "UPDATE acculearn_competencies SET prerequisite_id = ?, gating_enabled = ? WHERE id = ?",
                    [$newprereq, $newgating, $c->id]
                );
            }
            // Prerequisite/gating changes can immediately change which
            // students should be in each course's "AccuLearn Unlocked"
            // group (used for Restrict-access-based gating beyond just
            // quiz attempts), so resync right away rather than waiting for
            // the next scheduled run or the next quiz submission.
            $synced = local_acculearnfeedback_sync_all_unlock_groups();
            redirect($PAGE->url, "Sequence settings saved. Unlock groups resynced for $synced students.",
                null, \core\output\notification::NOTIFY_SUCCESS);
        }
    }
}

$competencies = local_acculearnfeedback_get_competencies_ordered();

echo $OUTPUT->header();
echo $OUTPUT->heading('Competency Sequence & Visibility');

if ($notice) {
    echo $OUTPUT->notification($notice, $noticetype);
}

echo html_writer::start_div('al-page');

echo html_writer::div(
    'This controls the real prerequisite chain used everywhere in AccuLearn: the mastery ' .
    'engine\'s Learning Pathway (LP) computation, the learning track narrative order, and ' .
    'the quiz-attempt gate. Reordering a competency or changing its prerequisite here changes ' .
    'those decisions immediately for every student - it is not a preview. For competencies ' .
    'mapped to a course below, the same LP decision also controls membership in that course\'s ' .
    '"AccuLearn Unlocked" group - use Moodle\'s native "Restrict access -> Group" on any ' .
    'activity in that course to gate its visibility on real mastery data, not just quiz attempts.',
    'al-card al-card--accent'
);

echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

echo html_writer::start_div('al-table-wrap');
echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'width:100%;']);
echo html_writer::tag('tr',
    html_writer::tag('th', 'Order') .
    html_writer::tag('th', 'Competency') .
    html_writer::tag('th', 'Course') .
    html_writer::tag('th', 'Prerequisite') .
    html_writer::tag('th', 'Gating enabled')
);

$total = count($competencies);
$index = 0;
foreach ($competencies as $c) {
    $index++;

    // Real <button type="submit"> elements inside the one outer form
    // (not <a href> links - those can never trigger the data_submitted()
    // gate above, since that only fires on POST) and not a second nested
    // <form> (invalid HTML). Each button's own name/value pair identifies
    // which row and which direction; clicking one submits the whole form,
    // same as the "Save Changes" button, just with a different formaction.
    $movecell = html_writer::tag('strong', $index);
    if ($index > 1) {
        $movecell .= ' ' . html_writer::tag('button', '&uarr;', [
            'type' => 'submit', 'name' => 'formaction', 'value' => "moveup_{$c->id}",
            'class' => 'al-btn al-btn-sm al-btn-secondary', 'title' => 'Move up',
        ]);
    }
    if ($index < $total) {
        $movecell .= ' ' . html_writer::tag('button', '&darr;', [
            'type' => 'submit', 'name' => 'formaction', 'value' => "movedown_{$c->id}",
            'class' => 'al-btn al-btn-sm al-btn-secondary', 'title' => 'Move down',
        ]);
    }

    echo html_writer::start_tag('tr');
    echo html_writer::tag('td', $movecell);
    echo html_writer::tag('td', html_writer::tag('strong', s($c->name)) . html_writer::tag('div', s($c->code), ['style' => 'color:var(--al-ink-500); font-size:0.85em;']));

    if ($c->courseid) {
        $coursename = $DB->get_field('course', 'fullname', ['id' => $c->courseid]);
        echo html_writer::tag('td', html_writer::link(
            new moodle_url('/course/view.php', ['id' => $c->courseid]), s($coursename)
        ));
    } else {
        echo html_writer::tag('td', html_writer::tag('span', 'Not mapped yet',
            ['style' => 'color:var(--al-ink-500); font-style:italic;']));
    }

    echo html_writer::start_tag('td');
    echo html_writer::start_tag('select', ['name' => "prerequisite_{$c->id}"]);
    echo html_writer::tag('option', 'None (start of chain)', ['value' => 0, 'selected' => !$c->prerequisite_id ? 'selected' : null]);
    foreach ($competencies as $other) {
        if ($other->id == $c->id) {
            continue;
        }
        $sel = ($c->prerequisite_id == $other->id) ? ['selected' => 'selected'] : [];
        echo html_writer::tag('option', s($other->name), array_merge(['value' => $other->id], $sel));
    }
    echo html_writer::end_tag('select');
    echo html_writer::end_tag('td');

    echo html_writer::start_tag('td');
    $checked = $c->gating_enabled ? ['checked' => 'checked'] : [];
    echo html_writer::empty_tag('input', array_merge(
        ['type' => 'checkbox', 'name' => "gating_{$c->id}", 'value' => 1], $checked
    ));
    echo html_writer::tag('span', $c->gating_enabled ? ' Locked until prerequisite mastered' : ' Always visible (gate disabled)',
        ['style' => 'margin-left:6px; color:var(--al-ink-500); font-size:0.85em;']);
    echo html_writer::end_tag('td');
    echo html_writer::end_tag('tr');
}

echo html_writer::end_tag('table');
echo html_writer::end_div();
echo html_writer::empty_tag('input', [
    'type' => 'submit', 'name' => 'formaction', 'value' => 'save',
    'class' => 'al-btn al-btn-primary', 'style' => 'margin-top:16px;',
]);
echo html_writer::end_tag('form');

echo html_writer::end_div();

echo $OUTPUT->footer();
