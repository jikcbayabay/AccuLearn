<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_login();

global $DB, $OUTPUT, $PAGE;

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url('/local/acculearnfeedback/admin_sequence.php');
$PAGE->set_context($context);
$PAGE->set_title('Competency Sequence & Visibility');
$PAGE->set_heading('Competency Sequence & Visibility');

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
    $formaction = required_param('formaction', PARAM_ALPHA);
    $competencies = local_acculearnfeedback_get_competencies_ordered();

    if ($formaction === 'moveup' || $formaction === 'movedown') {
        $competencyid = required_param('competencyid', PARAM_INT);
        $ids = array_values(array_map(fn($c) => $c->id, $competencies));
        $pos = array_search($competencyid, $ids, true);

        if ($pos !== false) {
            $swapwith = ($formaction === 'moveup') ? $pos - 1 : $pos + 1;
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
            redirect($PAGE->url, 'Sequence settings saved.', null, \core\output\notification::NOTIFY_SUCCESS);
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
    'those decisions immediately for every student - it is not a preview.',
    'al-card al-card--accent'
);

echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'formaction', 'value' => 'save']);

echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'width:100%;']);
echo html_writer::tag('tr',
    html_writer::tag('th', 'Order') .
    html_writer::tag('th', 'Competency') .
    html_writer::tag('th', 'Prerequisite') .
    html_writer::tag('th', 'Gating enabled')
);

$total = count($competencies);
$index = 0;
foreach ($competencies as $c) {
    $index++;
    $moveupurl = new moodle_url($PAGE->url, ['formaction' => 'moveup', 'competencyid' => $c->id, 'sesskey' => sesskey()]);
    $movedownurl = new moodle_url($PAGE->url, ['formaction' => 'movedown', 'competencyid' => $c->id, 'sesskey' => sesskey()]);

    $movecell = html_writer::tag('strong', $index);
    if ($index > 1) {
        $movecell .= ' ' . html_writer::link($moveupurl, '&uarr;', ['class' => 'al-btn al-btn-sm al-btn-secondary', 'title' => 'Move up']);
    }
    if ($index < $total) {
        $movecell .= ' ' . html_writer::link($movedownurl, '&darr;', ['class' => 'al-btn al-btn-sm al-btn-secondary', 'title' => 'Move down']);
    }

    echo html_writer::start_tag('tr');
    echo html_writer::tag('td', $movecell);
    echo html_writer::tag('td', html_writer::tag('strong', s($c->name)) . html_writer::tag('div', $c->code, ['style' => 'color:var(--al-ink-500); font-size:0.85em;']));

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
echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => 'Save Changes', 'class' => 'al-btn al-btn-primary', 'style' => 'margin-top:16px;']);
echo html_writer::end_tag('form');

echo html_writer::end_div();

echo $OUTPUT->footer();
