<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('reports', new admin_externalpage('tablechecker_report',
        get_string('pluginname', 'local_tablechecker'),
        "$CFG->wwwroot/local/tablechecker/report.php",
        'local/tablechecker:viewreport'
    ));
}