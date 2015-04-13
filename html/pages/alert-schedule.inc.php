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

$pagetitle[] = "Alert Schedule";

?>

<div class="modal fade bs-example-modal-sm" id="schedule-maintenance" tabindex="-1" role="dialog" aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="Create">Alert Rules</h5>
            </div>
            <div class="modal-body">
                <form method="post" role="form" id="sched-form" class="form-horizontal schedule-maintenance-form">
                    <div class="row">
                        <div class="col-md-12">
                            <span id="response"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for='device' class='col-sm-4 control-label'>Maintenance for? </label>
                        <div class="col-sm-8">
                            <select id='device' name='device' class='form-control'>
                                <option value="-1">All devices</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="start" class="col-sm-6 control-label">Start: </label>
                        <label for="end" class="col-sm-6 control-label">End: </label>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-6">
                            <input type="text" class="form-control date" id="start" name="start" value="<?php echo date('Y-m-d H:i'); ?>" data-date-format="YYYY-MM-DD HH:mm">
                        </div>
                        <div class="col-sm-6">
                            <input type="text" class="form-control date" id="end" name="end" value="<?php echo date('Y-m-d H:i',strtotime('+1 hour')); ?>" data-date-format="YYYY-MM-DD HH:mm">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-3">
                            <button class="btn btn-default btn-sm" type="submit" name="sched-submit" id="sched-submit" value="save">Save Rule</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="panel panel-default panel-condensed">
    <div class="table-responsive">
        <table id="alert-schedule" class="table table-condensed">
            <thead>
                <tr>
                    <th data-column-id="hostname" data-order="asc">Hostname</th>
                    <th data-column-id="start">Start</th>
                    <th data-column-id="end">End</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<script>

var grid = $("#alert-schedule").bootgrid({
    ajax: true,
    templates: {
        header: "<div id=\"{{ctx.id}}\" class=\"{{css.header}}\"><div class=\"row\">"+
                "<div class=\"col-sm-8 actionBar\"><span class=\"pull-left\">"+
                "<button type=\"button\" class=\"btn btn-primary btn-sm\" data-toggle=\"modal\" data-target=\"#schedule-maintenance\">Schedule maintenance</button>"+
                "</span></div>"+
                "<div class=\"col-sm-4 actionBar\"><p class=\"{{css.search}}\"></p><p class=\"{{css.actions}}\"></p></div></div></div>"
    },
    rowCount: [50,100,250,-1],
    post: function ()
    {
        return {
            id: "alert-schedule",
        };
    },
    url: "/ajax_table.php"
});

$('#sched-submit').click('', function(e) {
    e.preventDefault();
    alert($('form.schedule-maintenance-form').serialize());
});

$(function () {
    $("#start").datetimepicker();
    $("#end").datetimepicker();
    $("#start").on("dp.change", function (e) {
        $("#end").data("DateTimePicker").minDate(e.value);
    });
    $("#end").on("dp.change", function (e) {
        $("#start").data("DateTimePicker").maxDate(e.value);
    });
});

</script>
