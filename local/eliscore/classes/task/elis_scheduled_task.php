<?php

/**
 * Created by PhpStorm.
 * User: Mahtab
 * Date: 7/14/2018
 * Time: 11:12 AM
 */

namespace local_eliscore\task;

/**
 * @return cron task
 * 
 */
class elis_scheduled_task extends \core\task\scheduled_task
{
    public function get_name()
    {
        // Shown in admin screens
        return get_string('eliscorecron', 'local_eliscore');
    }

    public function execute()
    {
        global $CFG;
        require_once($CFG->dirroot . '/local/eliscore/lib.php');
        local_eliscore_cron_scheduledtask();
    }
}