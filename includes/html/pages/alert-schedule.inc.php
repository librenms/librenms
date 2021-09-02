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

$pagetitle[] = 'Alert Schedule';
$no_refresh = true;
if (Auth::user()->hasGlobalAdmin()) {
    include_once 'includes/html/modal/alert_schedule.inc.php';
    include_once 'includes/html/modal/remove_alert_schedule.inc.php'; ?>

<div class="row">
    <div class="col-sm-12">
        <span id="message"></span>
    </div>
</div>

<div class="panel panel-default panel-condensed">
    <div class="table-responsive">
        <table id="alert-schedule" class="table table-condensed">
            <thead>
                <tr>
                    <th data-column-id="title">Title</th>
                    <th data-column-id="recurring">Recurring</th>
                    <th data-column-id="start" data-order="desc">Start (no recurring)</th>
                    <th data-column-id="end">End (no recurring)</th>
                    <th data-column-id="start_recurring_dt">Start recurring dt</th>
                    <th data-column-id="end_recurring_dt">End recurring dt</th>
                    <th data-column-id="start_recurring_hr">Start recurring hr</th>
                    <th data-column-id="end_recurring_hr">End recurring hr</th>
                    <th data-column-id="recurring_day" data-sortable="false" data-searchable="false">Recurring on days</th>
                    <th data-column-id="actions" data-sortable="false" data-searchable="false" data-formatter="commands">Actions</th>
                    <th data-column-id="status" data-searchable="false" data-formatter="schedstatus">Status</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<script>

var grid = $("#alert-schedule").bootgrid({
    ajax: true,
    formatters: {
        "commands": function(column, row) {
            var response = "<button type=\"button\" class=\"btn btn-xs btn-primary command-edit\" data-toggle='modal' data-target='#schedule-maintenance' data-schedule_id=\"" + row.id + "\"><span class=\"fa fa-pencil\"></span></button> " +
                "<button type=\"button\" class=\"btn btn-xs btn-danger command-delete\" data-schedule_id=\"" + row.id + "\"><span class=\"fa fa-trash-o\"></span></button>";
            return response;
        },
        "schedstatus": function(column, row) {
            if (row.status == "1") {
                response = '<span class="label label-danger">Lapsed</span>';
            } else if (row.status == "2") {
                response = '<span class="label label-success">Current</span>';
            } else if (row.status == "0") {
                response = '<span class="label label-warning">Set</span>';
            }

            return response;
        }
    },
    templates: {
        header: "<div id=\"{{ctx.id}}\" class=\"{{css.header}}\"><div class=\"row\">"+
                "<div class=\"col-sm-4 actionBar\"><span class=\"pull-left\">"+
                "<button type=\"button\" class=\"btn btn-primary btn-sm\" data-toggle=\"modal\" data-target=\"#schedule-maintenance\">Schedule maintenance</button>"+
                "</span></div>"+
                "<div class=\"col-sm-8 actionBar\"><p class=\"{{css.search}}\"></p><p class=\"{{css.actions}}\"></p></div></div></div>"
    },
    rowCount: [50, 100, 250, -1],
    url: "ajax/table/alert-schedule"
}).on("loaded.rs.jquery.bootgrid", function() {
    /* Executes after data is loaded and rendered */
    grid.find(".command-edit").on("click", function(e) {
        $('#schedule_id').val($(this).data("schedule_id"));
        $("#schedule-maintenance").modal('show');
    }).end().find(".command-delete").on("click", function(e) {
        $('#del_schedule_id').val($(this).data("schedule_id"));
        $('#delete-maintenance').modal('show');
    });
});

</script>

    <?php
}//end if
