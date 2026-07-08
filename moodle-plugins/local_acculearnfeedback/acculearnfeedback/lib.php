<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Renders the AccuLearn toolbar on every page for logged-in users.
 * Uses before_footer since Boost's extend_navigation placement is
 * inconsistent across Moodle versions - this guarantees visibility.
 */
function local_acculearnfeedback_before_footer() {
    global $CFG;

    if (!isloggedin() || isguestuser()) {
        return '';
    }

    $context = context_system::instance();
    $isteacher = has_capability('moodle/course:update', $context);

    $items = [];
    $items[] = [
        'url' => $CFG->wwwroot . '/local/acculearnfeedback/my_track.php',
        'icon' => 'fa-route',
        'label' => 'My Learning Track',
    ];

    if ($isteacher) {
        $items[] = [
            'url' => $CFG->wwwroot . '/local/acculearnfeedback/teacher_review.php',
            'icon' => 'fa-clipboard-check',
            'label' => 'AI Output Review',
        ];
        $items[] = [
            'url' => $CFG->wwwroot . '/local/acculearnfeedback/pretest_entry.php',
            'icon' => 'fa-pencil',
            'label' => 'Pre-Test Entry',
        ];
    }

    $itemshtml = '';
    foreach ($items as $item) {
        $itemshtml .= '
            <a href="' . $item['url'] . '" style="
                display:flex; align-items:center; gap:8px;
                color:#e8ecf5; text-decoration:none; font-size:0.92em; font-weight:500;
                padding:10px 16px; border-radius:8px; transition:background 0.15s;
            " onmouseover="this.style.background=\'rgba(255,255,255,0.12)\'" onmouseout="this.style.background=\'transparent\'">
                <i class="fa ' . $item['icon'] . '" style="font-size:1em; opacity:0.85;"></i>
                <span>' . $item['label'] . '</span>
            </a>';
    }

    echo '
        <div style="
            position:fixed; bottom:0; left:0; right:0; z-index:1000;
            background:linear-gradient(135deg, #1e2749 0%, #2d3561 100%);
            box-shadow:0 -3px 12px rgba(0,0,0,0.25);
            display:flex; align-items:center; justify-content:center;
            padding:6px 20px; flex-wrap:wrap; gap:4px;
        ">
            <div style="
                display:flex; align-items:center; gap:8px; margin-right:12px;
                color:#8b93b8; font-size:0.8em; font-weight:600; letter-spacing:0.5px; text-transform:uppercase;
            ">
                <i class="fa fa-brain"></i> AccuLearn
            </div>
            ' . $itemshtml . '
        </div>
        <div style="height:56px;"></div>
    ';
}
