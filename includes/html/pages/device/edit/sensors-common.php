<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk>
 * Copyright (c) 2017 Tony Murray <https://github.com/murrant>
 * Copyright (c) 2018 TheGreatDoc <https://github.com/TheGreatDoc>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// FUA
echo "<h3>$title</h3>";
?>

<form class="form-inline">
<?php echo csrf_field() ?>
<table class="table table-striped table-condensed table-bordered">
  <tr>
    <th>Class</th>
    <th>Type</th>
    <th>Description</th>
    <th>Current</th>
    <th class="col-sm-1">High</th>
    <th class="col-sm-1">High warn</th>
    <th class="col-sm-1">Low warn</th>
    <th class="col-sm-1">Low</th>
    <th class="col-sm-2">Alerts</th>
    <th></th>
  </tr>
<?php
$rollback = [];
foreach (dbFetchRows("SELECT * FROM `$table` WHERE `device_id` = ? AND `sensor_deleted`='0' order by sensor_class, sensor_type, sensor_descr", [$device['device_id']]) as $sensor) {
    $rollback[] = [
        'sensor_id'        => $sensor['sensor_id'],
        'sensor_limit'     => $sensor['sensor_limit'],
        'sensor_limit_warn'     => $sensor['sensor_limit_warn'],
        'sensor_limit_low_warn' => $sensor['sensor_limit_low_warn'],
        'sensor_limit_low' => $sensor['sensor_limit_low'],
        'sensor_alert'     => $sensor['sensor_alert'],
    ];
    if ($sensor['sensor_alert'] == 1) {
        $alert_status = 'checked';
    } else {
        $alert_status = '';
    }

    if ($sensor['sensor_custom'] == 'No') {
        $custom = 'disabled';
    } else {
        $custom = '';
    }

    echo '
        <tr>
        <td>' . $sensor['sensor_class'] . '</td>
        <td>' . $sensor['sensor_type'] . '</td>
        <td style="white-space: nowrap">' . $sensor['sensor_descr'] . '</td>
        <td>' . $sensor['sensor_current'] . '</td>
        <td>
        <div class="form-group has-feedback">
        <input type="text" class="form-control col-sm-1 input-sm sensor" id="high-' . $sensor['device_id'] . '" data-device_id="' . $sensor['device_id'] . '" data-value_type="sensor_limit" data-sensor_id="' . $sensor['sensor_id'] . '" value="' . $sensor['sensor_limit'] . '">
        </div>
        </td>
        <td>
        <div class="form-group has-feedback">
        <input type="text" class="form-control col-sm-1 input-sm sensor" id="high-' . $sensor['device_id'] . '-warn" data-device_id="' . $sensor['device_id'] . '" data-value_type="sensor_limit_warn" data-sensor_id="' . $sensor['sensor_id'] . '" value="' . $sensor['sensor_limit_warn'] . '">
        </div>
        </td>
        <td>
        <div class="form-group has-feedback">
        <input type="text" class="form-control col-sm-1 input-sm sensor" id="low-' . $sensor['device_id'] . '-warn" data-device_id="' . $sensor['device_id'] . '" data-value_type="sensor_limit_low_warn" data-sensor_id="' . $sensor['sensor_id'] . '" value="' . $sensor['sensor_limit_low_warn'] . '">
        </div>
        </td>
        <td>
        <div class="form-group has-feedback">
        <input type="text" class="form-control input-sm sensor" id="low-' . $sensor['device_id'] . '" data-device_id="' . $sensor['device_id'] . '" data-value_type="sensor_limit_low" data-sensor_id="' . $sensor['sensor_id'] . '" value="' . $sensor['sensor_limit_low'] . '">
        </div>
        </td>
        <td>
        <input type="checkbox" name="alert-status" data-device_id="' . $sensor['device_id'] . '" data-sensor_id="' . $sensor['sensor_id'] . '" data-sensor_desc="' . $sensor['sensor_descr'] . '" ' . $alert_status . '>
        </td>
        <td>
        <a type="button" class="btn btn-danger btn-sm ' . $custom . ' remove-custom" id="remove-custom" name="remove-custom" data-sensor_id="' . $sensor['sensor_id'] . '">Reset</a>
        </td>
        </tr>
        ';
}
?>
</table>
</form>
<form id="alert-reset">
<?php
echo csrf_field();
foreach ($rollback as $reset_data) {
    echo '
        <input type="hidden" name="sensor_id[]" value="' . $reset_data['sensor_id'] . '">
        <input type="hidden" name="sensor_limit[]" value="' . $reset_data['sensor_limit'] . '">
        <input type="hidden" name="sensor_limit_warn[]" value="' . $reset_data['sensor_limit_warn'] . '">
        <input type="hidden" name="sensor_limit_low_warn[]" value="' . $reset_data['sensor_limit_low_warn'] . '">
        <input type="hidden" name="sensor_limit_low[]" value="' . $reset_data['sensor_limit_low'] . '">
        <input type="hidden" name="sensor_alert[]" value="' . $reset_data['sensor_alert'] . '">
        ';
}
?>
<input type="hidden" name="type" value="sensor-alert-reset">
<button id = "newThread" class="btn btn-primary btn-sm" type="submit">Reset values</button>
</form>
<script>
$('#newThread').on('click', function(e){
    e.preventDefault(); // preventing default click action

    var form = $('#alert-reset');

    $.ajax({
        type: 'POST',
            url: 'ajax_form.php',
            data: form.serialize(),
      dataType: "json",
      success: function(data){
          if (data.status == 'ok') {
              toastr.success(data.message);
              setTimeout(function() {
                  location.reload(true);
              }, 2000);
          } else {
              toastr.error(data.message);
          }

      },
          error:function(data){
              toastr.error(data.message);
          }
    });
});
</script>
<script>

$('.sensor').on('focusin', function(){
    console.log("Saving value " + $(this).val());
    $(this).data('val', $(this).val());
});

$( ".sensor" ).on('blur keyup',function(e) {
    if (e.type === 'keyup' && e.keyCode !== 13) return;
    var prev = $(this).data('val');
    var data = $(this).val();
    if(prev === data) return;

    var sensor_type = $(this).attr('id');
    var device_id = $(this).data("device_id");
    var sensor_id = $(this).data("sensor_id");
    var value_type = $(this).data("value_type");
    var $this = $(this);
    $.ajax({
        type: 'POST',
            url: 'ajax_form.php',
            data: { type: "<?php echo $ajax_prefix; ?>-update", device_id: device_id, data: data, sensor_id: sensor_id , value_type: value_type},
            dataType: "json",
            success: function(data){
            if (data.status == 'ok') {
                $('.remove-custom[data-sensor_id='+sensor_id+']').removeClass('disabled');
                toastr.success(data.message);
            } else {
                toastr.error(data.message);
            }

            },
            error:function(data){
                toastr.error(data.message);
            }
    });
});

$("[name='alert-status']").bootstrapSwitch('offColor','danger');
$('input[name="alert-status"]').on('switchChange.bootstrapSwitch',  function(event, state) {
    event.preventDefault();
    var $this = $(this);
    var device_id = $(this).data("device_id");
    var sensor_id = $(this).data("sensor_id");
    var sensor_desc = $(this).data("sensor_desc");
    $.ajax({
        type: 'POST',
            url: 'ajax_form.php',
            data: { type: "<?php echo $ajax_prefix; ?>-alert-update", device_id: device_id, sensor_id: sensor_id, sensor_desc: sensor_desc, state: state},
            dataType: "json",
            success: function(data){
                if (data.status != 'error') {
                    if (data.status == 'ok') {
                        toastr.success(data.message);
                    } else {
                        toastr.info(data.message);
                    }
                } else {
                    toastr.error(data.message);
                }
            },
                error:function(data){
                    toastr.error(data.message);
                }
    });
});
$("[name='remove-custom']").on('click', function(event) {
    event.preventDefault();
    var $this = $(this);
    var sensor_id = $(this).data("sensor_id");
    $.ajax({
        type: 'POST',
            url: 'ajax_form.php',
            data: { type: "<?php echo $ajax_prefix; ?>-alert-update", sensor_id: sensor_id, sub_type: "remove-custom" },
            dataType: "json",
            success: function(data){
                toastr.success(data.message);
                $this.addClass('disabled');
            },
                error:function(data){
                    toastr.error(data.message);
                }
    });
});

</script>
