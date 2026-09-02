<?php
/**
 * Database browser and light data-entry tool for the AccuLearn study data.
 *
 * Exists so a thesis adviser or panellist can inspect the real tables live -
 * refresh and the current contents are what they see - without putting a
 * general-purpose console like phpMyAdmin or Adminer on a public host.
 *
 * Deliberate limits, in order of how much they matter:
 *   - No DELETE. Nothing on this page removes a row, by design. Accounts are
 *     deactivated (Moodle's "suspended" flag) rather than deleted.
 *   - Rows can only be created in AccuLearn's own tables. Moodle core tables
 *     are read-only here: a raw INSERT into mdl_user or mdl_quiz_attempts
 *     skips the context, hashing and event work Moodle's APIs do, and would
 *     leave the site inconsistent rather than saving anyone time.
 *   - No free-text SQL box. Every query is one this file builds itself.
 *   - Only the 29 tables this study actually uses are listed, out of ~497.
 *
 * Two injection surfaces exist because table and column names cannot be bound
 * as parameters. Both are closed by whitelisting against what the database
 * actually reports (SHOW TABLES / SHOW COLUMNS), never by escaping. Values are
 * always bound. Every write checks sesskey.
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_login();

global $DB, $OUTPUT, $PAGE, $USER, $SITE;

$context = context_system::instance();
// Site administrators only. This shows unfiltered rows and can write, so it
// sits above every AccuLearn capability - teachers must never reach it.
require_capability('moodle/site:config', $context);

$table       = optional_param('table', '', PARAM_RAW);
$view        = optional_param('view', 'table', PARAM_ALPHA);
$pagenum     = optional_param('page', 0, PARAM_INT);
$sort        = optional_param('sort', '', PARAM_RAW);
$dir         = optional_param('dir', 'DESC', PARAM_ALPHA);
$search      = optional_param('search', '', PARAM_TEXT);
$autorefresh = optional_param('autorefresh', 0, PARAM_INT);
$action      = optional_param('action', '', PARAM_ALPHA);
$perpage     = 50;

$baseurl = new moodle_url('/local/acculearnfeedback/db_browser.php');
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_title('Database');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('report');

// ---------------------------------------------------------------------------
// What this page is allowed to show.
//
// AccuLearn's tables are split across two naming conventions - six were created
// without the mdl_ prefix and four with it - so everything here works from the
// raw names SHOW TABLES reports rather than assuming a prefix and using
// {braces}. $DB->insert_record() would resolve the wrong physical table for the
// unprefixed six, which is why inserts below go through $DB->execute().
// ---------------------------------------------------------------------------
$existing = [];
foreach ($DB->get_records_sql("SHOW TABLES") as $row) {
    $vals = (array)$row;
    $existing[] = array_shift($vals);
}
$existing = array_flip($existing);

$acculearn = [];
foreach (array_keys($existing) as $t) {
    if (stripos($t, 'acculearn') !== false || stripos($t, 'qbehaviour_confidence') !== false) {
        $acculearn[] = $t;
    }
}
sort($acculearn);

// The Moodle tables this study actually reads or writes. Everything else in the
// database is Moodle machinery the thesis never touches, and listing all ~497
// made the picker useless.
$moodleused = [];
foreach (['user', 'course', 'course_modules', 'quiz', 'quiz_attempts', 'question',
          'question_attempts', 'question_attempt_steps', 'question_attempt_step_data',
          'scorm', 'scorm_scoes', 'scorm_scoes_value', 'grade_grades', 'grade_items',
          'role', 'role_assignments', 'user_enrolments', 'enrol', 'user_lastaccess'] as $t) {
    if (isset($existing[$CFG->prefix . $t])) {
        $moodleused[] = $CFG->prefix . $t;
    }
}

$allowed = array_merge($acculearn, $moodleused);
$writable = array_flip($acculearn);   // Only AccuLearn tables accept new rows.

/** Columns of a physical table, keyed by name. Works for prefixed and not. */
function alb_columns($tablename) {
    global $DB;
    $cols = [];
    foreach ($DB->get_records_sql("SHOW COLUMNS FROM `$tablename`") as $c) {
        $a = (array)$c;
        $name = $a['field'] ?? $a['Field'];
        $cols[$name] = [
            'type'    => $a['type'] ?? $a['Type'],
            'null'    => ($a['null'] ?? $a['Null']) === 'YES',
            'extra'   => $a['extra'] ?? $a['Extra'] ?? '',
            'key'     => $a['key'] ?? $a['Key'] ?? '',
            'default' => $a['default'] ?? $a['Default'] ?? null,
        ];
    }
    return $cols;
}

/** The single-column primary key of a table, or null if there isn't one. */
function alb_pk(array $cols) {
    foreach ($cols as $name => $meta) {
        if ($meta['key'] === 'PRI') {
            return $name;
        }
    }
    return null;
}

/** True when the database assigns this column, so no form should offer it. */
function alb_is_auto($meta) {
    return strpos($meta['extra'], 'auto_increment') !== false;
}

/**
 * Check submitted values against the column definitions before going near the
 * database. Without this a mistyped number surfaces as a raw SQL exception,
 * which is no use to whoever is typing.
 *
 * @return array [values to write, per-column error messages]
 */
function alb_validate(array $cols, array $submitted, $skipcolumn = null) {
    $clean = [];
    $errors = [];
    foreach ($cols as $name => $meta) {
        if (alb_is_auto($meta) || $name === $skipcolumn) {
            continue;
        }
        $raw = $submitted[$name] ?? null;
        $given = ($raw !== null && $raw !== '');

        if (!$given) {
            // NOT NULL with no default and nothing supplied cannot be written.
            if (!$meta['null'] && $meta['default'] === null) {
                $errors[$name] = 'Required - this column cannot be empty.';
            }
            continue;
        }

        $type = strtolower($meta['type']);
        if (preg_match('/^(tiny|small|medium|big)?int/', $type)) {
            if (!preg_match('/^-?\\d+$/', trim($raw))) {
                $errors[$name] = 'Whole number expected, got "' . s($raw) . '".';
                continue;
            }
        } else if (preg_match('/^(decimal|numeric|float|double)/', $type)) {
            if (!is_numeric(trim($raw))) {
                $errors[$name] = 'Number expected, got "' . s($raw) . '".';
                continue;
            }
        } else if (preg_match('/^(var)?char\\((\\d+)\\)/', $type, $m)) {
            $max = (int)$m[2];
            if (core_text::strlen($raw) > $max) {
                $errors[$name] = 'Too long - ' . $max . ' characters maximum, got '
                    . core_text::strlen($raw) . '.';
                continue;
            }
        }
        $clean[$name] = $raw;
    }
    return [$clean, $errors];
}

/** Row count that never fatals the page if a table vanishes. */
function count_records_safe($db, $t) {
    try { return number_format($db->count_records_sql("SELECT COUNT(*) FROM `$t`")); }
    catch (Exception $e) { return '-'; }
}

// ---------------------------------------------------------------------------
// Writes. Both check sesskey; neither can remove anything.
// ---------------------------------------------------------------------------
// Populated when a submission fails validation, so the form can be redrawn
// with the user's own values and a message against each bad field rather than
// throwing away what they typed.
$formerrors = [];
$formvalues = [];
$editkey    = null;

if (($action === 'insert' || $action === 'update') && confirm_sesskey()) {
    $target = required_param('table', PARAM_RAW);
    if (!isset($writable[$target])) {
        throw new moodle_exception('nopermissions', 'error', $baseurl,
            ($action === 'insert' ? 'create' : 'edit') . ' rows in this table');
    }
    $cols = alb_columns($target);
    $pk = alb_pk($cols);

    $submitted = [];
    foreach ($cols as $name => $meta) {
        $submitted[$name] = optional_param('f_' . $name, null, PARAM_RAW);
    }

    // On edit the primary key identifies the row and is never rewritten.
    $skip = ($action === 'update') ? $pk : null;
    [$clean, $errors] = alb_validate($cols, $submitted, $skip);

    if ($action === 'update' && $pk === null) {
        throw new moodle_exception('invalidparameter', 'debug', $baseurl, null,
            'This table has no single-column primary key, so rows cannot be edited.');
    }

    if (!$errors && $action === 'insert' && !$clean) {
        $errors['__form'] = 'Nothing to save - every field was left blank.';
    }

    if ($errors) {
        // Fall through to the form instead of redirecting.
        $formerrors = $errors;
        $formvalues = $submitted;
        $table = $target;
        $view = ($action === 'insert') ? 'create' : 'edit';
        $editkey = ($action === 'update') ? required_param('key', PARAM_RAW) : null;
    } else if ($action === 'insert') {
        $collist = '`' . implode('`, `', array_keys($clean)) . '`';
        $placeholders = implode(', ', array_fill(0, count($clean), '?'));
        $DB->execute("INSERT INTO `$target` ($collist) VALUES ($placeholders)", array_values($clean));
        redirect(new moodle_url($baseurl, ['table' => $target]),
            'Row added to ' . $target . '.', null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        $key = required_param('key', PARAM_RAW);
        // Columns left blank on an edit are written back as NULL where the
        // schema allows it, so a value can actually be cleared.
        $sets = [];
        $params = [];
        foreach ($cols as $name => $meta) {
            if (alb_is_auto($meta) || $name === $pk) {
                continue;
            }
            $sets[] = "`$name` = ?";
            if (array_key_exists($name, $clean)) {
                $params[] = $clean[$name];
            } else {
                $params[] = $meta['null'] ? null : $meta['default'];
            }
        }
        $params[] = $key;
        $DB->execute("UPDATE `$target` SET " . implode(', ', $sets) . " WHERE `$pk` = ?", $params);
        redirect(new moodle_url($baseurl, ['table' => $target]),
            'Row updated in ' . $target . '.', null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

if (($action === 'deactivate' || $action === 'activate') && confirm_sesskey()) {
    $userid = required_param('userid', PARAM_INT);
    $target = $DB->get_record('user', ['id' => $userid, 'deleted' => 0], '*', MUST_EXIST);
    $accountsurl = new moodle_url($baseurl, ['view' => 'accounts']);

    // Guard rails: a site admin cannot be locked out from here, and neither can
    // the person clicking the button.
    if (is_siteadmin($target->id)) {
        redirect($accountsurl, 'Site administrators cannot be deactivated from this page.',
            null, \core\output\notification::NOTIFY_ERROR);
    }
    if ((int)$target->id === (int)$USER->id) {
        redirect($accountsurl, 'You cannot deactivate your own account.',
            null, \core\output\notification::NOTIFY_ERROR);
    }

    $target->suspended = ($action === 'deactivate') ? 1 : 0;
    if ($target->suspended) {
        // Suspending leaves live sessions running unless they are cleared.
        \core\session\manager::destroy_user_sessions($target->id);
    }
    user_update_user($target, false);
    redirect($accountsurl,
        fullname($target) . ($action === 'deactivate' ? ' deactivated.' : ' reactivated.'),
        null, \core\output\notification::NOTIFY_SUCCESS);
}

// Default landing table.
if ($table === '' && $view !== 'accounts' && $acculearn) {
    $table = $acculearn[0];
}
if ($table !== '' && !in_array($table, $allowed, true)) {
    throw new moodle_exception('invalidparameter', 'debug', $baseurl, null, 'Unknown or hidden table');
}

// Row counts drive the sidebar badges. 29 counts is cheap; 497 was not.
$counts = [];
foreach ($allowed as $t) {
    $counts[$t] = $DB->count_records_sql("SELECT COUNT(*) FROM `$t`");
}

echo $OUTPUT->header();
?>
<style>
.alb{--alb-accent:#2f6f3e;--alb-line:#e2e7e3;--alb-sunk:#f5f8f6;--alb-ink:#1a211c;--alb-muted:#6d776f;
  display:grid;grid-template-columns:250px minmax(0,1fr);gap:1.5rem;align-items:start;margin-top:.5rem}
@media (max-width:900px){.alb{grid-template-columns:1fr}}
.alb__side{position:sticky;top:1rem;border:1px solid var(--alb-line);border-radius:10px;background:#fff;overflow:hidden}
@media (max-width:900px){.alb__side{position:static}}
.alb__group{padding:.55rem .8rem .2rem;font-size:.68rem;font-weight:700;letter-spacing:.09em;
  text-transform:uppercase;color:var(--alb-muted);background:var(--alb-sunk);border-bottom:1px solid var(--alb-line)}
.alb__link{display:flex;justify-content:space-between;gap:.5rem;align-items:center;padding:.42rem .8rem;
  font-size:.83rem;color:var(--alb-ink);text-decoration:none;border-bottom:1px solid #f0f3f1;word-break:break-all}
.alb__link:hover{background:var(--alb-sunk);text-decoration:none;color:var(--alb-ink)}
.alb__link--on{background:var(--alb-accent);color:#fff;font-weight:600}
.alb__link--on:hover{background:var(--alb-accent);color:#fff}
.alb__n{font-variant-numeric:tabular-nums;font-size:.74rem;opacity:.72;white-space:nowrap}
.alb__main{min-width:0}
.alb__bar{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;justify-content:space-between;margin-bottom:.9rem}
.alb__title{margin:0;font-size:1.3rem;font-weight:600;word-break:break-all}
.alb__meta{color:var(--alb-muted);font-size:.85rem;margin:.15rem 0 0}
.alb__acts{display:flex;gap:.4rem;flex-wrap:wrap;align-items:center}
.alb__wrap{overflow-x:auto;border:1px solid var(--alb-line);border-radius:10px;background:#fff}
.alb__wrap table{margin:0;font-size:.83rem;width:100%;border-collapse:collapse}
.alb__wrap th,.alb__wrap td{padding:.48rem .7rem;border-bottom:1px solid var(--alb-line);
  text-align:left;vertical-align:top;white-space:nowrap}
.alb__wrap thead th{position:sticky;top:0;background:var(--alb-sunk);font-size:.7rem;text-transform:uppercase;
  letter-spacing:.06em;color:var(--alb-muted);font-weight:700;z-index:1}
.alb__wrap thead th a{color:var(--alb-muted)}
.alb__wrap tbody tr:hover{background:#fafcfb}
.alb__null{color:#aab3ad;font-style:italic}
.alb__pill{display:inline-block;padding:.1em .5em;border-radius:20px;font-size:.72rem;font-weight:600}
.alb__pill--on{background:#e7f0e9;color:#2f6f3e}
.alb__pill--off{background:#f6e5e3;color:#a03228}
.alb__form{border:1px solid var(--alb-line);border-radius:10px;background:#fff;padding:1.1rem 1.2rem;max-width:640px}
.alb__field{display:flex;flex-direction:column;gap:.25rem;margin-bottom:.85rem}
.alb__field label{font-weight:600;font-size:.85rem;color:var(--alb-ink)}
.alb__field .alb__hint{font-size:.75rem;color:var(--alb-muted)}
.alb__field input,.alb__field textarea{width:100%;padding:.42rem .6rem;border:1px solid #ccd4ce;border-radius:6px;font-size:.87rem}
.alb__field textarea{min-height:80px;font-family:ui-monospace,monospace}
.alb__note{background:var(--alb-sunk);border:1px solid var(--alb-line);border-radius:8px;
  padding:.7rem .9rem;font-size:.85rem;color:var(--alb-muted);margin-bottom:.9rem}
.alb__field--bad input,.alb__field--bad textarea{border-color:#c0392b;background:#fdf6f5}
.alb__err{color:#a03228;font-size:.78rem;font-weight:600}
.alb__req{color:#a03228}
.alb__edit{font-size:.78rem;font-weight:600;white-space:nowrap}
.alb__field input:disabled{background:var(--alb-sunk);color:var(--alb-muted)}
.alb__search{display:flex;gap:.4rem}
.alb__search input{padding:.35rem .6rem;border:1px solid #ccd4ce;border-radius:6px;font-size:.83rem;min-width:190px}
</style>
<?php

echo html_writer::start_div('alb');

// ---------------------------------------------------------------------------
// Sidebar
// ---------------------------------------------------------------------------
echo html_writer::start_div('alb__side');
$accounton = ($view === 'accounts') ? ' alb__link--on' : '';
echo html_writer::div('Manage', 'alb__group');
echo html_writer::link(new moodle_url($baseurl, ['view' => 'accounts']),
    html_writer::span('Accounts') . html_writer::span(count_records_safe($DB, $CFG->prefix . 'user'), 'alb__n'),
    ['class' => 'alb__link' . $accounton]);

echo html_writer::div('AccuLearn tables', 'alb__group');
foreach ($acculearn as $t) {
    $on = ($t === $table && $view !== 'accounts') ? ' alb__link--on' : '';
    echo html_writer::link(new moodle_url($baseurl, ['table' => $t]),
        html_writer::span($t) . html_writer::span(number_format($counts[$t]), 'alb__n'),
        ['class' => 'alb__link' . $on]);
}
echo html_writer::div('Moodle tables in use', 'alb__group');
foreach ($moodleused as $t) {
    $on = ($t === $table && $view !== 'accounts') ? ' alb__link--on' : '';
    echo html_writer::link(new moodle_url($baseurl, ['table' => $t]),
        html_writer::span($t) . html_writer::span(number_format($counts[$t]), 'alb__n'),
        ['class' => 'alb__link' . $on]);
}
echo html_writer::end_div();

echo html_writer::start_div('alb__main');

// ---------------------------------------------------------------------------
// Accounts view: activate / deactivate. No deletion.
// ---------------------------------------------------------------------------
if ($view === 'accounts') {
    echo html_writer::start_div('alb__bar');
    echo html_writer::div(
        html_writer::tag('h2', 'Accounts', ['class' => 'alb__title']) .
        html_writer::tag('p', 'Deactivating blocks sign-in and ends any live session. It keeps every record the '
            . 'account owns, so study data is never lost. Nothing here deletes an account.', ['class' => 'alb__meta']));
    echo html_writer::end_div();

    $users = $DB->get_records_select('user', 'deleted = 0 AND id > 1', null,
        'suspended ASC, username ASC', 'id, username, firstname, lastname, suspended, lastaccess');

    $t = new html_table();
    $t->head = ['Name', 'Username', 'Status', 'Last access', 'Action'];
    $t->attributes['class'] = '';
    foreach ($users as $u) {
        $isadmin = is_siteadmin($u->id);
        $isself  = ((int)$u->id === (int)$USER->id);
        $status = $u->suspended
            ? html_writer::span('Inactive', 'alb__pill alb__pill--off')
            : html_writer::span('Active', 'alb__pill alb__pill--on');

        if ($isadmin || $isself) {
            $btn = html_writer::span($isself ? 'This is you' : 'Site administrator',
                '', ['style' => 'color:#6d776f;font-size:.8rem']);
        } else {
            $act = $u->suspended ? 'activate' : 'deactivate';
            $button = new \core\output\single_button(
                new moodle_url($baseurl, ['action' => $act, 'userid' => $u->id,
                    'view' => 'accounts', 'sesskey' => sesskey()]),
                $u->suspended ? 'Activate' : 'Deactivate',
                'post',
                $u->suspended
                    ? \core\output\single_button::BUTTON_PRIMARY
                    : \core\output\single_button::BUTTON_SECONDARY);
            $button->add_confirm_action($u->suspended
                ? 'Let ' . fullname($u) . ' sign in again?'
                : 'Deactivate ' . fullname($u) . '? They will be signed out and blocked from '
                    . 'signing in. Their data is kept.');
            $btn = $OUTPUT->render($button);
        }
        $t->data[] = [
            fullname($u),
            html_writer::tag('code', s($u->username)),
            $status,
            $u->lastaccess ? userdate($u->lastaccess, '%d %b %Y, %H:%M') : html_writer::span('Never', 'alb__null'),
            $btn,
        ];
    }
    echo html_writer::div(html_writer::table($t), 'alb__wrap');
    echo html_writer::end_div();
    echo html_writer::end_div();
    echo $OUTPUT->footer();
    exit;
}

// ---------------------------------------------------------------------------
// Create form
// ---------------------------------------------------------------------------
$cols = alb_columns($table);
$colnames = array_keys($cols);

if ($view === 'create' || $view === 'edit') {
    if (!isset($writable[$table])) {
        throw new moodle_exception('nopermissions', 'error', $baseurl, 'change rows in this table');
    }
    $isedit = ($view === 'edit');
    $pk = alb_pk($cols);
    if ($isedit && $pk === null) {
        throw new moodle_exception('invalidparameter', 'debug', $baseurl, null,
            'This table has no single-column primary key, so rows cannot be edited.');
    }

    // Values shown in the fields: what was just rejected, else the stored row,
    // else sensible defaults for a new row.
    $current = [];
    if ($isedit) {
        $editkey = ($editkey !== null) ? $editkey : required_param('key', PARAM_RAW);
        $row = $DB->get_record_sql("SELECT * FROM `$table` WHERE `$pk` = ?", [$editkey]);
        if (!$row) {
            throw new moodle_exception('invalidparameter', 'debug', $baseurl, null, 'No such row.');
        }
        $current = (array)$row;
    }

    echo html_writer::start_div('alb__bar');
    echo html_writer::div(
        html_writer::tag('h2', ($isedit ? 'Edit row in ' : 'New row in ') . s($table), ['class' => 'alb__title']) .
        html_writer::tag('p', $isedit
            ? 'Clearing a field stores NULL where the column allows it.'
            : 'Leave a field blank to store NULL or the column default.', ['class' => 'alb__meta']));
    echo html_writer::link(new moodle_url($baseurl, ['table' => $table]), 'Cancel', ['class' => 'btn btn-secondary']);
    echo html_writer::end_div();

    if (!empty($formerrors)) {
        $count = count(array_diff_key($formerrors, ['__form' => 1]));
        echo $OUTPUT->notification(isset($formerrors['__form'])
            ? $formerrors['__form']
            : ($count === 1 ? 'One field needs fixing.' : $count . ' fields need fixing.'), 'error');
    }

    echo html_writer::start_tag('form', ['method' => 'post', 'action' => $baseurl->out(false), 'class' => 'alb__form']);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => $isedit ? 'update' : 'insert']);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'table', 'value' => $table]);
    if ($isedit) {
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'key', 'value' => $editkey]);
    }

    foreach ($cols as $name => $meta) {
        if (alb_is_auto($meta)) {
            continue;
        }
        $ispk = ($name === $pk);
        if ($isedit && $ispk) {
            // Shown for orientation, never rewritten.
            echo html_writer::start_div('alb__field');
            echo html_writer::tag('label', s($name));
            echo html_writer::div(s($meta['type']) . ' &middot; primary key, not editable', 'alb__hint');
            echo html_writer::empty_tag('input', ['type' => 'text', 'value' => $current[$name] ?? '', 'disabled' => 'disabled']);
            echo html_writer::end_div();
            continue;
        }

        $istext = preg_match('/text|blob/i', $meta['type']);
        $isnum  = preg_match('/int|decimal|float|double/i', $meta['type']);
        $istime = preg_match('/^(timecreated|entered_at|generated_at|reviewed_at|window_start|last_updated)$/', $name);
        $required = (!$meta['null'] && $meta['default'] === null);

        if (array_key_exists($name, $formvalues) && $formvalues[$name] !== null) {
            $value = $formvalues[$name];
        } else if ($isedit) {
            $value = $current[$name] ?? '';
        } else {
            $value = $istime ? time() : '';
        }

        $err = $formerrors[$name] ?? null;
        echo html_writer::start_div('alb__field' . ($err ? ' alb__field--bad' : ''));
        echo html_writer::tag('label', s($name) . ($required ? html_writer::span(' *', 'alb__req') : ''),
            ['for' => 'f_' . $name]);
        echo html_writer::div(s($meta['type']) . ($required ? ' &middot; required' : ' &middot; optional')
            . ($istime && !$isedit ? ' &middot; epoch seconds, prefilled with now' : ''), 'alb__hint');
        if ($istext) {
            echo html_writer::tag('textarea', s($value), ['name' => 'f_' . $name, 'id' => 'f_' . $name]);
        } else {
            echo html_writer::empty_tag('input', [
                'type' => $isnum ? 'number' : 'text',
                'step' => $isnum ? 'any' : null,
                'name' => 'f_' . $name, 'id' => 'f_' . $name, 'value' => $value,
            ]);
        }
        if ($err) {
            echo html_writer::div($err, 'alb__err');
        }
        echo html_writer::end_div();
    }
    echo html_writer::empty_tag('input', ['type' => 'submit',
        'value' => $isedit ? 'Save changes' : 'Add row', 'class' => 'btn btn-primary']);
    echo html_writer::end_tag('form');
    echo html_writer::end_div();
    echo html_writer::end_div();
    echo $OUTPUT->footer();
    exit;
}

// ---------------------------------------------------------------------------
// Table view
// ---------------------------------------------------------------------------
if ($sort === '' || !in_array($sort, $colnames, true)) {
    $sort = in_array('id', $colnames, true) ? 'id' : $colnames[0];
}
$dir = (strtoupper($dir) === 'ASC') ? 'ASC' : 'DESC';

// Search spans the text-ish columns only; numbers are matched as strings too,
// which is what you want when hunting for a userid.
$where = '';
$params = [];
if ($search !== '') {
    $ors = [];
    foreach ($cols as $name => $meta) {
        $ors[] = $DB->sql_like("`$name`", '?', false, false);
        $params[] = '%' . $search . '%';
    }
    $where = 'WHERE ' . implode(' OR ', $ors);
}

$total = $DB->count_records_sql("SELECT COUNT(*) FROM `$table` $where", $params);

echo html_writer::start_div('alb__bar');
echo html_writer::div(
    html_writer::tag('h2', s($table), ['class' => 'alb__title']) .
    html_writer::tag('p', number_format($total) . ($search !== '' ? ' matching' : '') . ' rows &middot; '
        . count($colnames) . ' columns &middot; sorted by ' . s($sort) . ' ' . $dir, ['class' => 'alb__meta']));

echo html_writer::start_div('alb__acts');
echo html_writer::start_tag('form', ['method' => 'get', 'class' => 'alb__search']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'table', 'value' => $table]);
echo html_writer::empty_tag('input', ['type' => 'search', 'name' => 'search', 'value' => $search,
    'placeholder' => 'Search this table']);
echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => 'Search', 'class' => 'btn btn-secondary']);
echo html_writer::end_tag('form');
if (isset($writable[$table])) {
    echo html_writer::link(new moodle_url($baseurl, ['table' => $table, 'view' => 'create']),
        'Create row', ['class' => 'btn btn-primary']);
}
$toggle = new moodle_url($baseurl, ['table' => $table, 'sort' => $sort, 'dir' => $dir,
    'search' => $search, 'autorefresh' => $autorefresh ? 0 : 10]);
echo html_writer::link($toggle, $autorefresh ? 'Stop auto-refresh' : 'Auto-refresh',
    ['class' => 'btn btn-secondary']);
echo html_writer::end_div();
echo html_writer::end_div();

if ($autorefresh) {
    $PAGE->requires->js_init_code('setTimeout(function(){ location.reload(); }, ' . ($autorefresh * 1000) . ');');
}

if (!isset($writable[$table])) {
    echo html_writer::div('This is a Moodle core table, shown read-only. Rows here are created by Moodle itself - '
        . 'adding them directly would skip the context and event handling Moodle relies on.', 'alb__note');
}

$rs = $DB->get_recordset_sql("SELECT * FROM `$table` $where ORDER BY `$sort` $dir",
    $params, $pagenum * $perpage, $perpage);

$rowpk = alb_pk($cols);
$caneditrows = isset($writable[$table]) && $rowpk !== null;

$out = new html_table();
$out->head = [];
if ($caneditrows) {
    $out->head[] = '';
}
foreach ($colnames as $c) {
    $newdir = ($c === $sort && $dir === 'ASC') ? 'DESC' : 'ASC';
    $arrow = ($c === $sort) ? ($dir === 'ASC' ? ' ↑' : ' ↓') : '';
    $out->head[] = html_writer::link(
        new moodle_url($baseurl, ['table' => $table, 'sort' => $c, 'dir' => $newdir,
            'search' => $search, 'autorefresh' => $autorefresh]),
        s($c) . $arrow);
}

// Credentials live in some Moodle tables. This page is for showing study data,
// so those are masked rather than printed to whoever is looking at the screen.
$sensitive = ['password', 'secret', 'token', 'sesskey', 'privatekey', 'salt', 'hash'];

$n = 0;
foreach ($rs as $record) {
    $cells = [];
    if ($caneditrows) {
        $cells[] = html_writer::link(
            new moodle_url($baseurl, ['table' => $table, 'view' => 'edit', 'key' => $record->{$rowpk}]),
            'Edit', ['class' => 'alb__edit']);
    }
    foreach ($colnames as $c) {
        $value = $record->{$c} ?? null;
        $masked = false;
        foreach ($sensitive as $needle) {
            if (stripos($c, $needle) !== false) { $masked = true; break; }
        }
        if ($masked && $value !== null && $value !== '') {
            $cells[] = html_writer::span('••••••••', 'alb__null');
        } else if ($value === null) {
            $cells[] = html_writer::span('NULL', 'alb__null');
        } else {
            $str = (string)$value;
            if (core_text::strlen($str) > 120) {
                $cells[] = html_writer::span(s(core_text::substr($str, 0, 120)) . '…', '', ['title' => s($str)]);
            } else {
                $cells[] = s($str);
            }
        }
    }
    $out->data[] = $cells;
    $n++;
}
$rs->close();

if ($n === 0) {
    echo $OUTPUT->notification($search !== ''
        ? 'Nothing in this table matches "' . s($search) . '".'
        : 'This table is empty.', 'info');
} else {
    echo html_writer::div(html_writer::table($out), 'alb__wrap');
}

echo $OUTPUT->paging_bar($total, $pagenum, $perpage,
    new moodle_url($baseurl, ['table' => $table, 'sort' => $sort, 'dir' => $dir,
        'search' => $search, 'autorefresh' => $autorefresh]));

echo html_writer::end_div();
echo html_writer::end_div();
echo $OUTPUT->footer();
