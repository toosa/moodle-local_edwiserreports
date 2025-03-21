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

defined('MOODLE_INTERNAL') || die();

use stdClass;
use core_user;
use context_course;
use html_writer;
use html_table;
use html_table_cell;
use html_table_row;
use moodle_url;

/**
 * Class Course Engagement Block. To get the data related to course engagement block.
 */
class courseengageblock extends utility {
    /**
     * Get data for course engagement block
     * @param  int    $cohortid Cohort id
     * @return object           Response data
     */
    public static function get_data($cohortid) {
        $response = new stdClass();
        $response->data = self::get_courseengage($cohortid);

        return $response;
    }

    /**
     * Get Course Engagement Data
     * @param  int   $cohortid Cohort id
     * @return array           Array of course engagement
     */
    public static function get_courseengage($cohortid) {
        global $CFG, $DB;

        $engagedata = array();
        $courses = self::get_courses(true);
        $params = array();
        $cohortjoin = '';
        $cohortcondition = '';
        if ($cohortid) {
            $cohortjoin = 'JOIN {cohort_members} cm ON cm.userid = u.id';
            $cohortcondition = 'AND cm.cohortid = :cohortid';
            $params["cohortid"] = $cohortid;
        }

        $fields = 'c.courseid, COUNT(c.userid) AS usercount';
        $completionsql = "SELECT $fields
            FROM {edwreports_course_progress} c
            JOIN {user} u ON u.id = c.userid  $cohortjoin
            WHERE c.progress >= :completionstart
            AND c.progress <= :completionend
            AND u.deleted = 0
            $cohortcondition
            GROUP BY c.courseid";

        // Calculate atleast completed one modules.
        $fields = 'c.courseid, COUNT(c.userid) AS usercount';
        if ($CFG->dbtype == 'sqlsrv') {
            $where = "(LEN(completedmodules) -
                        LEN(REPLACE(completedmodules, ',', '')) + 1
                      ) >= :completedactivities";
        } else {
            $where = "(LENGTH(completedmodules) -
                        LENGTH(REPLACE(completedmodules, ',', '')) + 1
                      ) >= :completedactivities";
        }
        $completionmodulesql = "SELECT $fields
            FROM {edwreports_course_progress} c
            JOIN {user} u ON u.id = c.userid
            $cohortjoin
            WHERE $where
            AND u.deleted = 0
            $cohortcondition
            GROUP BY c.courseid";

        // Calculate 50% Completion Count for Courses.
        $params["completionstart"] = 50;
        $params["completionend"] = 99;
        $completion50 = $DB->get_records_sql($completionsql, $params);

        // Calculate 100% Completion Count for Courses.
        $params["completionstart"] = 100.00;
        $params["completionend"] = 100.00;
        $completion100 = $DB->get_records_sql($completionsql, $params);

        $params ["completedactivities"] = 1;
        $completiononemodule = $DB->get_records_sql($completionmodulesql, $params);
        foreach ($courses as $course) {
            $values = array(
                "completed50" => 0,
                "completed100" => 0,
                "completiononemodule" => 0
            );

            if (isset($completion50[$course->id])) {
                $values["completed50"] = $completion50[$course->id]->usercount;
            }

            if (isset($completion100[$course->id])) {
                $values["completed100"] = $completion100[$course->id]->usercount;
            }

            if (isset($completiononemodule[$course->id])) {
                $values["completiononemodule"] = $completiononemodule[$course->id]->usercount;
            }
            $courseenageresp = self::get_engagement($course, $cohortid, $values);
            if ($courseenageresp) {
                $engagedata[] = $courseenageresp;
            }
        }
        return $engagedata;
    }

    /**
     * Get Course Engagement for a course
     * @param object $course   Course object
     * @param int    $cohortid Cohort id
     * @param array  $values   Values
     * @return object          Engagement data
     */
    public static function get_engagement($course, $cohortid, $values) {
        global $CFG;

        // Create engagement object.
        $engagement = new stdClass();

        // Get only enrolled students.
        $enrolledstudents = \local_edwiserreports\utility::get_enrolled_students($course->id, false, $cohortid);

        // Get course name with course url.
        $engagement->coursename = html_writer::link(
            new moodle_url(
                "/local/edwiserreports/completion.php",
                array(
                    "courseid" => $course->id,
                    'backurl' => new moodle_url('/local/edwiserreports/coursereport.php#engagement')
                )
            ),
            $course->fullname
        );

        // Generate enrolments link.
        $engagement->enrolment = self::get_course_engagement_link(
            "enrolment",
            $course,
            count($enrolledstudents)
        );

        // Generate visits link.
        $engagement->visited = self::get_course_engagement_link(
            "visited",
            $course,
            self::get_course_visites($course->id, $cohortid, true)
        );

        // Generate activity started link.
        $engagement->activitystart = self::get_course_engagement_link(
            "activitystart",
            $course,
            $values["completiononemodule"]
        );

        // Generate completed 50% of course link.
        $engagement->completedhalf = self::get_course_engagement_link(
            "completedhalf",
            $course,
            $values["completed50"]
        );

        // Generate course completion link.
        $engagement->coursecompleted = self::get_course_engagement_link(
            "coursecompleted",
            $course,
            $values["completed100"]
        );

        // Return engagement object.
        return $engagement;
    }

    /**
     * Get Engagement Attributes
     * @param  string $attrname Attribute name
     * @param  object $course   Course object
     * @param  string $val      Value for link
     * @return string           HTML link
     */
    public static function get_course_engagement_link($attrname, $course, $val) {
        return html_writer::link("javascript:void(0)", $val,
            array(
                "class" => "modal-trigger text-decoration-none",
                "data-courseid" => $course->id,
                "data-coursename" => $course->fullname,
                "data-action" => $attrname
            )
        );
    }

    /**
     * Get HTML table for userslist
     * @param  int    $courseid Course ID
     * @param  string $action   Action to get Users Data
     * @param  object $cohortid Cohort id
     * @return array            Array of users list
     */
    public static function get_userslist_table($courseid, $action, $cohortid) {
        global $OUTPUT;
        $context = new stdClass;
        $context->searchicon = \local_edwiserreports\utility::image_icon('actions/search');
        $context->placeholder = get_string('searchuser', 'local_edwiserreports');
        $context->length = [10, 25, 50, 100];
        $filter = $OUTPUT->render_from_template('local_edwiserreports/common-table-search-filter', $context);

        $table = new html_table();
        $table->attributes = array (
            "class" => "modal-table table",
            "style" => "min-width: 100%;",
        );

        // Get userslist to display.
        $data = (object) self::get_userslist($courseid, $action, $cohortid);

        $table->head = $data->head;
        if (!empty($data->data)) {
            $table->data = $data->data;
        }
        return $filter . html_writer::table($table);
    }

    /**
     * Get Users list
     * @param  int    $courseid Course ID
     * @param  string $action   Action to get Users Data
     * @param  object $cohortid Cohort id
     * @return array            Array of users list
     */
    public static function get_userslist($courseid, $action, $cohortid) {
        $course = get_course($courseid);

        switch($action) {
            case "enrolment":
                $usersdata = self::get_enrolled_users($course, $cohortid);
                ;
                break;
            case "visited":
                $usersdata = self::get_visited_users($course, $cohortid);
                ;
                break;
            case "activitystart":
                $usersdata = self::get_users_started_an_activity($course, $cohortid);
                break;
            case "completedhalf":
                $usersdata = self::get_users_completed_half_courses($course, $cohortid);
                break;
            case "coursecompleted":
                $usersdata = self::get_users_completed_courses($course, $cohortid);
                break;
        }
        return $usersdata;
    }

    /**
     * Get Users who visited the Course
     * @param  Integer $courseid Course ID to get all visits
     * @param  Integer $cohortid Cohort id
     * @param  Bool    $count    If count is true then return count of users
     * @return Array             Array of Users ID who visited the course
     */
    public static function get_course_visites($courseid, $cohortid, $count = false) {
        global $DB;

        $params = array(
            "courseid" => $courseid,
            "action" => "viewed",
            "archetype" => "student",
            "contextlevel" => CONTEXT_COURSE
        );

        $cohortsql = "";
        if ($cohortid) {
            $params["cohortid"] = $cohortid;
            $cohortsql = "JOIN {cohort_members} cm ON l.userid = cm.userid AND cm.cohortid = :cohortid
                          JOIN {cohort} c ON cm.cohortid = c.id AND c.visible = 1";
        }

        $select = "";
        $groupby = "";
        if ($count == true) {
            $select = "COUNT(l.userid) AS usercount";
        } else {
            $select = "u.id, u.firstname, u.lastname, u.email, COUNT(l.userid) AS visits";
            $groupby = "GROUP BY u.id, u.firstname, u.lastname, u.email";
        }

        $sql = "SELECT $select
            FROM {logstore_standard_log} l
            JOIN {user} u ON u.id = l.userid
            $cohortsql
            JOIN {context} ctx ON l.courseid = ctx.instanceid AND ctx.contextlevel = :contextlevel
            JOIN {role_assignments} ra ON u.id = ra.userid AND ctx.id = ra.contextid
            JOIN {role} r ON ra.roleid = r.id
            WHERE l.action = :action
            AND l.courseid = :courseid
            AND u.deleted = 0
            AND r.archetype = :archetype
            $groupby";
        if ($count == true) {
            return $DB->get_record_sql($sql, $params)->usercount;
        } else {
            return $DB->get_records_sql($sql, $params);
        }
    }

    /**
     * Get Enrolled users in a course
     * @param  object $course   Course Object
     * @param  object $cohortid Cohort id
     * @return array            Array of users list
     */
    public static function get_enrolled_users($course, $cohortid) {
        $users = \local_edwiserreports\utility::get_enrolled_students($course->id, false, $cohortid);

        $usersdata = new stdClass();
        $usersdata->head = array(
            get_string("name", "local_edwiserreports"),
            get_string("email", "local_edwiserreports")
        );

        $usersdata->data = array();
        foreach ($users as $user) {
            $usersdata->data[] = array(
                $user->firstname . " " . $user->lastname,
                $user->email,
            );
        }
        return $usersdata;
    }

    /**
     * Get Visited users in a course
     * @param  object $course   Course Object
     * @param  object $cohortid Cohort id
     * @return array            Array of users list
     */
    public static function get_visited_users($course, $cohortid) {
        $users = self::get_course_visites($course->id, $cohortid);
        $usersdata = new stdClass();
        $usersdata->head = array(
            get_string("name", "local_edwiserreports"),
            get_string("email", "local_edwiserreports"),
            get_string("visits", "local_edwiserreports")
        );

        $usersdata->data = array();
        foreach ($users as $user) {
            $usersdata->data[] = array(
                $user->firstname . ' ' . $user->lastname,
                $user->email,
                $user->visits
            );
        }
        return $usersdata;
    }

    /**
     * Get Users Who have complted atleast one activity in a course
     * @param  Object $course   Course
     * @param  Array  $users    Enrolled Users
     * @return Array            Array of Users ID who have completed a activity
     */
    public static function users_completed_a_module($course, $users) {
        $records = array();

        foreach ($users as $user) {
            $completion = self::get_course_completion_info($course, $user->id);
            if ($completion["completedactivities"] > 0) {
                $records[] = $user;
            }
        }

        return $records;
    }

    /**
     * Get users who have completed an activity
     * @param  object $course   Course Object
     * @param  object $cohortid Cohort id
     * @return array            Array of users list
     */
    public static function get_users_started_an_activity($course, $cohortid) {
        $enrolledusers = \local_edwiserreports\utility::get_enrolled_students($course->id, false, $cohortid);
        $users = self::users_completed_a_module($course, $enrolledusers);
        $usersdata = new stdClass();
        $usersdata->head = array(
            get_string("name", "local_edwiserreports"),
            get_string("email", "local_edwiserreports")
        );

        $usersdata->data = array();
        foreach ($users as $user) {
            $usersdata->data[] = array(
                $user->firstname . ' ' . $user->lastname,
                $user->email
            );
        }
        return $usersdata;
    }

    /**
     * Get users who have completed half of the course
     * @param  object $course   Course Object
     * @param  object $cohortid Cohort id
     * @return array            Array of users list
     */
    public static function get_users_completed_half_courses($course, $cohortid) {
        $enrolledusers = \local_edwiserreports\utility::get_enrolled_students($course->id, false, $cohortid);

        // Get completions.
        $compobj = new \local_edwiserreports\completions();
        $completions = $compobj->get_course_completions($course->id);

        $usersdata = new stdClass();
        $usersdata->head = array(
            get_string("name", "local_edwiserreports"),
            get_string("email", "local_edwiserreports")
        );

        $usersdata->data = array();
        foreach ($enrolledusers as $user) {
            $progress = isset($completions[$user->id]->completion) ? $completions[$user->id]->completion : 0;
            if ($progress >= 50 && $progress < 100) {
                $usersdata->data[] = array(
                    $user->firstname . ' ' . $user->lastname,
                    $user->email,
                );
            }
        }
        return $usersdata;
    }

    /**
     * Get users who have completed the course
     * @param  object $course   Course Object
     * @param  int    $cohortid Cohort id
     * @return array            Array of users list
     */
    public static function get_users_completed_courses($course, $cohortid) {
        $enrolledusers = \local_edwiserreports\utility::get_enrolled_students($course->id, false, $cohortid);

        // Get completions.
        $compobj = new \local_edwiserreports\completions();
        $completions = $compobj->get_course_completions($course->id);

        $usersdata = new stdClass();
        $usersdata->head = array(
            get_string("name", "local_edwiserreports"),
            get_string("email", "local_edwiserreports")
        );

        $usersdata->data = array();
        foreach ($enrolledusers as $user) {
            $progress = isset($completions[$user->id]->completion) ? $completions[$user->id]->completion : 0;
            if ($progress == 100) {
                $usersdata->data[] = array(
                    $user->firstname . ' ' . $user->lastname,
                    $user->email,
                );
            }
        }
        return $usersdata;
    }



    /**
     * Get Header for report
     * @return array Header array
     */
    public static function get_header_report() {
        $header = array(
            get_string("coursename", "local_edwiserreports"),
            get_string("enrolments", "local_edwiserreports"),
            get_string("visits", "local_edwiserreports"),
            get_string("activitystart", "local_edwiserreports"),
            get_string("completedhalf", "local_edwiserreports"),
            get_string("coursecompleted", "local_edwiserreports")
        );
        return $header;
    }

    /**
     * Get Exportable data for Course Engage Page
     * @return array Array of exportable data
     */
    public static function get_exportable_data_report() {
        $cohortid = optional_param("cohortid", 0, PARAM_INT);
        $export[] = self::get_header_report();

        $data = self::get_courseengage($cohortid);
        foreach ($data as $val) {
            $row = array();
            foreach ($val as $v) {
                $row[] = strip_tags($v);
            }
            $export[] = $row;
        }
        return $export;
    }
}
