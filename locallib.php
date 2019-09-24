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
 * @package     report_elucidsitereport
 * @category    admin
 * @copyright   2019 wisdmlabs <support@wisdmlabs.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . "/cohort/lib.php");
require_once($CFG->dirroot . "/report/elucidsitereport/classes/constants.php");

/**
 * Get Export Link to export data from blocks and individual page
 * @param  [string] $url Url prifix to get export link
 * @param  [stdClass] $data Object for additional data
 * @return [string] moodle link detail
 */
function get_block_exportlinks($url, $data) {
    $links = new stdClass();

    /* Course Progress Filter Id*/
    if (isset($data->firstcourseid)) {
        $cpfilter = $data->firstcourseid;
    } else {
        $cpfilter = false;
    }

    if (isset($data->firstlpid)) {
        $lpfilter = $data->firstlpid;
    } else {
        $lpfilter = false;
    }

    $links->blockactiveusers = get_exportlinks($url, "block", "activeusers", "weekly");
    $links->blockactivecourses = get_exportlinks($url, "block", "activecourses");
    $links->blockcourseprogress = get_exportlinks($url, "block", "courseprogress", $cpfilter);
    $links->blockcertificates = get_exportlinks($url, "block", "certificates");
    $links->blockf2fsessions = get_exportlinks($url, "block", "f2fsession");
    $links->blocklpstats = get_exportlinks($url, "block", "lpstats", $lpfilter);
    return $links;
}

/**
 * Get Export Link to export link array
 * @param  [string] $prifix Url url for export link
 * @param  [string] $region Region for export
 * @param  [string] $blockname Block to export
 * @param  [string] $filter Filter for data to export
 * @param  [string] $action Action of a page report
 * @return [array] Array of export link
 */
function get_exportlinks($url, $region, $blockname, $filter = false, $cohortid = false, $action = false) {
    $out = new stdClass();

    $params = array(
        "region" => $region,
        "blockname" => $blockname
    );

    if ($action !== false) {
        $params["action"] = $action;
    }

    if ($filter !== false) {
        $params["filter"] = $filter;
    }

    if ($cohortid !== false) {
        $params["cohortid"] = $cohortid;
    }

    $out->export = get_exportlink_array($url, $blockname, $params, $region);
    return $out;
}

/**
 * Get Export Link to export link array
 * @param  [string] $url Url for export link
 * @param  [string] $blockname Block to export
 * @param  [array] $params Prameters for link
 * @return [array] Array of export link
 */
function get_exportlink_array($url, $blockname, $params, $region) {
    $context = context_system::instance();

    return array(
        array(
            "name" => get_string("csv", "report_elucidsitereport"),
            "icon" => "file-o",
            "link" => (new moodle_url($url, array_merge(array("format" => "csv"), $params)))->out(),
            "action" => 'csv',
            "blockname" => $blockname,
            "region" => $region,
            "contextid" => $context->id
        ),
        array(
            "name" => get_string("excel", "report_elucidsitereport"),
            "icon" => "file-excel-o",
            "link" => (new moodle_url($url, array_merge(array("format" => "excel"), $params)))->out(),
            "action" => 'excel',
            "blockname" => $blockname,
            "region" => $region,
            "contextid" => $context->id
        ),
        array(
            "name" => get_string("pdf", "report_elucidsitereport"),
            "icon" => "file-pdf-o",
            "link" => (new moodle_url($url, array_merge(array("format" => "pdf"), $params)))->out(),
            "action" => 'pdf',
            "blockname" => $blockname,
            "region" => $region,
            "contextid" => $context->id
        ),
        array(
            "name" => get_string("email", "report_elucidsitereport"),
            "icon" => "envelope-o",
            "link" => (new moodle_url($url, array_merge(array("format" => "email"), $params)))->out(),
            "action" => 'email',
            "blockname" => $blockname,
            "region" => $region,
            "contextid" => $context->id
        ),
        array(
            "name" => get_string("emailscheduled", "report_elucidsitereport"),
            "icon" => "envelope-o",
            "link" => (new moodle_url($url, array_merge(array("format" => "emailscheduled"), $params)))->out(),
            "action" => 'emailscheduled',
            "blockname" => $blockname,
            "region" => $region,
            "contextid" => $context->id,
            "sesskey" => sesskey()
        )/*,
        array(
            "name" => get_string("copy", "report_elucidsitereport"),
            "icon" => "copy",
            "link" => new moodle_url($url, array_merge(array("format" => "copy"), $params)),
            "action" => 'copy'
        )*/
    );
}

/**
 * Get Users Filter for filer the data
 * @param [boolean] $customfields Custom Fields
 * @param [boolean] $cohortfilter Cohort Filters
 * @param [boolean] $rangeselector Range Selector
 * @return [array] Array of filters
 */
function get_userfilters($customfields, $cohortfilter, $rangeselector) {
    $userfilters = new stdClass();

    if ($cohortfilter) {
        $userfilters->cohortfilter = get_cohort_filter();
    }

    if ($rangeselector) {
        $userfilters->rangeselector = true;
    }

    return $userfilters;
}

/**
 * Get Cohort filter Filter for filer the data
 * @return [array] Array of Cohort filters
 */
function get_cohort_filter() {
    global $DB;

    $syscontext = context_system::instance();
    $cohorts = cohort_get_cohorts($syscontext->id)["cohorts"];
    $categories = $DB->get_records_select('course_categories', 'id');

    foreach($categories as $category) {
        $catcontext = context_coursecat::instance($category->id);
        $cohorts = array_merge($cohorts, cohort_get_cohorts($catcontext->id)["cohorts"]);
    }

    if (empty($cohorts)) {
        return false;
    }
    
    $cohortfilter = new stdClass();
    $cohortfilter->text = "Cohort";
    $cohortfilter->values = $cohorts;
    return $cohortfilter;
}

/**
 * Create back button for each individual page
 * @param [object] $backurl Moodle Url Object
 * @return [string] Html string for back button
 */
function create_back_button($backurl) {
    $html = html_writer::div(
        html_writer::link(
            $backurl,
            html_writer::tag(
                "i", "", array(
                    "class" => "icon fa fa-arrow-left",
                    "aria-hidden" => "true"
                )
            ),
            array(
                "class" => "btn btn-sm btn-default",
                "data-toggle" => "tooltip",
                "data-original-title" => get_string("back"),
                "data-placement" => "bottom"
            )
        ),
        "mb-10", array ("id" => "wdm_reportback_button")
    );
    return $html;
}

/**
 * If the moodle has plugin then return true
 * @param [string] $plugintype Plugin Type
 * @param [string] $pluginname Plugin Name
 * @return [boolean] True|False based on plugin exist
 */
function has_plugin($plugintype, $puginname) {
    $plugins = core_plugin_manager::instance()->get_plugins_of_type($plugintype);

    if (array_key_exists($puginname, $plugins)) {
        return true;
    }

    return false;
}

/**
 * Get Schedule Email form
 * @param [string] Url for submiting form
 * @return [string] HTML String of schedule form
 */
function get_schedule_emailform($id, $formaction, $blockname, $region) {
    global $DB;

    // Default values for form
    $esrid = null;
    $esrname = '';
    $esremailenable = true;
    $esrrecepient = '';
    $esrsubject = '';
    $esrmessage = '';
    $esrduration = 0;
    $esrtime = 0;
    $esrlastrun = '';
    $esrnextrun = '';

    // Get data from table
    $table = "elucidsitereport_schedemails";
    $sql = "SELECT * FROM {elucidsitereport_schedemails}
        WHERE blockname = :blockname
        AND component = :component";
    $params = array(
        "blockname" => $blockname,
        "component" => $region
    );

    // If data exist then replace the data
    $rec = $DB->get_record_sql($sql, $params);
    if ($rec && $emaildata = json_decode($rec->emaildata)) {
        if (is_array($emaildata) && isset($emaildata[$id])) {
            $esrid = $id;
            $esrname = $emaildata[$id]->esrname;
            $esremailenable = $emaildata[$id]->esremailenable;
            $esrrecepient = $emaildata[$id]->esrrecepient; 
            $esrsubject = $emaildata[$id]->esrsubject;
            $esrmessage = $emaildata[$id]->esrmessage;
            $esrduration = $emaildata[$id]->esrduration; 
            $esrtime = $emaildata[$id]->esrtime;
            $esrlastrun = $emaildata[$id]->esrlastrun;
            $esrnextrun = $emaildata[$id]->esrnextrun;
            $reportparams = $emaildata[$id]->reportparams;
        }
    }

    /*
     * Generate Modal for email schedule
     */
    // Form Start
    $out = html_writer::start_tag("form", array(
        "action" => $formaction
    ));

    $out .= get_email_schedule_header($esremailenable, $esrduration, $esrtime);

    // Start Body Div
    $out .= html_writer::start_div("w-full my-10");

    // Name of scheduled email
    $out .= html_writer::start_div("mb-5");
    $out .= html_writer::tag("i", "", array(
        "class" => "icon fa fa-calendar-o my-auto",
        "aria-hidden" => "true"
    ));
    $out .= html_writer::span("Name");
    $out .= html_writer::end_div();
    $out .= html_writer::start_tag("input", array(
        "type" => "text",
        "value" => $esrname,
        "name" => "esr-name",
        "class" =>"w-full mb-10"
    ));

    // Recepient Input Text
    $out .= html_writer::start_div("mb-5");
    $out .= html_writer::tag("i", "", array(
        "class" => "icon fa fa-user my-auto",
        "aria-hidden" => "true"
    ));
    $out .= html_writer::span("Recepient");
    $out .= html_writer::end_div();
    $out .= html_writer::start_tag("input", array(
        "type" => "text",
        "value" => $esrrecepient,
        "name" => "esr-recepient",
        "class" =>"w-full mb-10"
    ));

    // Subject Input Text
    $out .= html_writer::start_div("mb-5");
    $out .= html_writer::span("Subject");
    $out .= html_writer::end_div();
    $out .= html_writer::start_tag("input", array(
        "type" => "text",
        "value" => $esrsubject,
        "name" => "esr-subject",
        "class" =>"w-full mb-10"
    ));

    // Message box for emails
    $out .= html_writer::start_div("mb-5");
    $out .= html_writer::span("Message");
    $out .= html_writer::end_div();
    $out .= html_writer::tag("textarea", $esrmessage, array(
        "value" => $esrmessage,
        "name" => "esr-message",
        "class" =>"form-control w-full mb-10",
        "rows" => "4"
    ));

    // Error message box
    $out .= html_writer::div("", "esr-form-error");
    
    // Hidden inputs
    $out .= html_writer::tag("input", "", array(
        "value" => $esrduration,
        "type" => "text",
        "id" => "esr-sendduration",
        "name" => "esr-duration",
        "class" =>"d-none"
    ));

    $out .= html_writer::tag("input", "", array(
        "value" => $esrtime,
        "type" => "text",
        "id" => "esr-sendtime",
        "name" => "esr-time",
        "class" =>"d-none"
    ));

    $out .= html_writer::tag("input", "", array(
        "value" => $esrid,
        "type" => "text",
        "id" => "esr-id",
        "name" => "esr-id",
        "class" =>"d-none"
    ));

    // End Body Div
    $out .= html_writer::end_div();

    // End Main Div
    $out .= html_writer::end_div();

    $out .= html_writer::start_div("modal-footer px-0 py-5", array(
        "data-region" => "footer"
    ));
    $out .= html_writer::tag("button",
        get_string("schedule", "report_elucidsitereport"),
        array(
            "class" => "btn btn-primary",
            "type" => "button",
            "data-action" => "save"
        )
    );

    $out .= html_writer::tag("button",
        get_string("reset", "report_elucidsitereport"),
        array(
            "class" => "btn btn-secondary",
            "type" => "button",
            "data-action" => "cancel"
        )
    );
    $out .= html_writer::end_div();

    // End of Form
    $out .= html_writer::end_tag("form");

    return $out;
}

/**
 * Get email schedule header
 * @param  [type] $emailenable [description]
 * @param  [type] $duration    [description]
 * @param  [type] $time        [description]
 * @return [type]              [description]
 */
function get_email_schedule_header($emailenable, $duration, $time) {
    // Select which sropdown has to be select
    $daily = $weekly = $monthly = false;
    $dayofweek = $timeofday = $dayofmonth = 0;

   // Set the time value for weeks day
    switch($duration) {
        case ESR_DAILY_EMAIL:
            if ($time <= 3) {
                $timeofday = $time;
            }
            $daily = true;
            break;
        case ESR_WEEKLY_EMAIL:
            if ($time <= 6) {
                $dayofweek = $time;
            }
            $weekly = true;
            break;
        case ESR_MONTHLY_EMAIL:
            if ($time <= 3) {
                $dayofmonth = $time;
            }
            $monthly = true;
            break;
        default:
            $daily = true;
            $duration = 0; // Set Default value
    }

    // Start Main Div
    $out = html_writer::start_div();

    // Start Header Div
    $out .= html_writer::start_div("d-flex");

    // Duration Count Dropdown Start
    $out .= html_writer::tag("i", "", array(
        "class" => "icon fa fa-calendar my-auto",
        "aria-hidden" => "true"
    ));
    // Header Duration Count
    $out .= html_writer::span(get_string("emailthisreport", "report_elucidsitereport"), "my-auto mx-5");

    // Email Shcedule Duration
    $out .= html_writer::span(get_duration_dropdown($duration), "duration-dropdown dropdown");


    $out .= html_writer::span(get_string("onevery", "report_elucidsitereport"), "my-auto mx-5");

    // Weeks Dropdown
    $out .= html_writer::span(get_weeks_dropdown($dayofweek, $weekly), "weekly-dropdown dropdown");
    // Times Dropdown
    $out .= html_writer::span(get_times_dropdown($timeofday, $daily), "daily-dropdown dropdown");
    // Monthly Dropdown
    $out .= html_writer::span(get_monthly_dropdown($dayofmonth, $monthly), "monthly-dropdown dropdown");
    
    $out .= create_toggle_switch_for_emails("", $emailenable, "", "", "ml-auto");
    // End Header Div 
    $out .= html_writer::end_div();

    return $out;
}

/**
 * Get Times dropdown
 * @param  integer $time [description]
 * @return [type]        [description]
 */
function get_times_dropdown($time = 0, $active = false) {
    $dnone = '';
    if (!$active) {
        $dnone = "display: none;";
    }

    $timesdropdown = html_writer::tag("button", get_string("times_" . $time, "report_elucidsitereport"), array(
        "data-value" => $time,
        "type" => "button",
        "class" => "btn btn-default btn-sm dropdown-toggle mx-5",
        "id" => "timesdropdown",
        "data-toggle" => "dropdown",
        "data-managedby" => ESR_DAILY_EMAIL,
        "aria-expanded" => "false",
        "style" => $dnone
    ));

    $timesdropdown .= html_writer::start_div("dropdown-menu", array(
        "aria-labelledby" => "timesdropdown",
        "role" => "menu"
    ));

    // Get all 7 weeks
    for ($i = 0; $i <= 3; $i++) {
        $str = get_string("times_". $i, "report_elucidsitereport");
        $timesdropdown .= '<a class="dropdown-item" href="javascript:void(0)"
        data-value="'. $i .'" role="menuitem">'. $str .'</a>';
    }

    $timesdropdown .= html_writer::end_div();

    return $timesdropdown;
}

/**
 * Get weeks dropdown
 * @param  [type] $time [description]
 * @return [type]      [description]
 */
function get_weeks_dropdown($time = 0, $active = false) {
    $dnone = '';
    if (!$active) {
        $dnone = "display: none;";
    }

    $weeksdropdown = html_writer::tag("button", get_string("week_" . $time, "report_elucidsitereport"), array(
        "data-value" => $time,
        "type" => "button",
        "class" => "btn btn-default btn-sm dropdown-toggle mx-5",
        "id" => "weeksdropdown",
        "data-toggle" => "dropdown",
        "data-managedby" => ESR_WEEKLY_EMAIL,
        "aria-expanded" => "false",
        "style" => $dnone
    ));

    $weeksdropdown .= html_writer::start_div("dropdown-menu", array(
        "aria-labelledby" => "weeksdropdown",
        "role" => "menu"
    ));

    // Get all 7 weeks
    for ($i = 0; $i <= 6; $i++) {
        $str = get_string("week_". $i, "report_elucidsitereport");
        $weeksdropdown .= '<a class="dropdown-item" href="javascript:void(0)"
        data-value="'. $i .'" role="menuitem">'. $str .'</a>';
    }

    $weeksdropdown .= html_writer::end_div();

    return $weeksdropdown;
}

/**
 * Get quaterly dropdown
 * @param  [int] $time Which quater is selected
 * @return [string] HTML string for quaterly dropdown
 */
function get_monthly_dropdown($time = 0, $active = false) {
    $dnone = '';
    if (!$active) {
        $dnone = "display: none;";
    }

    $monthlydropdown = html_writer::tag("button", get_string("monthly_" . $time, "report_elucidsitereport"), array(
        "data-value" => $time,
        "type" => "button",
        "class" => "btn btn-default btn-sm dropdown-toggle mx-5",
        "id" => "monthlydropdown",
        "data-toggle" => "dropdown",
        "data-managedby" => ESR_MONTHLY_EMAIL,
        "aria-expanded" => "false",
        "style" => $dnone
    ));

    $monthlydropdown .= html_writer::start_div("dropdown-menu", array(
        "aria-labelledby" => "monthlydropdown",
        "role" => "menu"
    ));

    // Get all 7 weeks
    for ($i = 0; $i <= 2; $i++) {
        $str = get_string("monthly_". $i, "report_elucidsitereport");
        $monthlydropdown .= '<a class="dropdown-item" href="javascript:void(0)"
        data-value="'. $i .'" role="menuitem">'. $str .'</a>';
    }

    $monthlydropdown .= html_writer::end_div();

    return $monthlydropdown;
}

/**
 * Get duration dropdown
 * @param  integer $duration [description]
 * @return [type]            [description]
 */
function get_duration_dropdown($duration = 0) {
    // Create count dropdown 
    $durationdropdown = html_writer::tag("button", get_string("duration_" .$duration, "report_elucidsitereport"), array(
        "data-value" => $duration,
        "type" => "button",
        "class" => "btn btn-default btn-sm dropdown-toggle",
        "id" => "durationcount",
        "data-toggle" => "dropdown",
        "aria-expanded" => "false"
    ));

    $durationdropdown .= html_writer::start_div("dropdown-menu", array(
        "aria-labelledby" => "durationcount",
        "role" => "menu"
    ));

    for ($i = 0; $i <= 2; $i++) {
        $durationdropdown .= '<a class="dropdown-item" href="javascript:void(0)"
        data-value="'. $i .'" role="menuitem">'. get_string("duration_" .$i, "report_elucidsitereport") .'</a>';
    }

    $durationdropdown .= html_writer::end_div();

    return $durationdropdown;
}

/**
 * Get Scheduled email list
 * @return [type] [description]
 */
function get_schedule_emaillist() {
    global $DB;

    $emails = array();
    $rec = $DB->get_records('elucidsitereport_schedemails');
    foreach($rec as $key => $val) {
        // If it dosent have email data
        if (!$emaildata = json_decode($val->emaildata)) {
            continue;
        }

        // If dta is not an array
        if (!is_array($emaildata)) {
            continue;
        }

        // If everythings is ok then
        foreach($emaildata as $key => $emailinfo) {
            $data = array();
            $data["esrselect"] = create_toggle_switch_for_emails($key, $emailinfo->esremailenable, $val->blockname, $val->component);
            $data["esrname"] = $emailinfo->esrname;
            $data["esrnextrun"] = date("d M y", $emailinfo->esrnextrun);
            $data["esrfrequency"] = $emailinfo->esrfrequency;
            $data["esrcomponent"] = $val->blockname;
            $data["esrmanage"] = create_manage_icons_for_emaillist(
                $val->blockname,
                $val->component,
                $emailinfo->esremailenable
            );
            $emails = $data;
        }
    }
    return $emails;
}

/**
 * Create select icon for email list
 * @param  [bolean] $select True (If selected)
 * @return [string] Html string to render select
 */
function carete_select_icons_for_emaillist($select) {
    $selectparam = array(
        "class" => "checkbox-custom checkbox-primary",
        "type" => "checkbox"
    );

    if ($select) {
        $selectparam["checked"] = "checked";
    }

    $out = html_writer::start_span("checkbox-custom checkbox-primary");
    $out .= html_writer::start_tag("input", $selectparam);
    $out .= html_writer::tag("label", "");
    $out .= html_writer::end_span();
    return $out;
}

/**
 * Create mange icons to manage email list
 * @return [string] Html manage icon string
 */
function create_manage_icons_for_emaillist($id, $blockname, $region) {
    $manage = html_writer::start_span("row esr-manage-scheduled-emails m-0 p-0 justify-content-center");
    $manage .= html_writer::link('javascript:void(0)',
        '<i class="fa fa-cog mx-1"></i>',
        array(
            "class" => "esr-email-sched-setting",
            "data-blockname" => $blockname,
            "data-region" => $region,
            "data-id" => $id,
            "data-sesskey" => sesskey()
        )
    );
    $manage .= html_writer::link('javascript:void(0)',
        '<i class="fa fa-trash mx-1 text-danger"></i>',
        array(
            "class" => "esr-email-sched-delete",
            "data-blockname" => $blockname,
            "data-region" => $region,
            "data-id" => $id,
            "data-sesskey" => sesskey()
        )
    );
    
    return $manage;
}

/**
 * Create toggle switch to enable disable emails
 * @return [string] Html string for toggle switch
 */
function create_toggle_switch_for_emails($id, $emailenable, $blockname, $region, $customclass = '') {
    $toggleid = "esr-toggle-" . $blockname . "-" . $region . "-" . $id;
    $switchparams = array(
        "id" => $toggleid,
        "type" => "checkbox",
        "value" => true,
        "name" => "esr-emailenable",
        "data-sesskey" => sesskey(),
        "data-blockname" => $blockname,
        "data-region" => $region,
        "data-id" => $id,
    );

    if ($emailenable) {
        $switchparams["checked"] = "checked";
    }

    // Toggle Switch For Enable and Disable Start
    $out = html_writer::start_div("my-auto px-5 ". $customclass);
    $out .= html_writer::label(
        html_writer::tag("input", "", $switchparams).
        html_writer::div(
            html_writer::div("", "switch-background bg-primary").
            html_writer::div("", "switch-lever bg-primary"),
            "switch-container esr-enable-disable-form"
        ), $toggleid, true,
        array(
            "class" => "esr-switch",
            "title" => "Enable/Disable email"
        )
    );
    $out .= html_writer::end_div();
    // Toggle Switch For Enable and Disable End
    
    return $out;
}

/**
 * Get email schedule duration time
 * @param  [int] $duration Duration
 * @param  [int] $time Time
 * @return [int] Run time
 */
function get_email_schedule_next_run($duration, $time) {
    $timenow = time();
    $frequency = '';
    // According to duation and time calculate the next scheduled time
    switch($duration) {
        case ESR_WEEKLY_EMAIL:
            $day = get_string("week_" . $time, "report_elucidsitereport");
            $weekstr = 'next ' . $day;

            // Calculate time
            $schedtime = strtotime($weekstr);
            $frequency = get_string("everyweeks", "report_elucidsitereport", array("day" => $day));
            break;
        case ESR_MONTHLY_EMAIL:
            // Get last date of the month
            $lastdate = date("d", strtotime('last day of this month'));
            $day = $time;

            // If it is greater then then assign as time
            if ($time > (int) $lastdate) {
                $time = (int) $lastdate;
            }

            // Get month string
            $monthstr = date($time . ' M Y', $timenow);

            // Calculate time
            $schedtime = strtotime($monthstr);
            $frequency = get_string("everymonths", "report_elucidsitereport",array("time" => $day));

            // If time has passed the add one month
            if ($timenow > $schedtime) {
                $schedtime = $schedtime + ONEMONTH;
            }
            break;

        default: // Default daily emails
            $dailystr = date("d M Y", $timenow);

            // Calculate time
            $schedtime = strtotime($dailystr) + $time * 60 * 60;

            if ($time < 10) {
                $day = get_string("time0".$time, "report_elucidsitereport");
            } else {
                $day = get_string("time".$time, "report_elucidsitereport");
            }

            // Get frequency string
            $frequency = get_string("everydays", "report_elucidsitereport",array("time" => $day));

            // If scheduledtime has been passed then add one day
            if ($timenow > $schedtime) {
                $schedtime = $schedtime + ONEDAY;
            }
    }

    // Return scheduled time
    return array($frequency, $schedtime);
}