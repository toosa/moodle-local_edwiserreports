<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Plugin administration pages are defined here.
 *
 * @package     local_edwiserreports
 * @category    admin
 * @copyright   2019 wisdmlabs <support@wisdmlabs.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_edwiserreports\task;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . "/local/edwiserreports/classes/constants.php");

use local_edwiserreports\controller\progress;
use xmldb_table;
use context_course;

/**
 * Scheduled Task to Update Report Plugin Table.
 */
class active_users_data extends \core\task\scheduled_task {

    /**
     * Object to show progress of task
     * @var \local_edwiserreports\task\progress
     */
    private $progress;

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() {
        return get_string('activeuserstask', 'local_edwiserreports');
    }

    /**
     * Construct
     */
    public function __construct() {

        $this->progress = new progress('activeusers');

    }

    /**
     * Get the first log from the log table
     * @return stdClass | bool firstlog
     */
    public function get_first_log() {
        global $DB;

        $fields = 'id, userid, timecreated';
        $firstlogs = $DB->get_record('logstore_standard_log', array(), $fields, IGNORE_MULTIPLE);

        return $firstlogs;
    }

    /**
     * Constructor
     */
    public function prerequisite() {

        $this->dates = [];

        // Based on the filter select labels.
        switch ($this->filter) {
            case LOCAL_SITEREPORT_WEEKLY:
                // Weekly days.
                $this->xlabelcount = LOCAL_SITEREPORT_WEEKLY_DAYS;
                break;
            case LOCAL_SITEREPORT_MONTHLY:
                // Monthly days.
                $this->xlabelcount = LOCAL_SITEREPORT_MONTHLY_DAYS;
                break;
            case LOCAL_SITEREPORT_YEARLY:
                // Yearly days.
                $this->xlabelcount = LOCAL_SITEREPORT_YEARLY_DAYS;
                break;
        }

        // Get all lables.
        for ($i = 0; $i < $this->xlabelcount; $i++) {
            $time = $this->timenow - $i * LOCAL_SITEREPORT_ONEDAY;
            $this->dates[floor($time / LOCAL_SITEREPORT_ONEDAY)] = 0;
        }
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;

        $filters = ['weekly', 'monthly', 'yearly'];

        // Set current time.
        $this->timenow = time();

        // Data for graph.
        $activeusers = [];

        echo "\n....................................................................................\n";
        foreach ($filters as $filter) {
            // Generate data.
            echo "Calculating " . $filter . " data:\n";
            $this->filter = $filter;
            $this->prerequisite();

            echo "Calculating active users:";
            // Start progress.
            $this->progress->start_progress();
            $activeusers[$filter]['activeusers'] = $this->get_active_users();
            // End progress.
            $this->progress->end_progress();

            echo "\nCalculating enrolments:";
            // Start progress.
            $this->progress->start_progress();
            $activeusers[$filter]['enrolments'] = $this->get_enrolments();
            // End progress.
            $this->progress->end_progress();

            echo "\nCalculating course completion rate:";
            // Start progress.
            $this->progress->start_progress();
            $activeusers[$filter]['completionrate'] = $this->get_course_completionrate();
            // End progress.
            $this->progress->end_progress();
            echo "\n....................................................................................\n";
        }
        set_config('activeusersdata', json_encode($activeusers), 'local_edwiserreports');
        return true;
    }

    /**
     * Get all active users
     * @return array           Array of all active users based
     */
    public function get_active_users() {
        global $DB;

        $starttime = $this->timenow - ($this->xlabelcount * LOCAL_SITEREPORT_ONEDAY);
        $params = array(
            "starttime" => $starttime,
            "endtime" => $this->timenow,
            "action" => "viewed"
        );

        $sql = "SELECT FLOOR(l.timecreated/86400) as userdate,
                        COUNT( DISTINCT l.userid ) as usercount
                FROM {logstore_standard_log} l
                WHERE l.action = :action
                    AND l.timecreated >= :starttime
                    AND l.timecreated < :endtime
                    AND l.userid > 1
                    GROUP BY FLOOR(l.timecreated/86400)";

        // Get Logs to generate active users data.
        $activeusers = $this->dates;

        $logs = $DB->get_records_sql($sql, $params);
        $logcount = count($logs);
        $progress = 0;
        $updater = 0;
        $increament = $logcount != 0 ? 100 / count($logs) : 1;
        // Get active users for every day.
        foreach (array_keys($activeusers) as $key) {
            $progress += $increament;
            if (++$updater >= 500) {
                $updater = 0;
                $this->progress->update_progress($progress);
            }
            if (!isset($logs[$key])) {
                continue;
            }
            $activeusers[$key] = $logs[$key]->usercount;
        }

        $activeusers = array_values($activeusers);

        /* Reverse the array because the graph take
        value from left to right */
        return array_reverse($activeusers);
    }

    /**
     * Get all Enrolments
     * @return array            Array of all active users based
     */
    public function get_enrolments() {
        global $DB;

        $starttime = $this->timenow - ($this->xlabelcount * LOCAL_SITEREPORT_ONEDAY);
        $params = array(
            "starttime" => $starttime,
            "endtime" => $this->timenow,
            "eventname" => '\core\event\user_enrolment_created',
            "actionname" => "created"
        );

        $sql = "SELECT FLOOR(l.timecreated/86400) as userdate,
                    COUNT(
                        DISTINCT(
                            CONCAT(
                                CONCAT(l.courseid, '-')
                                , l.relateduserid
                            )
                        )
                    )
                    as usercount
                FROM {logstore_standard_log} l
                WHERE l.eventname = :eventname
                AND l.action = :actionname
                AND l.timecreated >= :starttime
                AND l.timecreated < :endtime
                GROUP BY FLOOR(l.timecreated/86400)";

        // Get enrolments log.
        $logs = $DB->get_records_sql($sql, $params);
        $enrolments = $this->dates;

        $logcount = count($logs);
        $progress = 0;
        $updater = 0;
        $increament = $logcount != 0 ? 100 / count($logs) : 1;

        // Get enrolments from every day.
        foreach (array_keys($enrolments) as $key) {
            $progress += $increament;
            if (++$updater >= 500) {
                $updater = 0;
                $this->progress->update_progress($progress);
            }
            if (!isset($logs[$key])) {
                continue;
            }
            $enrolments[$key] = $logs[$key]->usercount;
        }

        $enrolments = array_values($enrolments);

        /* Reverse the array because the graph take
        value from left to right */
        return array_reverse($enrolments);
    }

    /**
     * Get all Enrolments
     * @return array            Array of all active users based
     */
    public function get_course_completionrate() {
        global $DB;

        $starttime = $this->timenow - ($this->xlabelcount * LOCAL_SITEREPORT_ONEDAY);
        $params = array(
            "starttime" => $starttime,
            "endtime" => $this->timenow
        );

        $sql = "SELECT FLOOR(cc.completiontime/86400) as userdate,
                       COUNT(
                           CONCAT(
                               CONCAT(cc.courseid, '-'),
                               cc.userid
                           )
                       ) as usercount
                  FROM {edwreports_course_progress} cc
                 WHERE cc.completiontime IS NOT NULL
                    AND cc.completiontime >= :starttime
                    AND cc.completiontime < :endtime
                 GROUP BY FLOOR(cc.completiontime/86400)";

        $completionrate = $this->dates;
        $logs = $DB->get_records_sql($sql, $params);

        $logcount = count($logs);
        $progress = 0;
        $updater = 0;
        $increament = $logcount != 0 ? 100 / count($logs) : 1;

        // Get completion for each day.
        foreach (array_keys($completionrate) as $key) {
            $progress += $increament;
            if (++$updater >= 500) {
                $updater = 0;
                $this->progress->update_progress($progress);
            }
            if (!isset($logs[$key])) {
                continue;
            }
            $completionrate[$key] = $logs[$key]->usercount;
        }

        $completionrate = array_values($completionrate);

        /* Reverse the array because the graph take
        value from left to right */
        return array_reverse($completionrate);
    }
}
