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

    return true;
}
