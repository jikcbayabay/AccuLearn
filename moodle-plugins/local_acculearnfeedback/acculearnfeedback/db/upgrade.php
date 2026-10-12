<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade steps for local_acculearnfeedback.
 *
 * Deliberately does NOT create acculearn_competencies, acculearn_feedback,
 * acculearn_learning_tracks, acculearn_pretest_responses or
 * acculearn_question_competency - those already exist on this site as
 * unprefixed tables created outside Moodle's schema management (see the
 * long comment in db/install.xml). $dbman->table_exists() checks for the
 * PREFIXED name, so it would report those as "missing" and attempting to
 * create them here would produce duplicate, differently-named tables next
 * to the real (unprefixed) ones already in use. Only the two genuinely new
 * tables below are created through the normal Moodle DB manager.
 */
function xmldb_local_acculearnfeedback_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026072901) {

        $table = new xmldb_table('acculearn_review_log');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('entitytype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('entityid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('target_userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('reviewer_userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('action', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('note', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('target_userid', XMLDB_INDEX_NOTUNIQUE, ['target_userid']);
        $table->add_index('reviewer_userid', XMLDB_INDEX_NOTUNIQUE, ['reviewer_userid']);
        $table->add_index('entitytype_entityid', XMLDB_INDEX_NOTUNIQUE, ['entitytype', 'entityid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('acculearn_usage_log');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('action', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('target_type', XMLDB_TYPE_CHAR, '30', null, null, null, null);
        $table->add_field('target_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        $table->add_index('timecreated', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);
        $table->add_index('action', XMLDB_INDEX_NOTUNIQUE, ['action']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026072901, 'local', 'acculearnfeedback');
    }

    if ($oldversion < 2026072904) {

        // 'detail' carries a human-readable description of WHAT was viewed or
        // done, captured at event time. Previously the log held only an action
        // slug plus a bare numeric id ("view_feedback / attempt #9"), which is
        // unreadable in a report and becomes meaningless once the underlying
        // activity is renamed or deleted. Recording the name at the moment of
        // the event keeps the research log a faithful historical record.
        $table = new xmldb_table('acculearn_usage_log');
        $field = new xmldb_field('detail', XMLDB_TYPE_TEXT, null, null, null, null, null, 'target_id');
        if ($dbman->table_exists($table) && !$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Course context, so engagement can be broken down per competency
        // (each competency is its own course) - one of the metrics the study
        // commits to reporting in Sections 3.5.3 / 3.8.1.
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'detail');
        if ($dbman->table_exists($table) && !$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $index = new xmldb_index('userid_timecreated', XMLDB_INDEX_NOTUNIQUE, ['userid', 'timecreated']);
        if ($dbman->table_exists($table) && !$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2026072904, 'local', 'acculearnfeedback');
    }

    if ($oldversion < 2026081002) {

        // Paper POST-test responses, for the study's pre/post comparison.
        //
        // A separate table rather than a 'phase' column on
        // acculearn_pretest_responses. That table has a UNIQUE(userid,
        // questionid) index and is written with INSERT ... ON DUPLICATE KEY
        // UPDATE, so the post-test answer to a question would land on the
        // student's PRE-test row and overwrite it - silently destroying the
        // baseline half of the comparison. Adding the phase to that unique
        // index instead would mean rebuilding an index on live data holding
        // every student's baseline, for no gain over a clean new table.
        //
        // This one is created the normal Moodle way, so unlike the five legacy
        // tables it DOES carry the site prefix and must be queried as
        // {acculearn_posttest_responses}.
        $table = new xmldb_table('acculearn_posttest_responses');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('questionid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('competency_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('confidence', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null);
        $table->add_field('is_correct', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null);
        $table->add_field('entered_at', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        $table->add_index('competency_id', XMLDB_INDEX_NOTUNIQUE, ['competency_id']);
        // Required, not just for integrity: the save path relies on it for
        // ON DUPLICATE KEY UPDATE, so re-entering a student's answer corrects
        // the existing row instead of appending a second one.
        $table->add_index('userid_questionid', XMLDB_INDEX_UNIQUE, ['userid', 'questionid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026081002, 'local', 'acculearnfeedback');
    }

    if ($oldversion < 2026100500) {
        // Teachers can now add and edit quiz questions (the Quiz Questions
        // page, quiz_questions.php). The editing itself happens in Moodle's
        // question bank, so the AccuLearn teacher role needs the question
        // capabilities as well as the new page capability. Granted at system
        // context because that is where the role is assigned, and the banks
        // live in front-page module contexts no course enrolment reaches.
        //
        // Deliberately NOT granted: moodle/question:managecategory. Teachers
        // must not be able to rename or delete the Easy/Medium/Hard pool
        // categories, which the quizzes' random slots are wired to.
        //
        // Moodle only registers a plugin's new capabilities after this
        // function returns, and assign_capability() refuses one it does not
        // know yet - so register db/access.php first.
        update_capabilities('local_acculearnfeedback');
        $role = $DB->get_record('role', ['shortname' => 'acculearnteacher']);
        if ($role) {
            $syscontext = context_system::instance();
            foreach ([
                'local/acculearnfeedback:managequizquestions',
                'moodle/question:add',
                'moodle/question:viewall',
                'moodle/question:editall',
                'moodle/question:moveall',
                'moodle/question:useall',
                'mod/qbank:view',
            ] as $capability) {
                assign_capability($capability, CAP_ALLOW, $role->id, $syscontext->id, true);
            }
            $syscontext->mark_dirty();
        }

        upgrade_plugin_savepoint(true, 2026100500, 'local', 'acculearnfeedback');
    }

    if ($oldversion < 2026100501) {
        // Quiz Questions is for teachers only. 2026100500 handed the page
        // capability to the manager-archetype roles (manager, superadmin) by
        // default; take it back off every role that is not a teacher role.
        $syscontext = context_system::instance();
        $roles = $DB->get_records_sql("
            SELECT DISTINCT r.id, r.shortname
              FROM {role} r
              JOIN {role_capabilities} rc ON rc.roleid = r.id
             WHERE rc.capability = ?
               AND r.shortname NOT IN ('acculearnteacher', 'editingteacher')",
            ['local/acculearnfeedback:managequizquestions']);
        foreach ($roles as $role) {
            unassign_capability('local/acculearnfeedback:managequizquestions', $role->id);
        }
        $syscontext->mark_dirty();

        upgrade_plugin_savepoint(true, 2026100501, 'local', 'acculearnfeedback');
    }

    if ($oldversion < 2026100502) {
        // Activity Completion moves from the admin roles to the teachers:
        // grant the new page capability to the AccuLearn teacher role (it is
        // registered first - see 2026100500 for why).
        update_capabilities('local_acculearnfeedback');
        $syscontext = context_system::instance();
        $role = $DB->get_record('role', ['shortname' => 'acculearnteacher']);
        if ($role) {
            assign_capability('local/acculearnfeedback:viewcompletion', CAP_ALLOW, $role->id, $syscontext->id, true);
        }
        $syscontext->mark_dirty();

        upgrade_plugin_savepoint(true, 2026100502, 'local', 'acculearnfeedback');
    }

    return true;
}
