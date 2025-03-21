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

namespace local_edwiserreports;

use local_edwiserreports\utility;
use context_course;
use stdClass;
use cache;

/**
 * Class Acive Users Block. To get the data related to active users block.
 */
class activecoursesblock extends block_base {

    /**
     * Initialize the block.
     */
    public function __construct() {
        parent::__construct();
        $this->cache = cache::make('local_edwiserreports', 'activecourses');
    }
    /**
     * Preapre layout for active courses block
     * @return object Layout object
     */
    public function get_layout() {
        global $CFG;

        // Layout related data.
        $this->layout->id = 'activecoursesblock';
        $this->layout->name = get_string('activecoursesheader', 'local_edwiserreports');
        $this->layout->info = get_string('activecoursesblockhelp', 'local_edwiserreports');
        $this->layout->downloadlinks = $this->get_block_download_links();
        $this->layout->filters = $this->get_filters();

        // Block related data.
        $this->block->displaytype = 'line-chart';

        // Add block view in layout.
        $this->layout->blockview = $this->render_block('activecoursesblock', $this->block);
        // Set block edit capabilities.
        $this->set_block_edit_capabilities($this->layout->id);

        // Return blocks layout.
        return $this->layout;
    }

    /**
     * Prepare Inactive users filter
     * @return string Filter HTML content
     */
    public function get_filters() {
        global $OUTPUT;
        return $OUTPUT->render_from_template('local_edwiserreports/common-table-search-filter', [
            'searchicon' => $this->image_icon('actions/search'),
            'placeholder' => get_string('searchcourse', 'local_edwiserreports')
        ]);
    }

    /**
     * Filter active courses data based on enrolled courses.
     *
     * @param array $data Data to be filtered.
     *
     * @return array
     */
    public function filter_active_courses($data) {
        $userid = $this->get_current_user();
        $courses = $this->get_courses_of_user($userid);
        unset($courses[SITEID]);
        $courses = array_keys($courses);
        $filtered = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $courses)) {
                $filtered[] = $value;
            }
        }
        return $filtered;
    }

    /**
     * Get Data for Active Courses
     * @param  object $params Parameteres
     * @return object         Response for Active Courses
     */
    public function get_data($params = false) {
        $response = new stdClass();

        if (!$data = $this->cache->get('activecoursesdata')) {
            $data = get_config('local_edwiserreports', 'activecoursesdata');
            if (!$data || !$data = json_decode($data, true)) {
                $data = $this->get_course_data();
            } else {
                $data = $this->filter_active_courses($data);
            }
            $this->cache->set('activecoursesdata', $data);
        }

        $response->data = $data;
        return $response;
    }

    /**
     * Get Active Courses data
     * @return array         Array of course active records
     */
    public function get_course_data() {
        global $DB;

        $userid = $this->get_current_user();
        $courses = $this->get_courses_of_user($userid);

        $count = 1;
        $response = array();

        $params = array("progress" => 100);

        // Calculate Completion Count for All Course.
        $sql = "SELECT ecp.courseid, COUNT(ecp.userid) AS users
            FROM {edwreports_course_progress} ecp
            WHERE ecp.progress = :progress
            GROUP BY ecp.courseid";
        // Get records with 100% completions.
        $coursecompletion = $DB->get_records_sql($sql, $params);

        foreach ($courses as $course) {
            // If moodle course then return false.
            if ($course->id == 1) {
                continue;
            }

            // Get Course Context.
            $coursecontext = context_course::instance($course->id);

            // Get Enrolled users
            // 'moodle/course:isincompletionreports' - this capability is allowed to only students.
            $enrolledstudents = utility::get_enrolled_students($course->id, $coursecontext);
            if (empty($enrolledstudents)) {
                continue;
            }

            // Create a record for responce.
            $res = array(
                $count++,
                $course->fullname
            );

            $res[] = count($enrolledstudents);

            // Get Completion count.
            if (!isset($coursecompletion[$course->id])) {
                $completedusers = 0;
            } else {
                $completedusers = $coursecompletion[$course->id]->users;
            }

            $res[] = self::get_courseview_count($course->id, array_keys($enrolledstudents));
            $res[] = $completedusers;
            $response[] = $res;
        }
        return $response;
    }

    /**
     * Get Course View Count by users
     * @param  int   $courseid    Course Id
     * @param  array $studentsids Array of enrolled uesers id
     * @return int                Number of course views by users
     */
    public static function get_courseview_count($courseid, $studentsids) {
        global $DB;

        $userstable = '';
        if (!empty($studentsids)) {
            // Create a temporary table for enrolled users.
            $userstable = utility::create_temp_table('tmp_ac_users', $studentsids);
        }

        $params = [
            'courseid' => $courseid,
            'action' => 'viewed'
        ];

        $sqlcourseview = "SELECT COUNT(DISTINCT lsl.userid) as usercount
            FROM {logstore_standard_log} lsl
            JOIN {{$userstable}} ut ON lsl.userid = ut.tempid
            WHERE lsl.action = :action
            AND lsl.courseid = :courseid";

        $views = $DB->get_record_sql($sqlcourseview, $params);

        if (!empty($studentsids)) {
            // Drop temporary table.
            utility::drop_temp_table($userstable);
        }
        return $views->usercount;
    }

    /**
     * Get headers for Active Courses Block
     * @return array Array of header of course block
     */
    public static function get_header() {
        $header = array(
            get_string("coursename", "local_edwiserreports"),
            get_string("enrolments", "local_edwiserreports"),
            get_string("visits", "local_edwiserreports"),
            get_string("completions", "local_edwiserreports"),
        );
        return $header;
    }

    /**
     * Get Exportable data for Active Courses Block
     * @return array Array of exportable data
     */
    public static function get_exportable_data_block() {

        $export = array();
        $header = self::get_header();

        $classobj = new self();
        $activecoursesdata = $classobj->get_data();
        $exportdata = array_map(function ($data) {
            array_splice($data, 0, 1);
            return $data;
        }, $activecoursesdata->data);
        $export = array_merge(
            array($header),
            $exportdata
        );

        return $export;
    }
}
