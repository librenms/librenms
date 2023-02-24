<?php
/*
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

use Carbon\Carbon;
use LibreNMS\Config;

$no_refresh = true;
$param = [];
$device_id = (int) $vars['device'];

$pagetitle[] = 'Outages';
?>
<div class="panel panel-default panel-condensed">
    <div class="panel-heading">
        <strong>Outages</strong>
    </div>

    <?php
    require_once 'includes/html/common/outages.inc.php';
    echo implode('', $common_output);
    ?>
</div>
<script>
    $('.actionBar').append(
        '<div class="pull-left">' +
        '<form method="post" action="" class="form-inline" role="form" id="result_form">' +
        '<?php echo csrf_field() ?>'+
        '<div class="form-group">' +
        <?php
        if (! isset($vars['fromdevice'])) {
            ?>
        '<select name="device" id="device" class="form-control">' +
        '<option value="">All Devices&nbsp;&nbsp;</option>' +
            <?php
            if ($device_id) {
                echo "'<option value=$device_id>" . format_hostname(device_by_id_cache($device_id)) . "</option>' +";
            } ?>
        '</select>' +
            <?php
        } else {
            echo "'&nbsp;&nbsp;<input type=\"hidden\" name=\"device\" id=\"device\" value=\"" . $device_id . "\">' + ";
        }
        ?>
        '</div>' +
        '&nbsp;&nbsp;<div class="form-group">' +
        '<input name="from" type="text" class="form-control" id="dtpickerfrom" maxlength="16" value="<?php echo $vars['from']; ?>" placeholder="From" data-date-format="YYYY-MM-DD HH:mm">' +
        '</div>' +
        '<div class="form-group">' +
        '&nbsp;&nbsp;<input name="to" type="text" class="form-control" id="dtpickerto" maxlength="16" value="<?php echo $vars['to']; ?>" placeholder="To" data-date-format="YYYY-MM-DD HH:mm">' +
        '</div>' +
        '&nbsp;&nbsp;<button type="submit" class="btn btn-default">Filter</button>' +
        '</form>' +
        '</div>'
    );

    $(function () {
        $("#dtpickerfrom").datetimepicker({
            icons: {
                time: 'fa fa-clock-o',
                date: 'fa fa-calendar',
                up: 'fa fa-chevron-up',
                down: 'fa fa-chevron-down',
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-calendar-check-o',
                clear: 'fa fa-trash-o',
                close: 'fa fa-close'
            },
            defaultDate: '<?php echo Carbon::now()->subMonth()->format(Config::get('dateformat.byminute', 'Y-m-d H:i')); ?>'
        });
        $("#dtpickerfrom").on("dp.change", function (e) {
            $("#dtpickerto").data("DateTimePicker").minDate(e.date);
        });
        $("#dtpickerto").datetimepicker({
            icons: {
                time: 'fa fa-clock-o',
                date: 'fa fa-calendar',
                up: 'fa fa-chevron-up',
                down: 'fa fa-chevron-down',
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-calendar-check-o',
                clear: 'fa fa-trash-o',
                close: 'fa fa-close'
            },
            defaultDate: '<?php echo Carbon::now()->format(Config::get('dateformat.byminute', 'Y-m-d H:i')); ?>'
        });
        $("#dtpickerto").on("dp.change", function (e) {
            $("#dtpickerfrom").data("DateTimePicker").maxDate(e.date);
        });
        if ($("#dtpickerfrom").val() != "") {
            $("#dtpickerto").data("DateTimePicker").minDate($("#dtpickerfrom").val());
        }
        if ($("#dtpickerto").val() != "") {
            $("#dtpickerfrom").data("DateTimePicker").maxDate($("#dtpickerto").val());
        } else {
            $("#dtpickerto").data("DateTimePicker").maxDate('<?php echo Carbon::now()->format(Config::get('dateformat.byminute', 'Y-m-d H:i')); ?>');
        }
    });

    <?php if (! isset($vars['fromdevice'])) { ?>
    $("#device").select2({
        theme: "bootstrap",
        dropdownAutoWidth : true,
        width: "auto",
        allowClear: true,
        placeholder: "All Devices",
        ajax: {
            url: '<?php echo url('/ajax/select/device'); ?>',
            delay: 200
        }
    })<?php echo $device_id ? ".val($device_id).trigger('change');" : ''; ?>;
    <?php } ?>
</script>

