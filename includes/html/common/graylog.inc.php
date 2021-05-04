<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

use App\Models\Device;

if (empty($results_limit)) {
    $results_limit = 25;
}
$tmp_output = '

<div class="table-responsive">
    <table id="graylog" class="table table-hover table-condensed graylog">
        <thead>
            <tr>
            <th data-column-id="severity" data-sortable="false"></th>
            <th data-column-id="timestamp" data-formatter="browserTime">Timestamp</th>
            <th data-column-id="level">Level</th>
            <th data-column-id="source">Source</th>
            <th data-column-id="message" data-sortable="false">Message</th>
            <th data-column-id="facility">Facility</th>
            </tr>
        </thead>
    </table>
</div>

<script>

searchbar = "<div id=\"{{ctx.id}}\" class=\"{{css.header}}\"><div class=\"row\">"+
            "<div class=\"col-sm-8\"><form method=\"post\" action=\"\" class=\"form-inline\">"+
            "' . addslashes(csrf_field()) . '" +
            "Filter: "+
';

$tmp_output .= '"<div class=\"form-group\"><select name=\"stream\" id=\"stream\" class=\"form-control\" data-placeholder=\"All Messages\">"+';
if ($vars['stream']) {
    $tmp_output .= '"<option value=\"' . \LibreNMS\Util\Clean::html($vars['stream'], []) . '\">' . \LibreNMS\Util\Clean::html($vars['stream'], []) . '</option>" +';
    $filter_device = $device->device_id;
}
$tmp_output .= '"</select>&nbsp;</div>"+';

if (! empty($filter_device)) {
    $tmp_output .= '
            "<input type=\"hidden\" name=\"device\" id=\"device\" value=\"' . $filter_device . '\">"+
';
} else {
    $tmp_output .= '
            "<div class=\"form-group\"><select name=\"device\" id=\"device\" class=\"form-control\" data-placeholder=\"All Devices\">"+

';
    if ($vars['device'] && $device = Device::find($vars['device'])) {
        $tmp_output .= '"<option value=\"' . $device->device_id . '\">' . $device->displayName() . '</option>" +';
        $filter_device = $device->device_id;
    }

    $tmp_output .= '
                "</select>&nbsp;</div>"+
';
}

if (\LibreNMS\Config::has('graylog.timezone')) {
    $timezone = 'row.timestamp;';
} else {
    $timezone = 'moment.parseZone(row.timestamp).local().format("YYYY-MM-DD HH:MM:SS");';
}

$tmp_output .= '
                "<div class=\"form-group\">"+
                "<select name=\"loglevel\" id=\"loglevel\" class=\"form-control\">"+
                "<option value=\"\" disabled selected>Log Level</option>"+
                "<option value=\"0\">' . ('(0) ' . __('syslog.severity.0')) . '</option>"+
                "<option value=\"1\">' . ('(1) ' . __('syslog.severity.1')) . '</option>"+
                "<option value=\"2\">' . ('(2) ' . __('syslog.severity.2')) . '</option>"+
                "<option value=\"3\">' . ('(3) ' . __('syslog.severity.3')) . '</option>"+
                "<option value=\"4\">' . ('(4) ' . __('syslog.severity.4')) . '</option>"+
                "<option value=\"5\">' . ('(5) ' . __('syslog.severity.5')) . '</option>"+
                "<option value=\"6\">' . ('(6) ' . __('syslog.severity.6')) . '</option>"+
                "<option value=\"7\">' . ('(7) ' . __('syslog.severity.7')) . '</option>"+
                "</select>&nbsp;</div>"+
                "<div class=\"form-group\"><select name=\"range\" class=\"form-control\">"+
                "<option value=\"0\">Search all time</option>"+
                "<option value=\"300\">Search last 5 minutes</option>"+
                "<option value=\"900\">Search last 15 minutes</option>"+
                "<option value=\"1800\">Search last 30 minutes</option>"+
                "<option value=\"3600\">Search last 1 hour</option>"+
                "<option value=\"7200\">Search last 2 hours</option>"+
                "<option value=\"28800\">Search last 8 hours</option>"+
                "<option value=\"86400\">Search last 1 day</option>"+
                "<option value=\"172800\">Search last 2 days</option>"+
                "<option value=\"432000\">Search last 5 days</option>"+
                "<option value=\"604800\">Search last 7 days</option>"+
                "<option value=\"1209600\">Search last 14 days</option>"+
                "<option value=\"2592000\">Search last 30 days</option>"+
                "</select>&nbsp;</div>"+
                "<button type=\"submit\" class=\"btn btn-success\">Filter</button>&nbsp;"+
                "</form></div>"+
                "<div class=\"col-sm-4 actionBar\"><p class=\"{{css.search}}\"></p><p class=\"{{css.actions}}\"></p></div></div></div>";

    var graylog_grid = $("#graylog").bootgrid({
        ajax: true,
        rowCount: [' . $results_limit . ', 25,50,100,250,-1],
        formatters: {
            "browserTime": function(column, row) {
                return ' . $timezone . '
            }
        },
';

if (! isset($no_form) && $no_form !== true) {
    $tmp_output .= '
        templates: {
            header: searchbar
        },
    ';
}

$tmp_output .= '
        post: function ()
        {
            return {
                stream: "' . (isset($_POST['stream']) ? $_POST['stream'] : '') . '",
                device: "' . (isset($filter_device) ? $filter_device : '') . '",
                range: "' . (isset($_POST['range']) ? $_POST['range'] : '') . '",
                loglevel: "' . (isset($_POST['loglevel']) ? $_POST['loglevel'] : '') . '",
            };
        },
        url: "' . url('/ajax/table/graylog') . '",
    });

    init_select2("#stream", "graylog-streams", {}, "' . (isset($_POST['stream']) ? $_POST['stream'] : '') . '");
    init_select2("#device", "device", {limit: 100}, "' . (isset($filter_device) ? $filter_device : '') . '");
</script>

';

$common_output[] = $tmp_output;
