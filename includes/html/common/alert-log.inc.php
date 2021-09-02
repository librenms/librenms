<?php
/*
* This program is free software: you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation, either version 3 of the License, or (at your
* option) any later version.  Please see LICENSE.txt at the top level of
* the source code distribution for details.
*
* @package    LibreNMS
* @subpackage graphs
* @link       https://www.librenms.org
* @copyright  2017 LibreNMS
* @author     LibreNMS Contributors
*/

$param = [];

$pagetitle[] = 'Alert Log';

$alert_states = [
    // divined from librenms/alerts.php
    'Any' => -1,
    'Ok (recovered)' => 0,
    'Alert' => 1,
    //    'Acknowledged' => 2,
    'Worse' => 3,
    'Better' => 4,
];

$alert_severities = [
    // alert_rules.status is enum('ok','warning','critical')
    'Any' => '',
    'Ok, warning and critical' => 1,
    'Warning and critical' => 2,
    'Critical' => 3,
    'OK' => 4,
    'Warning' => 5,
];

if (Auth::user()->hasGlobalAdmin()) {
    $admin_verbose_details = '<th data-column-id="verbose_details" data-sortable="false">Details</th>';
}

$common_output[] = '<div class="panel panel-default panel-condensed">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-2">
                            <strong>Alert Log entries</strong>
                        </div>
                        <div class="col-md-2 col-md-offset-8">
                            <div class="pull-right pdf-export"></div>
                        </div>
                    </div>
                </div>
            ';

if (isset($_POST['device_id'])) {
    $selected_device = '<option value="' . (int) $_POST['device_id'] . '" selected="selected">';
    $selected_device .= htmlentities($_POST['hostname']) . '</option>';
} else {
    $selected_device = $device_id;
    $_POST['device_id'] = $device_id;
}
if (isset($_POST['state'])) {
    $selected_state = '<option value="' . $_POST['state'] . '" selected="selected">';
    $selected_state .= array_search((int) $_POST['state'], $alert_states) . '</option>';
} else {
    $selected_state = '';
    $_POST['state'] = -1;
}
if (isset($_POST['min_severity'])) {
    $selected_min_severity = '<option value="' . $_POST['min_severity'] . '" selected="selected">';
    $selected_min_severity .= array_search((int) $_POST['min_severity'], $alert_severities) . '</option>';
} else {
    $selected_min_severity = '';
    $_POST['min_severity'] = '';
}

$common_output[] = '
<div class="table-responsive">
    <table id="alertlog" class="table table-hover table-condensed table-striped">
        <thead>
        <tr>
            <th data-column-id="status" data-sortable="false">State</th>
            <th data-column-id="time_logged" data-order="desc">Timestamp</th>
            <th data-column-id="details" data-sortable="false">&nbsp;</th>
            <th data-column-id="hostname">Device</th>
            <th data-column-id="alert">Alert</th>
            <th data-column-id="severity">Severity</th>
            ' . $admin_verbose_details . '
        </tr>
        </thead>
    </table>
</div>
</div>

<script>

    var grid = $("#alertlog").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        templates: {
            header: \'<div id="{{ctx.id}}" class="{{css.header}}"><div class="row"> \
                <div class="col-sm-8 actionBar"><span class="pull-left"> \
                <form method="post" action="" class="form-inline" role="form" id="result_form"> \
                ' . csrf_field() . ' \
            <input type=hidden name="hostname" id="hostname"> \
';

if (isset($vars['fromdevice']) && ! $vars['fromdevice']) {
    $common_output[] = '<div class="form-group"> \
                <label> \
                <strong>Device&nbsp;</strong> \
                </label> \
                <select name="device_id" id="device_id" class="form-control input-sm" style="min-width: 175px;"> \
                ' . $selected_device . ' \
               </select> \
               </div> \
               ';
}

$common_output[] = '<div class="form-group"> \
               <label> \
               <strong>&nbsp;State&nbsp;</strong> \
               </label> \
               <select name="state" id="state" class="form-control input-sm"> \
                $common_output[] = ' . $selected_state . ' \
               <option value="-1">Any</option> \
               <option value="0">Ok (recovered)</option> \
               <option value="1">Alert</option> \
               <option value="3">Worse</option> \
               <option value="4">Better</option> \
               </select> \
               </div> \
               <div class="form-group"> \
               <label> \
               <strong>&nbsp;Severity&nbsp;</strong> \
               </label> \
               <select name="min_severity" id="min_severity" class="form-control input-sm"> \
                ' . $selected_min_severity . ' \
               <option value>Any</option> \
               <option value="3">Critical</option> \
               <option value="5">Warning</option> \
               <option value="4">Ok</option> \
               <option value="2">Warning and critical</option> \
               <option value="1">Ok, warning and critical</option> \
               </select> \
               </div> \
               <button type="submit" class="btn btn-default input-sm">Filter</button> \
               </form></span></div> \
               <div class="col-sm-4 actionBar"><p class="{{css.search}}"></p><p class="{{css.actions}}"></p></div></div></div>\'
        },
        post: function () {
            return {
                id: "alertlog",
                device_id: \'' . htmlspecialchars($_POST['device_id']) . '\',
                state: \'' . htmlspecialchars($_POST['state']) . '\',
                min_severity: \'' . htmlspecialchars($_POST['min_severity']) . '\'
            };
        },
        url: "ajax_table.php"
    }).on("loaded.rs.jquery.bootgrid", function () {

        var results = $("div.infos").text().split(" ");
        low = results[1] - 1;
        high = results[3];
        max = high - low;
        search = $(\'.search-field\').val();

        $(".pdf-export").html("<a href=\'pdf.php?report=alert-log&device_id=' . $_POST['device_id'] . '&string=" + search + "&results=" + max + "&start=" + low + "\'><i class=\'fa fa-heartbeat fa-lg icon-theme\' aria-hidden=\'true\'></i> Export to pdf</a>");

        grid.find(".incident-toggle").each(function () {
            $(this).parent().addClass(\'incident-toggle-td\');
        }).on("click", function (e) {
            var target = $(this).data("target");
            $(target).collapse(\'toggle\');
            $(this).toggleClass(\'fa-plus fa-minus\');
        });
        grid.find(".command-alert-details").on("click", function(e) {
            e.preventDefault();
            var alert_log_id = $(this).data(\'alert_log_id\');
            $(\'#alert_log_id\').val(alert_log_id);
            $("#alert_details_modal").modal(\'show\');
        });
        grid.find(".incident").each(function () {
            $(this).parent().addClass(\'col-lg-4 col-md-4 col-sm-4 col-xs-4\');
            $(this).parent().parent().on("mouseenter", function () {
                $(this).find(".incident-toggle").fadeIn(200);
                if ($(this).find(".alert-status").hasClass(\'label-danger\')){
                    $(this).find(".command-alert-details").fadeIn(200);
                }
            }).on("mouseleave", function () {
                $(this).find(".incident-toggle").fadeOut(200);
                if ($(this).find(".alert-status").hasClass(\'label-danger\')){
                    $(this).find(".command-alert-details").fadeOut(200);
                }
            }).on("click", "td:not(.incident-toggle-td)", function () {
                var target = $(this).parent().find(".incident-toggle").data("target");
                if ($(this).parent().find(".incident-toggle").hasClass(\'fa-plus\')) {
                    $(this).parent().find(".incident-toggle").toggleClass(\'fa-plus fa-minus\');
                    $(target).collapse(\'toggle\');
                }
            });
        });
    });

    $("#device_id").select2({
        allowClear: true,
        placeholder: "All Devices",
        ajax: {
            url: \'ajax_list.php\',
            delay: 250,
            data: function (params) {
                return {
                    type: \'devices\',
                    search: params.term,
                    limit: 8,
                    page: params.page || 1
                };
            }
        }
    }).on(\'select2:select\', function (e) {
        $(\'#hostname\').val(e.params.data.text);
    });
</script>
';
