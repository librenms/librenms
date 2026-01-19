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
    'Any State' => '',
    'Ok (recovered)' => 0,
    'Alert' => 1,
    //    'Acknowledged' => 2,
    'Worse' => 3,
    'Better' => 4,
    'Changed' => 5,
];

$alert_severities = [
    // alert_rules.status is enum('ok','warning','critical')
    'Critical' => 3,
    'Warning' => 2,
    'OK' => 1,
];

if (Auth::user()->hasGlobalAdmin()) {
    $admin_verbose_details = '<th data-column-id="verbose_details" data-sortable="false">Details</th>';
}

$device_id ??= (int) ($vars['device'] ?? 0);

$common_output[] = '<div class="panel panel-default panel-condensed">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-2">
                            <strong>Alert Log entries</strong>
                        </div>
                    </div>
                </div>
            ';

$device = DeviceCache::get($device_id);
$device_selected = json_encode($device->exists ? ['id' => $device->device_id, 'text' => $device->displayName()] : '');

$common_output[] = '
<div class="table-responsive">
    <table id="alertlog" class="table table-hover table-condensed table-striped" data-url="' .  route('table.alertlog') . '">
        <thead>
        <tr>
            <th data-column-id="status">State</th>
            <th data-column-id="time_logged" data-order="desc">Timestamp</th>
            <th data-column-id="details" data-sortable="false">&nbsp;</th>
            <th data-column-id="hostname">Device</th>
            <th data-column-id="alert_rule">Alert</th>
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
                <form method="post" action="" class="form-inline" role="form" id="alertlog-filter-form"> \
                ' . csrf_field() . ' \
            <input type=hidden name="hostname" id="hostname"> \
';

if (isset($vars['fromdevice']) && ! $vars['fromdevice']) {
    $common_output[] = '<div class="form-group"> \
                <select name="device_id" id="device_id" class="form-control input-sm" style="min-width: 175px;"></select> \
               </div> \
               ';
}

$common_output[] = '<div class="form-group"> \
               <select name="state" id="state" class="form-control input-sm"> \\';
foreach ($alert_states as $text => $value) {
    $selected = $value == ($_POST['state'] ?? '') ? ' selected' : '';
    $common_output[] = "<option value=\"" . htmlspecialchars((string) $value) . "\"$selected>$text</option> \\";
}
               $common_output[] = '</select> \
               </div> \
               <div class="form-group"> \
               <select name="severity[]" id="severity" class="form-control input-sm" multiple> \\';
foreach ($alert_severities as $text => $value) {
    $selected = in_array($value, $_POST['severity'] ?? []) == $value ? ' selected' : '';
    $common_output[] = "<option value=\"$value\"$selected>$text</option> \\";
}
$common_output[] = '</select> \
               </div> \
               <button id="filter" type="submit" class="btn btn-default input-sm">Filter</button> \
               </form></span></div> \
               <div class="col-sm-4 actionBar"><p class="{{css.search}}"></p><p class="{{css.actions}}"></p></div></div></div>\'
        },
        post: function () {
            return {
                device_id: $(\'#device_id\').val() || \'' . ($device_id ? (int) $device_id : '') . '\',
                state: $(\'#state\').val(),
                severity: $(\'#severity\').val() || []
            };
        }
    }).on("loaded.rs.jquery.bootgrid", function () {

        var results = $("div.infos").text().split(" ");
        low = results[1] - 1;
        high = results[3];
        max = high - low;
        search = $(\'.search-field\').val();

        grid.find(".incident-toggle").each(function () {
            $(this).parent().addClass(\'incident-toggle-td\');
        }).on("click", function (e) {
            var target = $(this).data("target");
            $(target).collapse(\'toggle\');
            $(this).toggleClass(\'fa-plus fa-minus\');
        });
        grid.find(".verbose-alert-details").on("click", function(e) {
            e.preventDefault();
            var alert_log_id = $(this).data(\'alert_log_id\');
            $(\'#alert_log_id\').val(alert_log_id);
            $("#alert_details_modal").modal(\'show\');
        });
        grid.find(".incident").each(function () {
            $(this).parent().addClass(\'col-lg-4 col-md-4 col-sm-4 col-xs-4\');
            if ($(this).parent().parent().find(".alert-status").hasClass(\'label-danger\')){
                $(this).parent().parent().find(".verbose-alert-details").fadeIn(0);
            }
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

    $("#severity").select2({
        placeholder: "Any Severity",
        width: "13.1em",
        maximumSelectionLength: 2,
        containerCssClass: "severity-select-box"
     });
    init_select2("#device_id", "device", {}, ' . $device_selected . ' , "All Devices");

    $("#alertlog-filter-form").on("submit", function (e) {
        e.preventDefault();
        grid.bootgrid("reload");
    });
</script>
<style>
.severity-select-box .select2-search--inline {
    display: none;
}
.severity-select-box .select2-search--inline:first-child {
    display: inline-block;
}
</style>
';
