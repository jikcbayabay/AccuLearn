<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_login();

global $DB, $OUTPUT, $PAGE, $USER;

$context = context_system::instance();
// Hiding the toolbar link is not access control - gate the page too.
require_capability('local/acculearnfeedback:viewowntrack', $context);
$PAGE->set_url('/local/acculearnfeedback/my_track.php');
$PAGE->set_context($context);
$PAGE->set_title('My Learning Track');
// Boost paints $PAGE->set_heading() in the full-width page-header region,
// which is a different element from the $OUTPUT->heading() printed at the top
// of the content column - so passing the page's own title to both rendered it
// twice, one above the other. The page heading is meant to name the SITE.
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading('My Learning Track');

local_acculearnfeedback_log_usage($USER->id, 'view_track', 'track', null,
    'Opened their personalised learning track');

$trackrow = $DB->get_record_sql("SELECT * FROM acculearn_learning_tracks WHERE userid = ?", [$USER->id]);

if (!$trackrow) {
    echo html_writer::start_div('al-page');
    echo html_writer::div(
        html_writer::div('<i class="fa fa-route" aria-hidden="true"></i>', 'al-empty__icon') .
        html_writer::div('Your track isn\'t ready yet', 'al-empty__title') .
        html_writer::div('This usually appears once your pre-test results are entered, or after you complete your first quiz.', 'al-empty__sub'),
        'al-card al-empty'
    );
    echo html_writer::end_div();
    echo $OUTPUT->footer();
    exit;
}

// Guard the decode. track_json is AI-generated content stored verbatim, so a
// truncated or malformed response is a real possibility - and reading
// $track['narrative'] straight off a null decode fatals the page, leaving the
// student with a blank screen rather than a usable message.
$track = json_decode($trackrow->track_json, true);

// Swap the [STUDENT] token the AI was given for this student's own name.
$track = local_acculearnfeedback_personalise($track, $USER->firstname);
if (!is_array($track) || empty($track['competencies'])) {
    echo html_writer::start_div('al-page');
    echo html_writer::div(
        html_writer::div('<i class="fa fa-triangle-exclamation" aria-hidden="true"></i>', 'al-empty__icon') .
        html_writer::div('Your track could not be displayed', 'al-empty__title') .
        html_writer::div('It will rebuild automatically after your next quiz submission. '
            . 'Tell your teacher if it keeps happening.', 'al-empty__sub'),
        'al-card al-empty'
    );
    echo html_writer::end_div();
    echo $OUTPUT->footer();
    exit;
}

$narrative = $track['narrative'] ?? [];
$competencies = $track['competencies'];

// Same teacher-override rule as view.php: a flagged track must stop showing
// its AI prose, or flagging changes nothing the student actually reads. Only
// the narrative is withheld - everything below it is recomputed from live
// mastery data rather than generated, so suppressing it would hide the
// student's real progress for no reason.
$narrativeflagged = !empty($trackrow->flagged);
if ($narrativeflagged) {
    $narrative = [];
}

// The stored track is a SNAPSHOT, rewritten only when its evidence signature
// changes - so its per-quiz checklist and mastery figures can lag the real
// gradebook (a quiz regraded outside a submission, or a formative passed
// without moving a mastery band, both leave it behind). Everything below the
// narrative is deterministic and cheap to recompute, so it is recomputed here
// against live data; only the AI prose comes from storage. Falls back to the
// snapshot if the backend is unavailable.
$live = local_acculearnfeedback_live_competencies($USER->id, $track['source'] ?? 'quiz');
if ($live) {
    $competencies = $live;
}

// Each competency is its own Moodle course, so its name links to the material.
$courselinks = local_acculearnfeedback_competency_courses();

echo html_writer::start_div('al-page');

// Anonymization badge: shows the student their own pseudonym so they can
// recognize their row when the teacher discusses results anonymously.
echo html_writer::div(
    'You are ' . html_writer::tag('strong', local_acculearnfeedback_pseudonym($USER->id)),
    'al-badge al-badge--pill',
    ['style' => 'margin-bottom:16px;']
);

// Narrative card
echo html_writer::start_div('al-card al-card--accent', ['style' => 'margin-bottom:24px;']);
echo html_writer::tag('h4', '<i class="fa fa-compass" aria-hidden="true"></i>Overview', ['style' => 'margin-top:0;']);
echo html_writer::tag('p', s($narrative['overview'] ?? ''));
echo html_writer::tag('h5', '<i class="fa fa-bullseye" aria-hidden="true"></i>What to focus on next');
echo html_writer::tag('p', s($narrative['next_focus'] ?? ''));
echo html_writer::tag('p', s($narrative['encouragement'] ?? ''), ['style' => 'font-style:italic; color:var(--al-blue);']);
// The prose is only rewritten when the student's evidence materially changes,
// while the cards below are recomputed live on every view. Dating the summary
// keeps that difference honest rather than implying both were written now.
echo html_writer::tag('div',
    'Summary written ' . userdate($trackrow->generated_at, get_string('strftimedatetimeshort')),
    ['style' => 'color:var(--al-ink-500); font-size:0.8em; margin-top:10px;']);
echo html_writer::end_div();

// Competency cards, grouped by the adaptive priority the mastery engine
// assigned. The old view was a flat co1..co7 list in curriculum order, which
// looked identical for every student regardless of what they actually knew.
// Nothing is skipped or hidden here - every competency still appears and is
// still required; only the ORDER and the grouping adapt to the student.
$groups = [
    'next' => [
        'heading' => 'Do this next',
        'icon' => 'fa-play-circle',
        'blurb' => 'Your prerequisites are met here, so this is where your effort pays off right now.',
    ],
    'revisit' => [
        'heading' => 'Worth revisiting',
        'icon' => 'fa-triangle-exclamation',
        'blurb' => 'You passed these, but answered confidently and incorrectly often enough that something '
            . 'may be misunderstood. Clearing it up now stops it following you into later topics.',
    ],
    'upcoming' => [
        'heading' => 'Coming up',
        'icon' => 'fa-road',
        'blurb' => 'Still ahead of you. These unlock as you master what they build on.',
    ],
    'maintain' => [
        'heading' => 'Keep fresh',
        'icon' => 'fa-circle-check',
        'blurb' => 'Already mastered. Optional review, weakest first - revisit any time.',
    ],
];

$bucketed = [];
$hasgrouping = false;
foreach ($competencies as $c) {
    if (!empty($c['priority_group'])) {
        $hasgrouping = true;
    }
    $bucketed[$c['priority_group'] ?? 'upcoming'][] = $c;
}

// A track generated before adaptive ordering existed has no priority_group.
// Rather than mislabel it, fall back to the plain list; it regenerates into
// the grouped view automatically on the student's next quiz submission,
// because priority_group is part of the track's evidence signature.
if (!$hasgrouping) {
    $groups = ['upcoming' => [
        'heading' => 'Your progress by competency',
        'icon' => 'fa-chart-line',
        'blurb' => 'Your personalised ordering will appear here after your next quiz submission.',
    ]];
    $bucketed = ['upcoming' => $competencies];
}

// 'Insufficient Evidence' means the score looks decent but rests on too few
// answered items to justify calling it mastered (see MIN_ITEMS_FOR_MASTERY in
// mastery_engine.php). Styled as developing rather than mastered so it never
// reads as a pass the student hasn't actually earned.
$masterytones = [
    'Mastered' => 'mastered',
    'Developing' => 'developing',
    'Insufficient Evidence' => 'developing',
    'Needs Improvement' => 'needs',
    'Not Attempted' => 'neutral',
];
$masterydots = [
    'Mastered' => '#72b579',
    'Developing' => '#d9a441',
    'Insufficient Evidence' => '#d9a441',
    'Needs Improvement' => '#c97064',
    'Not Attempted' => '#9ca3af',
];
$masteryflags = [
    'Mastered' => 'mastered',
    'Developing' => 'developing',
    'Insufficient Evidence' => 'developing',
    'Needs Improvement' => 'needs',
    'Not Attempted' => 'neutral',
];

foreach ($groups as $key => $group) {
    if (empty($bucketed[$key])) {
        continue;
    }

    echo html_writer::tag('h4',
        '<i class="fa ' . $group['icon'] . '" aria-hidden="true"></i>' . s($group['heading']),
        ['style' => 'margin-top:28px;']);
    echo html_writer::tag('p', s($group['blurb']),
        ['style' => 'color:var(--al-ink-500); font-size:0.9em; margin-top:-6px;']);

    foreach ($bucketed[$key] as $c) {
        $tone = $masterytones[$c['mastery']] ?? 'neutral';
        $flag = $masteryflags[$c['mastery']] ?? 'neutral';
        $dot = $masterydots[$c['mastery']] ?? '#9ca3af';

        // The single recommended competency gets the accent treatment so it
        // reads as the one thing to act on, not just another row.
        $cardclass = "al-card al-card--flag-$flag" . (!empty($c['is_next']) ? ' al-card--accent' : '');
        echo html_writer::start_div($cardclass, [
            'style' => 'display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:10px;'
        ]);

        echo html_writer::start_div('', ['style' => 'flex:1; min-width:220px;']);
        if (!empty($c['is_next'])) {
            echo html_writer::div('START HERE', 'al-badge al-badge--pill',
                ['style' => 'margin-bottom:6px; display:inline-block;']);
            echo html_writer::empty_tag('br');
        }
        // The competency name doubles as the way INTO the material: each
        // competency is its own Moodle course, and a student reading "do this
        // next" should not then have to go hunting for the course by hand.
        // Falls back to plain text when the course is unmapped or not open to
        // them, so the link never leads to an access error.
        $title = html_writer::tag('strong', s($c['competency_name']), ['style' => 'font-size:1.05em;']);
        $courselink = $courselinks[$c['competency_id']] ?? null;
        if ($courselink) {
            echo html_writer::link($courselink->url,
                $title . '<i class="fa fa-chevron-right" aria-hidden="true"></i>',
                [
                    'class' => 'al-course-link',
                    'aria-label' => 'Open the course: ' . $c['competency_name'],
                    'title' => 'Open ' . $courselink->fullname,
                ]);
        } else {
            echo $title;
        }
        // Always show what the percentage was computed from. A bare "50%"
        // hides whether that came from 1 item or 20, which is exactly the
        // difference between a meaningful result and noise.
        $basis = '';
        if ($c['cm'] !== null && isset($c['attempted'])) {
            $basis = ' <span style="opacity:0.75;">(from ' . (int)$c['attempted'] . ' answered '
                . ((int)$c['attempted'] === 1 ? 'item' : 'items') . ')</span>';
        }
        echo html_writer::tag('div',
            ($c['cm'] !== null
                ? html_writer::tag('span', $c['cm'], ['class' => 'tnum']) . '% mastery' . $basis
                : 'Not yet attempted'),
            ['style' => 'color:var(--al-ink-500); font-size:0.9em;']);
        echo local_acculearnfeedback_progress_bar($c['cm'] ?? null, $c['mastery']);
        // Per-quiz checklist. Mastery is now earned by passing every quiz in
        // the module, so the student needs to see WHICH ones are outstanding -
        // a single percentage cannot tell them what to actually go and do.
        if (!empty($c['quizzes'])) {
            echo html_writer::start_div('', ['style' => 'margin-top:10px; display:flex; flex-wrap:wrap; gap:6px;']);
            foreach ($c['quizzes'] as $q) {
                if ($q['passed']) {
                    $icon = '<i class="fa fa-circle-check" aria-hidden="true"></i> ';
                    $cls = 'al-badge al-badge--mastered';
                    $note = $q['grade'] !== null ? ' ' . $q['grade'] . '/' . $q['grademax'] : '';
                } else if ($q['attempted']) {
                    $icon = '<i class="fa fa-circle-xmark" aria-hidden="true"></i> ';
                    $cls = 'al-badge al-badge--needs';
                    $note = ' ' . $q['grade'] . '/' . $q['grademax']
                        . ' (need ' . $q['gradepass'] . ')';
                } else {
                    $icon = '<i class="fa fa-circle" aria-hidden="true"></i> ';
                    $cls = 'al-badge al-badge--neutral';
                    $note = ' not taken';
                }
                // The summative is visibly held until the formatives are done.
                if ($q['is_summative'] && !$q['passed'] && empty($c['formatives_passed'])) {
                    $icon = '<i class="fa fa-lock" aria-hidden="true"></i> ';
                    $cls = 'al-badge al-badge--neutral';
                    $note = ' locked until formatives passed';
                }
                // A formative held shut by its lesson says so, and names the
                // lesson. Without this it read as merely "not taken", which
                // sends the student to a quiz that will refuse them.
                $lessonlink = null;
                if (!empty($q['lesson_locked']) && !$q['passed']) {
                    $icon = '<i class="fa fa-lock" aria-hidden="true"></i> ';
                    $cls = 'al-badge al-badge--neutral';
                    $note = ' finish the lesson first';
                    if (!empty($q['lesson_cmid'])) {
                        $lessonlink = new moodle_url('/mod/scorm/view.php', ['id' => $q['lesson_cmid']]);
                    }
                }
                $badge = html_writer::tag('span', $icon . s($q['name']) . s($note), ['class' => $cls]);
                if ($lessonlink) {
                    $badge = html_writer::link($lessonlink, $badge, [
                        'class' => 'al-course-link',
                        'title' => 'Open the lesson: ' . $q['lesson_name'],
                        'aria-label' => s($q['name']) . ' is locked - open the lesson '
                            . $q['lesson_name'] . ' to unlock it',
                    ]);
                }
                echo $badge;
            }
            echo html_writer::end_div();
        }

        if (!empty($c['priority_reason'])) {
            echo html_writer::tag('div', s($c['priority_reason']),
                ['style' => 'color:var(--al-ink-500); font-size:0.85em; margin-top:6px;']);
        }
        echo html_writer::end_div();

        echo html_writer::start_div('', ['style' => 'display:flex; flex-wrap:wrap; align-items:center; gap:8px;']);
        if (!empty($c['blocked'])) {
            echo html_writer::tag('span',
                '<i class="fa fa-lock" aria-hidden="true"></i> Locked',
                ['class' => 'al-badge al-badge--neutral']);
        }
        echo html_writer::tag('span',
            html_writer::tag('span', '', ['class' => 'al-badge__dot', 'style' => "background:$dot;"]) . ' ' . s($c['mastery']),
            ['class' => "al-badge al-badge--$tone"]);
        echo local_acculearnfeedback_lp_badge($c['lp'] ?? null, $c['lp_label']);
        echo html_writer::end_div();

        echo html_writer::end_div();
    }
}

echo html_writer::end_div();

echo $OUTPUT->footer();
