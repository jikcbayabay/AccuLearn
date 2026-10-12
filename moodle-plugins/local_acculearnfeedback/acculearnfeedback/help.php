<?php
/**
 * System instructions for every kind of user.
 *
 * One page rather than four, because the audiences overlap in practice - a
 * panellist or an adviser wants to read all of it, and an administrator
 * answering a student's question wants the student's version in front of them.
 * The reader's own section is expanded on arrival and the rest are collapsed,
 * so nobody has to scroll past three other roles to find theirs.
 *
 * Sections are chosen by capability, never by role name: a site with a renamed
 * or additional role still resolves correctly, and it matches how every other
 * page in this plugin decides what to show.
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_login();

global $OUTPUT, $PAGE, $USER, $SITE, $CFG;

$context = context_system::instance();
$PAGE->set_url('/local/acculearnfeedback/help.php');
$PAGE->set_context($context);
$PAGE->set_title('How to use AccuLearn');
$PAGE->set_heading($SITE->fullname);

$canreview   = has_capability('local/acculearnfeedback:reviewaioutput', $context);
$canmanage   = has_capability('local/acculearnfeedback:managesequence', $context);
$canviewlogs = has_capability('local/acculearnfeedback:viewusagelogs', $context);
$isadmin     = has_capability('moodle/site:config', $context);
// An IT Admin can read the logs and manage accounts but is deliberately shut
// out of AI review and sequence management - that combination is what
// identifies them, and it is exactly what db/access.php grants the role.
$isitadmin   = $canviewlogs && !$canreview && !$canmanage;
$isstudent   = !$canreview && !$canmanage && !$canviewlogs && !$isadmin;

echo $OUTPUT->header();
echo $OUTPUT->heading('How to use AccuLearn');

local_acculearnfeedback_log_usage($USER->id, 'view_help', 'help', null,
    'Opened the system instructions');

/**
 * Renders one audience's instructions.
 *
 * @param string $title Section heading.
 * @param string $who One-line statement of who the section is for.
 * @param string $body Pre-built HTML.
 * @param bool $open Whether to render it expanded.
 * @return string
 */
function local_acculearnfeedback_help_section($title, $who, $body, $open) {
    $attr = $open ? ' open' : '';
    return '
    <details class="al-card al-help-section" style="margin-bottom:1rem; padding:1.25rem clamp(1rem, 4vw, 1.5rem);"' . $attr . '>
        <summary style="cursor:pointer; font-weight:600; font-size:1.05rem; list-style:none;">
            ' . s($title) . '
            <span style="display:block; font-weight:400; font-size:.85rem; opacity:.7; margin-top:.15rem;">'
                . s($who) . '</span>
        </summary>
        <div style="margin-top:1rem;">' . $body . '</div>
    </details>';
}

$student = <<<HTML
<h4>Getting in</h4>
<p>Your account is created for you &mdash; there is no sign-up form. If you cannot log in,
ask your teacher rather than trying repeatedly: after eight failed attempts the account
locks itself for thirty minutes.</p>

<h4>Finding your lessons</h4>
<p>Your courses are listed on your Dashboard, one per competency, in the order you are meant
to take them. Inside each course an <strong>Overview</strong> comes first, then the
<strong>Lesson Content</strong> packages. Open them in order &mdash; each one builds on the
last. Click through every page of a lesson; closing it early means the material after that
point was never shown to you.</p>

<h4>Taking a quiz</h4>
<p>Every question asks two things: your answer, and <strong>how sure you are</strong>.</p>
<ul>
    <li><strong>Not sure</strong> &mdash; you are guessing or close to it.</li>
    <li><strong>Fairly sure</strong> &mdash; you think you know it but could be wrong.</li>
    <li><strong>Quite sure</strong> &mdash; you are confident this is right.</li>
</ul>
<p>Answer the confidence question <em>honestly</em>. It is not part of your score in the way
you might expect, and inflating it does not help you. The system uses it to tell the
difference between a lucky guess and real understanding, and between a small slip and a
genuine misconception. If you mark a wrong answer &ldquo;Quite sure&rdquo;, the system
learns that this is a topic you believe you understand but do not &mdash; which is the most
useful thing it can know about you. If you mark a right answer &ldquo;Not sure&rdquo;, it
knows not to treat that as mastery yet.</p>
<p>You get <strong>three attempts</strong> at each quiz. Your highest attempt is the one that
counts. If you use all three without reaching mastery, the system will ask you to go back and
restudy before moving on.</p>

<h4>Why a quiz might be locked</h4>
<p>If a quiz will not open, it is almost always because the competency before it is not
mastered yet. The message names the topic you need to work on first. Go back to that lesson,
raise your mastery there, and the next one unlocks.</p>

<h4>Your feedback</h4>
<p>After you submit, feedback is written for you automatically. It usually appears within a
minute &mdash; not instantly &mdash; so if it is not there yet, wait and refresh. It tells you
what you got wrong, why it was probably wrong, and what to do about it.</p>
<p>The feedback is written by an AI, not by your teacher. It is usually good, but it can be
wrong. Your teacher reviews it and can correct or withhold any of it. <strong>If it does not
make sense to you, ask your teacher</strong> &mdash; do not assume the machine is right.</p>

<h4>My Learning Track</h4>
<p>Open <strong>My Learning Track</strong> from the bar at the bottom of any page. It shows
every competency, your mastery percentage, and what the system thinks you should do next:</p>
<ul>
    <li><strong>Prerequisite Review</strong> &mdash; go back; the topic before this one is not solid.</li>
    <li><strong>Guided Remediation</strong> &mdash; work through this topic again with support.</li>
    <li><strong>Reinforcement Practice</strong> &mdash; you are close; practise to make it stick.</li>
    <li><strong>Advancement</strong> &mdash; mastered; move on.</li>
</ul>
<p>You can be placed in Reinforcement Practice even with a high score, if your confidence
pattern suggests the score came partly from guessing.</p>
HTML;

$teacher = <<<HTML
<h4>Reviewing AI output</h4>
<p><strong>AI Output Review</strong> in the bottom toolbar lists every piece of generated
feedback and every learning track, unreviewed first. Each row shows the computed Competency
Mastery, Confidence Misconception Index and Guessing Index behind it, so you can judge whether
the AI's reading of the attempt is fair.</p>
<ul>
    <li><strong>Approve</strong> confirms the output is sound. It stays visible to the student.</li>
    <li><strong>Flag</strong> marks it as unsound. Flagged feedback is <em>withheld from the
    student</em> &mdash; this is not a silent bookmark, it changes what they see.</li>
    <li><strong>Teacher note</strong> records why, for your own record and for the study.</li>
</ul>
<p>Every approve and flag is written to a log that cannot be edited afterwards.</p>

<h4>Student names</h4>
<p>Students appear under a stable pseudonym such as &ldquo;Student-4C2A&rdquo;. The same
student always gets the same pseudonym, so you can follow one learner across records without
seeing a name. Revealing real names needs a separate permission that the Teacher role does not
carry &mdash; ask an administrator if you need it for a specific case.</p>

<h4>Overriding a grade</h4>
<p>The Feedback tab lets you set a final grade directly. This writes to the Moodle gradebook
as an <em>overridden</em> value, which stops the quiz module recalculating over the top of it
later. Use it when the quiz score does not reflect what the student actually demonstrated.</p>

<h4>Quiz questions</h4>
<p><strong>Quiz Questions</strong> in the bottom toolbar shows, for each quiz, its three
question pools: Easy, Medium and Hard. Every attempt picks at random from them. A formative
quiz takes 2 Easy, 2 Medium and 1 Hard question; a summative quiz takes 10 of each and has a
50-minute time limit.</p>
<ul>
    <li><strong>Add question</strong> adds a new multiple-choice question to that pool. It can
    appear from the student's next attempt.</li>
    <li><strong>View &amp; edit</strong> opens the pool to edit, preview or delete its questions,
    or to move one to another difficulty (tick it, then <em>Move to</em>).</li>
    <li>To check how many questions a quiz has, look under the quiz's name (&ldquo;25 questions in
    the bank&rdquo;) and at the <em>Questions in pool</em> column. The total for the whole
    competency is shown under its heading.</li>
    <li>A pool marked <strong>Needs more</strong> is smaller than it should be. Until you add
    questions, the quiz takes the missing ones from the neighbouring pool.</li>
</ul>
<p>Editing a question never changes answers students have already given. New and edited
questions count towards each student's mastery automatically.</p>

<h4>Activity completion</h4>
<p><strong>Activity Completion</strong> in the bottom toolbar shows, per student, which lessons and
quizzes are done (&#10003;) and which are not (&#10007;). A lesson counts once the student finishes it;
a quiz counts once the student submits an attempt. Pick a course for the activity-by-activity view.
Students appear under their pseudonyms here too.</p>

<h4>Confidence report</h4>
<p>The confidence report exports the raw answer-and-confidence data per quiz as CSV, for when
you want to look at the pattern yourself rather than through the computed indices.</p>

<h4>What to tell students</h4>
<p>The single most important thing is that <strong>confidence ratings must be honest</strong>.
Students who inflate them get worse feedback, not better marks. It is worth saying this out
loud before the first quiz &mdash; the mechanism is not obvious.</p>
HTML;

$admin = <<<HTML
<h4>Users and roles</h4>
<p>Four roles exist. <strong>Student</strong> sees only their own work.
<strong>Teacher</strong> reviews AI output and grades, seeing students pseudonymously.
<strong>IT Admin</strong> runs the platform but is walled off from academic data.
<strong>Super Admin</strong> can do everything.</p>
<p>Create accounts under Site administration &rarr; Users. <strong>Never delete a user
account</strong> &mdash; suspend it instead, from the same screen. Deleting destroys the
enrolments and identity behind that student's study records; suspending only blocks login and
leaves every attempt, feedback record and track intact.</p>

<h4>Sequence Settings</h4>
<p>Controls the competency chain: the order competencies run in, which competency is the
prerequisite for which, and whether gating is enforced for each one. Changes take effect
immediately &mdash; the mastery engine and the quiz access rule read these values live, so
switching gating off for a competency unblocks its quizzes on the next page load.</p>

<h4>Pre/Post-Test Entry</h4>
<p>The pre-test and post-test are administered on paper. This page is where those responses
are typed in, per student and per question, with the confidence rating the student wrote down.
Entering a student's pre-test is what generates their first learning track, before they have
attempted anything in the system.</p>

<h4>Tag Questions</h4>
<p>Maps questions to competencies. Every question must be tagged or its responses cannot count
towards any mastery figure. All 720 curriculum questions are already tagged; this page is for
anything added later.</p>

<h4>Data Retention</h4>
<p>Reports which study records are past the 180-day retention window. Nothing is ever purged
automatically &mdash; deleting a category takes an explicit, per-category confirmation from a
human, every time.</p>

<h4>Database</h4>
<p>A read-mostly browser over the 29 tables this study uses, so the real data can be inspected
live without putting a general-purpose SQL console on a public host. It cannot delete rows and
cannot write to Moodle's own tables. Site administrators only.</p>

<h4>Appearance</h4>
<p>Logo, theme colours and site name are under Site administration &rarr; Appearance. The
brand colour and the custom styling are set through the Boost theme's own settings, which live
in the database rather than in a file &mdash; they will not survive a fresh install without
being reapplied.</p>
HTML;

$itadmin = <<<HTML
<h4>What this role can do</h4>
<p>IT Admin covers keeping the platform running: creating and suspending user accounts,
enrolling students, assigning the Student role, reading system logs and the security and
performance reports, putting the site into maintenance mode, and taking course backups.</p>

<h4>What this role deliberately cannot do</h4>
<p>These are not oversights. The role is explicitly forbidden from editing grades, viewing all
grades, reading the confidence report, reviewing AI output, revealing student names, changing
the competency sequence, editing course content, backing up user data, logging in as another
user, and deleting user accounts. Technical administration does not require access to a
student's academic record, and separating the two is what makes the boundary demonstrable.</p>
<p>Site configuration is also excluded &mdash; that belongs to Super Admin. If you need it,
the change should go through someone holding that role.</p>

<h4>Accounts</h4>
<p>Create and suspend accounts under Site administration &rarr; Users. Suspension is the only
way to deactivate someone; deletion is blocked for this role on purpose, because study records
must be preserved.</p>

<h4>Keeping it running</h4>
<p>Scheduled and background jobs run from cron once a minute. AI feedback is generated by a
background task, so <strong>if cron stops, feedback silently stops being generated</strong>
while the site otherwise looks healthy. If feedback is not appearing, check cron first.</p>
<p>A pending upgrade also suspends cron. If you see that message, something has changed on
disk &mdash; a plugin folder added or a version bumped &mdash; and the upgrade needs to be
completed before background work resumes.</p>

<h4>Logs</h4>
<p><strong>Usage Logs</strong> in the toolbar shows AccuLearn's own activity log: who opened
which lesson, quiz, track or feedback record, and when. Moodle's standard log report under
Site administration &rarr; Reports covers everything else, including failed logins.</p>
HTML;

echo html_writer::start_div('al-page');

// list-style:none on the summary hides the disclosure triangle in Chrome and
// Firefox; Safari, desktop and iOS both, needs ::-webkit-details-marker as
// well or the native triangle stays and sits oddly beside a two-line summary.
echo '<style>.al-help-section > summary::-webkit-details-marker { display: none; }</style>';

echo '<p class="al-page__subtitle" style="margin-bottom:1.25rem;">'
   . 'Your own section is open below. The others are collapsed &mdash; tap any heading to read it.</p>';

echo local_acculearnfeedback_help_section(
    'For students', 'Taking lessons and quizzes, and reading your feedback', $student, $isstudent);
echo local_acculearnfeedback_help_section(
    'For teachers', 'Reviewing AI output, grades and student progress', $teacher, $canreview);
echo local_acculearnfeedback_help_section(
    'For administrators', 'Users, roles, competency sequence and study data', $admin, $canmanage || $isadmin);
echo local_acculearnfeedback_help_section(
    'For IT administrators', 'Accounts, logs, maintenance and background jobs', $itadmin, $isitadmin);

echo html_writer::end_div();
echo $OUTPUT->footer();
