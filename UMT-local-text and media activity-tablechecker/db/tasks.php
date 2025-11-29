<?php
defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'local_tablechecker\task\check_tables',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '2', // Run daily at 2 AM
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
];