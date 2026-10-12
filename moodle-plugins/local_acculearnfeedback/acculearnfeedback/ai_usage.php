<?php
/**
 * AI Usage - what AccuLearn has spent on OpenAI, read from OpenAI.
 *
 * The headline figure is OpenAI's own num_model_requests, not a tally this
 * plugin keeps: a locally-incremented counter would drift the moment a call
 * failed, was retried, or was made by anything other than AccuLearn. Both
 * numbers are shown side by side precisely so a gap between them is visible.
 *
 * The window is capped at 30 days and cannot be widened. OpenAIUsage::MAX_DAYS
 * clamps every request, so neither the selector below nor a hand-edited address
 * can ask for more.
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_login();

global $DB, $OUTPUT, $PAGE, $USER;

$context = context_system::instance();
require_capability('local/acculearnfeedback:viewusagelogs', $context);

$PAGE->set_url('/local/acculearnfeedback/ai_usage.php');
$PAGE->set_context($context);
$PAGE->set_title('AI Usage');
$PAGE->set_heading($SITE->fullname);

require_once('/var/www/html/acculearn-backend/openai_usage.php');

// Clamped twice on purpose: once against the offered list, once by the class.
$days = optional_param('days', OpenAIUsage::MAX_DAYS, PARAM_INT);
if (!in_array($days, OpenAIUsage::ALLOWED_RANGES, true)) {
    $days = OpenAIUsage::MAX_DAYS;
}
$days = OpenAIUsage::windowDays($days);

$usage = (new OpenAIUsage())->summary($days);
$since = time() - ($days * 86400);

// AccuLearn's own record over the same window, for comparison.
$localfeedback = (int) $DB->get_field_sql(
    "SELECT COUNT(*) FROM acculearn_feedback WHERE timecreated >= ?", [$since]);
$localtracks = (int) $DB->count_records_select('acculearn_usage_log',
    "action IN ('track_generated', 'track_refreshed') AND timecreated >= ?", [$since]);

local_acculearnfeedback_log_usage($USER->id, 'view_ai_usage', 'report', null,
    "Viewed the AI usage report ($days-day window)");

echo $OUTPUT->header();
echo $OUTPUT->heading('AI Usage');

echo html_writer::start_div('al-page');

// ---------------------------------------------------------------- range
echo html_writer::start_tag('form', ['method' => 'get', 'action' => $PAGE->url,
    'style' => 'margin:0 0 18px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;']);
echo html_writer::tag('span', 'Window', ['style' => 'font-size:0.88em; color:var(--al-ink-500);']);
foreach (OpenAIUsage::ALLOWED_RANGES as $r) {
    $cls = 'al-btn' . ($r === $days ? ' al-btn-success' : '');
    echo html_writer::tag('button', "Last $r days",
        ['type' => 'submit', 'name' => 'days', 'value' => $r, 'class' => $cls]);
}
echo html_writer::tag('span', 'OpenAI keeps this data for a limited period; '
    . OpenAIUsage::MAX_DAYS . ' days is the longest window this report will request.',
    ['style' => 'font-size:0.84em; color:var(--al-ink-500);']);
echo html_writer::end_tag('form');

// ---------------------------------------------------------------- state
if (!$usage['configured'] || $usage['error']) {

    $reason = $usage['error'] === 'forbidden'
        ? 'The key in OPENAI_ADMIN_KEY was rejected (HTTP 403). OpenAI\'s usage endpoints '
          . 'accept only an organization admin key - a project key (sk-proj-...) always fails here.'
        : ($usage['configured']
            ? 'OpenAI returned an error: ' . s($usage['error'])
            : 'No organization admin key is configured, so these figures cannot be read from OpenAI.');

    echo html_writer::div(
        html_writer::tag('div', 'Live OpenAI figures unavailable',
            ['style' => 'font-weight:600; margin-bottom:6px;'])
        . html_writer::tag('p', $reason, ['style' => 'margin:0 0 10px;'])
        . html_writer::tag('p', 'To enable them: at platform.openai.com go to Settings, then '
            . 'Organization, then Admin keys, create a key, and add it to '
            . html_writer::tag('code', '/var/www/html/acculearn-backend/.env')
            . ' as ' . html_writer::tag('code', 'OPENAI_ADMIN_KEY=sk-admin-...') . '. '
            . 'Only an organization owner can create one. The key is read only for this '
            . 'report and is never sent anywhere else.',
            ['style' => 'margin:0;']),
        'al-card', ['style' => 'padding:16px 20px; border-left:4px solid var(--al-warn, #d9a441);']);

    echo html_writer::tag('p', 'AccuLearn\'s own record of the requests it made is shown below '
        . 'and does not depend on OpenAI.', ['style' => 'margin:18px 0 10px; color:var(--al-ink-700);']);
}

// ---------------------------------------------------------------- figures
$tiles = [];
if ($usage['configured'] && !$usage['error']) {
    $tiles[] = ['AI requests', number_format($usage['requests']), 'reported by OpenAI'];
    $tiles[] = ['Input tokens', number_format($usage['input_tokens']), 'sent to the model'];
    $tiles[] = ['Output tokens', number_format($usage['output_tokens']), 'generated'];
    if ($usage['cost_usd'] !== null) {
        $tiles[] = ['Cost', '$' . number_format($usage['cost_usd'], 2), 'billed by OpenAI'];
    }
}
$tiles[] = ['Feedback generated', number_format($localfeedback), 'AccuLearn\'s own record'];
$tiles[] = ['Tracks generated', number_format($localtracks), 'AccuLearn\'s own record'];

echo html_writer::start_div('', ['style' => 'display:grid; gap:12px; '
    . 'grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); margin-bottom:22px;']);
foreach ($tiles as [$label, $value, $note]) {
    echo html_writer::div(
        html_writer::tag('div', $label, ['style' => 'font-size:0.78em; letter-spacing:.06em; '
            . 'text-transform:uppercase; color:var(--al-ink-500);'])
        . html_writer::tag('div', $value, ['style' => 'font-size:1.9em; font-weight:600; '
            . 'line-height:1.1; margin:6px 0 4px;', 'class' => 'tnum'])
        . html_writer::tag('div', $note, ['style' => 'font-size:0.8em; color:var(--al-ink-500);']),
        'al-card', ['style' => 'padding:14px 16px;']);
}
echo html_writer::end_div();

// ---------------------------------------------------------------- per model
if (!empty($usage['models'])) {
    echo html_writer::tag('h3', 'By model', ['style' => 'font-size:1.05em; margin:0 0 8px;']);
    echo html_writer::start_div('al-table-wrap');
    echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'width:100%']);
    echo html_writer::tag('tr', html_writer::tag('th', 'Model') . html_writer::tag('th', 'Requests'));
    foreach ($usage['models'] as $model => $n) {
        echo html_writer::tag('tr',
            html_writer::tag('td', s($model)) .
            html_writer::tag('td', number_format($n), ['class' => 'tnum']));
    }
    echo html_writer::end_tag('table');
    echo html_writer::end_div();
}

// ---------------------------------------------------------------- per day
// Drawn as columns rather than the smooth area OpenAI uses. These are discrete
// daily counts and most days are zero; a continuous curve would sweep a filled
// ribbon across days on which nothing happened, implying traffic that did not
// exist. One bar per day says the same thing without inventing values between
// them. Hover any bar for its exact figures - and the table underneath lists
// the active days, so the numbers are readable without a pointer.
if (!empty($usage['daily'])) {
    $days_list = $usage['daily'];
    $peak      = max(array_map(function ($d) { return $d['requests']; }, $days_list));
    $peak      = $peak ?: 1;
    $active    = array_filter($days_list, function ($d) { return $d['requests'] > 0; });

    echo html_writer::tag('h3', 'Requests per day',
        ['style' => 'font-size:1.05em; margin:26px 0 2px;']);
    $firstday = array_key_first($days_list);
    $lastday  = array_key_last($days_list);
    echo html_writer::tag('p',
        userdate(strtotime($firstday . ' UTC'), '%d %b') . ' – '
        . userdate(strtotime($lastday . ' UTC'), '%d %b %Y')
        . ' · ' . number_format($usage['requests']) . ' requests across '
        . count($active) . ' active ' . (count($active) === 1 ? 'day' : 'days'),
        ['style' => 'margin:0 0 10px; font-size:0.86em; color:var(--al-ink-500);']);

    // Geometry. A viewBox plus width:100% lets it scale to any column width.
    $w = 720; $h = 190;
    $padl = 44; $padr = 12; $padt = 14; $padb = 26;
    $plotw = $w - $padl - $padr;
    $ploth = $h - $padt - $padb;
    $n = count($days_list);
    $gap = 2;
    $barw = max(2, ($plotw - ($gap * ($n - 1))) / $n);

    $svg  = '<svg viewBox="0 0 ' . $w . ' ' . $h . '" role="img" '
          . 'aria-label="Daily OpenAI request counts for the selected window" '
          . 'style="width:100%; height:auto; display:block; overflow:visible;">';

    // Recessive gridlines and y labels at 0, half, peak.
    foreach ([0, 0.5, 1] as $frac) {
        $y = $padt + $ploth - ($ploth * $frac);
        $svg .= '<line x1="' . $padl . '" y1="' . $y . '" x2="' . ($w - $padr) . '" y2="' . $y
             . '" stroke="var(--al-ink-200, #e5e7eb)" stroke-width="1" />';
        $svg .= '<text x="' . ($padl - 8) . '" y="' . ($y + 4) . '" text-anchor="end" '
             . 'font-size="11" fill="var(--al-ink-500, #6b7280)">'
             . number_format((int) round($peak * $frac)) . '</text>';
    }

    $peakday = null;
    $i = 0;
    foreach ($days_list as $day => $d) {
        $x = $padl + ($i * ($barw + $gap));
        $barh = $d['requests'] > 0 ? max(3, ($d['requests'] / $peak) * $ploth) : 0;
        $y = $padt + $ploth - $barh;
        $label = userdate(strtotime($day . ' UTC'), '%d %b') . ' — '
               . number_format($d['requests']) . ' request' . ($d['requests'] === 1 ? '' : 's')
               . ', ' . number_format($d['input']) . ' in / ' . number_format($d['output']) . ' out'
               . ($d['cost'] ? ', $' . number_format($d['cost'], 4) : '');

        $svg .= '<g><title>' . s($label) . '</title>';
        // Full-height transparent hit area, so a 0-request day is still hoverable
        // and a 3px bar does not demand pixel-perfect aim.
        $svg .= '<rect x="' . $x . '" y="' . $padt . '" width="' . $barw . '" height="' . $ploth
             . '" fill="transparent" />';
        if ($barh > 0) {
            // Rounded top, square foot: the bar is anchored to the baseline.
            $r = min(3, $barw / 2, $barh);
            $svg .= '<path d="M' . $x . ',' . ($y + $barh)
                 . ' L' . $x . ',' . ($y + $r)
                 . ' Q' . $x . ',' . $y . ' ' . ($x + $r) . ',' . $y
                 . ' L' . ($x + $barw - $r) . ',' . $y
                 . ' Q' . ($x + $barw) . ',' . $y . ' ' . ($x + $barw) . ',' . ($y + $r)
                 . ' L' . ($x + $barw) . ',' . ($y + $barh) . ' Z" '
                 . 'fill="var(--al-blue-light, #2f6fa6)" />';
        }
        $svg .= '</g>';

        if ($d['requests'] === $peak && $peakday === null) {
            $peakday = ['x' => $x + ($barw / 2), 'y' => $y, 'day' => $day, 'n' => $d['requests']];
        }
        $i++;
    }

    // Exactly one direct label - the peak. Numbering every column would be noise.
    if ($peakday && $peakday['n'] > 0) {
        $anchor = $peakday['x'] > ($w - 70) ? 'end' : ($peakday['x'] < 70 ? 'start' : 'middle');
        $svg .= '<text x="' . $peakday['x'] . '" y="' . max(11, $peakday['y'] - 6) . '" '
             . 'text-anchor="' . $anchor . '" font-size="11" font-weight="600" '
             . 'fill="var(--al-ink-900, #1f2937)">' . number_format($peakday['n']) . '</text>';
    }

    // Baseline plus first/last date ticks.
    $svg .= '<line x1="' . $padl . '" y1="' . ($padt + $ploth) . '" x2="' . ($w - $padr)
         . '" y2="' . ($padt + $ploth) . '" stroke="var(--al-ink-500, #6b7280)" stroke-width="1" />';
    $svg .= '<text x="' . $padl . '" y="' . ($h - 8) . '" font-size="11" '
         . 'fill="var(--al-ink-500, #6b7280)">'
         . userdate(strtotime($firstday . ' UTC'), '%d %b') . '</text>';
    $svg .= '<text x="' . ($w - $padr) . '" y="' . ($h - 8) . '" text-anchor="end" font-size="11" '
         . 'fill="var(--al-ink-500, #6b7280)">'
         . userdate(strtotime($lastday . ' UTC'), '%d %b') . '</text>';
    $svg .= '</svg>';

    echo html_writer::div($svg, 'al-card', ['style' => 'padding:16px 18px 10px;']);

    // Only the days that actually saw traffic. Thirty rows of zeroes told the
    // reader nothing the chart does not already show.
    if ($active) {
        echo html_writer::tag('h3', 'Active days',
            ['style' => 'font-size:1.05em; margin:22px 0 8px;']);
        echo html_writer::start_div('al-table-wrap');
        echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'width:100%']);
        echo html_writer::tag('tr',
            html_writer::tag('th', 'Date') . html_writer::tag('th', 'Requests') .
            html_writer::tag('th', 'Input') . html_writer::tag('th', 'Output') .
            html_writer::tag('th', 'Cost'));
        foreach ($active as $day => $d) {
            echo html_writer::tag('tr',
                html_writer::tag('td', userdate(strtotime($day . ' UTC'), '%d %b %Y')) .
                html_writer::tag('td', number_format($d['requests']), ['class' => 'tnum']) .
                html_writer::tag('td', number_format($d['input']), ['class' => 'tnum']) .
                html_writer::tag('td', number_format($d['output']), ['class' => 'tnum']) .
                html_writer::tag('td', $d['cost'] ? '$' . number_format($d['cost'], 4) : '-',
                    ['class' => 'tnum']));
        }
        echo html_writer::end_tag('table');
        echo html_writer::end_div();
    }
}

if (!empty($usage['fetched_at'])) {
    echo html_writer::tag('p',
        'Read from OpenAI at ' . userdate($usage['fetched_at'], '%d %b %Y, %H:%M')
        . '. Figures are cached for 15 minutes, so a refresh does not make another API call.',
        ['style' => 'margin-top:16px; font-size:0.84em; color:var(--al-ink-500);']);
}

echo html_writer::end_div();
echo $OUTPUT->footer();
