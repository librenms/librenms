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
 * @link       http://librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

#use App\Models\Device;

$tmp_output = '

<div class="table-responsive">
    <table id="graylog" class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
            <th data-column-id="severity"></th>
            <th data-column-id="timestamp">Timestamp</th>
            <th data-column-id="level">Level</th>
            <th data-column-id="message">Message</th>
            <th data-column-id="facility">Facility</th>
            </tr>
        </thead>
    </table>
</div>

<script>
';

$rowCount = \LibreNMS\Config::get('graylog.device-page.rowCount');
$maxLevel = \LibreNMS\Config::get('graylog.device-page.maxLevel');

$tmp_output .= '
    $.ajax({
        type: "post",
        data: {
            device: "' . (isset($filter_device) ? $filter_device : '') . '",
            '. ($rowCount? 'rowCount: '.$rowCount .',' : '') .'
            '. ($maxLevel? 'maxLevel: '.$maxLevel .',' : '') .'
        },
        url: "' . url('/ajax/table/graylog') . '",
        success: function(data){
            if (data.rowCount == 0) {
                $("#graylog-card").remove();
                return;
            }
            var html = "<tbody>";
            $("#graylog").append("<tbody></tbody>");
            $.each(data.rows, function(i,v){
                html = html + "<tr><td>"+v.severity+"</td><td>"+
                    v.timestamp+"</td><td>"+v.level+"</td><td>"+
                    v.message+"</td><td>"+v.facility+"</td></tr>";
            });
            html = html + "</tbody>";
            $("#graylog").append(html);
        }
    });
';

$tmp_output .= '
</script>

';

$common_output[] = $tmp_output;
