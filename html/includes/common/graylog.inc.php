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
 */

if (empty($results_limit)) {
    $results_limit = 25;
}
$tmp_output = '<h3>Graylog</h3>

<div class="table-responsive">
    <table id="graylog" class="table table-hover table-condensed graylog">
        <thead>
            <tr>
                <th data-column-id="timestamp">Timestamp</th>
                <th data-column-id="source">Source</th>
                <th data-column-id="message">Message</th>
                <th data-column-id="facility" data-visible="false">Facility</th>
                <th data-column-id="level" data-visible="false">Level</th>
            </tr>
        </thead>
    </table>
</div>

<script>

searchbar = "<div id=\"{{ctx.id}}\" class=\"{{css.header}}\"><div class=\"row\">"+
            "<div class=\"col-sm-8\"><form method=\"post\" action=\"\" class=\"form-inline\">"+
            "Filter: "+
';

if (!empty($filter_device)) {
    $tmp_output .= '
            "<input type=\"hidden\" name=\"hostname\" id=\"hostname\" value=\"'. $filter_device .'\">"+
';
} else {
    $tmp_output .= '
            "<div class=\"form-group\"><select name=\"hostname\" id=\"hostname\" class=\"form-control input-sm\">"+
            "<option value=\"\">All devices</option>"+
';

    if (is_admin() === true || is_read() === true) {
        $results = dbFetchRows("SELECT `hostname` FROM `devices` GROUP BY `hostname` ORDER BY `hostname`");
    } else {
        $results = dbFetchRows("SELECT `D`.`hostname` FROM `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` GROUP BY `hostname` ORDER BY `hostname`", array($_SESSION['user_id']));
    }

    foreach ($results as $data) {
        $tmp_output .= '"<option value=\"'.$data['hostname'].'\""+';
        if (isset($vars['hostname']) && $data['hostname'] == $vars['hostname']) {
            $tmp_output .= '"selected"+';
        }
        $tmp_output .= '">'.$data['hostname'].'</option>"+';
    }

    $tmp_output .= '
                "</select>&nbsp;</div>"+
';
}

if (empty($filter_device) && isset($_POST['hostname'])) {
    $filter_device = mres($_POST['hostname']);
}

$tmp_output .= '
                "<div class=\"form-group\"><select name=\"range\" class=\"form-group input-sm\">"+
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
                "<option value=\"0\">Search all time</option>"+
                "</select>&nbsp;</div>"+
                "<button type=\"submit\" class=\"btn btn-success btn-sm\">Filter</button>&nbsp;"+
                "</form></div>"+
                "<div class=\"col-sm-4 actionBar\"><p class=\"{{css.search}}\"></p><p class=\"{{css.actions}}\"></p></div></div></div>"

    var graylog_grid = $("#graylog").bootgrid({
        ajax: true,
        rowCount: ['. $results_limit .', 25,50,100,250,-1],
';

if (isset($no_form) && $no_form !== true) {
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
                id: "graylog",
                hostname: "' . (isset($filter_device) ? $filter_device : '') . '",
                range: "' . (isset($_POST['range']) ? mres($_POST['range']) : '')  . '"
            };
        },
        url: "ajax_table.php",
    });
</script>

';

$common_output[] = $tmp_output;
