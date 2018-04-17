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
 * @link       http://librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

use LibreNMS\Authentication\Auth;

$no_refresh = true;
$param = array();

if ($vars['action'] == 'expunge' && Auth::user()->hasGlobalAdmin()) {
    dbQuery('TRUNCATE TABLE `syslog`');
    print_message('syslog truncated');
}

$pagetitle[] = 'Syslog';
?>
<div class="panel panel-default panel-condensed">
    <div class="panel-heading">
        <strong>Syslog</strong>
    </div>

    <?php
    require_once 'includes/common/syslog.inc.php';
    echo implode('', $common_output);
    ?>
</div>

<script>
    $('.actionBar').append(
        '<div class="pull-left">' +
        '<form method="post" action="" class="form-inline" role="form" id="result_form">' +
        '<div class="form-group">' +
        <?php
        if (!is_numeric($vars['device'])) {
        ?>
        '<select name="device" id="device" class="form-control input-sm">' +
        '<option value="">All Devices&nbsp;&nbsp;</option>' +
        <?php
        foreach (get_all_devices() as $data) {
            if (device_permitted($data['device_id'])) {
                echo "'<option value=\"" . $data['device_id'] . "\"";
                if ($data['device_id'] == $vars['device']) {
                    echo ' selected';
                }
                echo ">" . format_hostname($data) . "</option>' + ";
            }
        }
        ?>
        '</select>' +
        <?php
        } else {
            echo "'&nbsp;&nbsp;<input type=\"hidden\" name=\"device\" id=\"device\" value=\"" . $vars['device'] . "\">' + ";
        }
        ?>
        '</div>' +
        '&nbsp;&nbsp;<div class="form-group">' +
        '<select name="program" id="program" class="form-control input-sm">' +
        '<option value="">All Programs&nbsp;&nbsp;</option>' +
        <?php
        $sqlstatement = 'SELECT DISTINCT `program` FROM `syslog`';
        if (is_numeric($vars['device'])) {
            $sqlstatement = $sqlstatement . ' WHERE device_id=?';
            $param[] = $vars['device'];
        }
        $sqlstatement = $sqlstatement . ' ORDER BY `program`';
        foreach (dbFetchRows($sqlstatement, $param) as $data) {
            echo "'<option value=\"" . mres($data['program']) . "\"";
            if ($data['program'] == $vars['program']) {
                echo ' selected';
            }
            echo ">" . $data['program'] . "</option>' + ";
        }
        ?>
        '</select>' +
        '</div>' +
        '&nbsp;&nbsp;<div class="form-group">' +
        '<select name="priority" id="priority" class="form-control input-sm">' +
        '<option value="">All Priorities</option>' +
        <?php
        $sqlstatement = 'SELECT DISTINCT `priority` FROM `syslog`';
        if (is_numeric($vars['device'])) {
            $sqlstatement = $sqlstatement . ' WHERE device_id=?';
            $param[] = $vars['device'];
        }
        $sqlstatement = $sqlstatement . ' ORDER BY `level`';
        foreach (dbFetchRows($sqlstatement, $param) as $data) {
            echo "'<option value=\"" . mres($data['priority']) . "\"";
            if ($data['priority'] == $vars['priority']) {
                echo ' selected';
            }
            echo ">" . $data['priority'] . "</option>' + ";
        }
        ?>
        '</select>' +
        '</div>' +
        '&nbsp;&nbsp;<div class="form-group">' +
        '<input name="from" type="text" class="form-control input-sm" id="dtpickerfrom" maxlength="16" value="<?php echo $vars['from']; ?>" placeholder="From" data-date-format="YYYY-MM-DD HH:mm">' +
        '</div>' +
        '<div class="form-group">' +
        '&nbsp;&nbsp;<input name="to" type="text" class="form-control input-sm" id="dtpickerto" maxlength="16" value="<?php echo $vars['to']; ?>" placeholder="To" data-date-format="YYYY-MM-DD HH:mm">' +
        '</div>' +
        '&nbsp;&nbsp;<button type="submit" class="btn btn-default input-sm">Filter</button>' +
        '</form>' +
        '</div>' +
        '</div>' +
        '</div>' +
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
            }
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
            }
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
            $("#dtpickerto").data("DateTimePicker").maxDate('<?php echo date($config['dateformat']['byminute']); ?>');
        }
    });
</script>

