<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_local_tablechecker_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2025102501) {

        // Define table local_tablechecker_status to be amended.
        $table = new xmldb_table('local_tablechecker_status');
        
        // Add field timemodified.
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, false, '0', 'timechecked');
        
        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Tablechecker savepoint reached.
        upgrade_plugin_savepoint(true, 2025102501, 'local', 'tablechecker');
    }

    return true;
}