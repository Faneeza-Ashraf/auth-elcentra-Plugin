<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'local/tablechecker:viewreport' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),
);