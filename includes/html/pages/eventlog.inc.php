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

use App\Models\Device;

$no_refresh = true;
$param = [];
if ($device_id = (int) Request::get('device')) {
    $device = Device::find($device_id);
}

$pagetitle[] = 'Eventlog';
?>

<div class="panel panel-default panel-condensed">
    <div class="panel-heading">
        <strong>Eventlog</strong>
    </div>

    <?php
    require_once 'includes/html/common/eventlog.inc.php';
    echo implode('', $common_output);
    ?>
</div>

<script>
    $('.actionBar').append(
        '<div class="pull-left">' +
        '<form method="post" action="" class="form-inline" role="form" id="result_form">' +
        '<?php echo csrf_field() ?>' +
        <?php
        if (! isset($vars['fromdevice'])) {
            ?>
        '<div class="form-group">' +
        '<label><strong>Device&nbsp;&nbsp;</strong></label>' +
        '<select name="device" id="device" class="form-control">' +
        '<option value="">All Devices</option>' +
            <?php
            if ($device instanceof Device) {
                echo "'<option value=$device->device_id>" . $device->displayName() . "</option>' +";
            } ?>
        '</select>' +
        '</div>&nbsp;&nbsp;&nbsp;&nbsp;' +
            <?php
        } else {
            echo "'&nbsp;&nbsp;<input type=\"hidden\" name=\"device\" id=\"device\" value=\"" . $vars['device'] . "\">' + ";
        }
        ?>
        '<div class="form-group"><label><strong>Type&nbsp;&nbsp;</strong></label>' +
        '<select name="eventtype" id="eventtype" class="form-control input-sm">' +
        '<option value="">All types</option>' +
        <?php
        if ($type = Request::get('eventtype')) {
            $js_type = addcslashes(htmlentities($type), "'");
            echo "'<option value=\"$js_type\">$js_type</option>' +";
        }
        ?>
        '</select>' +
        '</div>&nbsp;&nbsp;' +
        '<button type="submit" class="btn btn-default">Filter</button>' +
        '</form>' +
        '</div>'
    );

    <?php if (! isset($vars['fromdevice'])) { ?>
    $("#device").select2({
        theme: 'bootstrap',
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

    $("#eventtype").select2({
        theme: 'bootstrap',
        dropdownAutoWidth : true,
        width: "auto",
        allowClear: true,
        placeholder: "All Types",
        ajax: {
            url: '<?php echo url('/ajax/select/eventlog'); ?>',
            delay: 200,
            data: function(params) {
                return {
                    field: "type",
                    device: $('#device').val(),
                    term: params.term,
                    page: params.page || 1
                }
            }
        }
    })<?php echo Request::get('eventtype') ? ".val('" . addcslashes(Request::get('eventtype'), "'") . "').trigger('change');" : ''; ?>;

</script>
