<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Aldemir Akpinar <https://github.com/aldemira>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 * @package    LibreNMS
 * @subpackage webui
 * @link       http://librenms.org
 * @copyright  2018 Aldemir Akpinar
 * @author     Aldemir Akpinar <aldemir.akpinar@gmail.com>
 */


$pagetitle[] = 'Virtual Machines';
?>
<div class="table-responsive">
    <table id="vminfo" class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th data-column-id="deviceid" data-visible="false" data-css-class="deviceid">No</th>
                <th data-column-id="sysname" data-visible="false">Sysname</th>
                <th data-column-id="vmname" data-type="string">Server Name</th>
                <th data-column-id="powerstat" data-type="string" data-formatter="powerstatus">Power Status</th>
                <th data-column-id="physicalsrv" data-type="string" data-formatter="hostdev">Physical Server</th>
                <th data-column-id="os" data-type="string" data-searchable="false" data-formatter="osname">Operating System</th>
                <th data-column-id="memory" data-type="string" data-searchable="false" data-formatter="mem">Memory</th>
                <th data-column-id="cpu" data-type="string" data-formatter="cpu" data-searchable="false">CPU</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
var grid = $("#vminfo").bootgrid({
    rowCount: [50, 100, 250, -1],
    ajax: true,
    post: function() {
        return {
            type: "get-vmlist",
        };
    },
    url: "ajax_form.php",
    templates: {
        header: '<div id="{{ctx.id}}" class="{{css.header}}"> \
                    <div class="row"> \
                <div class="actionBar"><p class="{{css.search}}"></p><p class="{{css.actions}}"></p></div></div></div>'
    },
    formatters: {
        "osname": function(column, row) {
            if (row.os == 'E: tools not installed') {
                return 'Unknown (VMware tools not installed)';
            } else if (row.os == 'E: tools not running') {
                return 'Unknown (VMware tools not running)';
            } else if (row.os == '') {
                return 'Uknown';
            } else {
                return row.os;
            }
        },
        "powerstatus": function(column, row) {
            if (row.powerstat == "powered on") {
                var response =  '<span class="label label-success">ON</span>';
            } else if (row.powerstat == "powered off") {
                var response =  '<span class="label label-default">OFF</span>';
            }
            return response;
        },
        "mem": function(column, row) {
            if (row.memory >= 1024) {
                tmpNumber = row.memory / 1024;
                return tmpNumber.toFixed(2) + ' GB';
            } else {
                return row.memory + ' MB';
            }
        },
        "cpu": function(column, row) {
            return row.cpu + ' CPU';
        },
        "hostdev": function(column, row) {
            return '<a href="device/device='+row.deviceid+'/" class="list-device">'+row.physicalsrv+'</a><br />'+row.sysname;
        },
    },
});
</script>
