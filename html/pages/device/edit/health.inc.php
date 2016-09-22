<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// FUA
?>

<h3>Health settings</h3>

<form class="form-inline">
<table class="table table-hover table-condensed table-bordered">
  <tr class="info">
    <th>Class</th>
    <th>Type</th>
    <th>Desc</th>
    <th>Current</th>
    <th class="col-sm-1">High</th>
    <th class="col-sm-1">Low</th>
    <th class="col-sm-2">Alerts</th>
    <th></th>
  </tr>
<?php
foreach (dbFetchRows("SELECT * FROM sensors WHERE device_id = ? AND sensor_deleted='0'", array($device['device_id'])) as $sensor) {
    $rollback[] = array(
        'sensor_id'        => $sensor['sensor_id'],
        'sensor_limit'     => $sensor['sensor_limit'],
        'sensor_limit_low' => $sensor['sensor_limit_low'],
        'sensor_alert'     => $sensor['sensor_alert'],
    );
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
        <td>'.$sensor['sensor_class'].'</td>
        <td>'.$sensor['sensor_type'].'</td>
        <td>'.$sensor['sensor_descr'].'</td>
        <td>'.$sensor['sensor_current'].'</td>
        <td>
        <div class="form-group has-feedback">
        <input type="text" class="form-control input-sm sensor" id="high-'.$sensor['device_id'].'" data-device_id="'.$sensor['device_id'].'" data-value_type="sensor_limit" data-sensor_id="'.$sensor['sensor_id'].'" value="'.$sensor['sensor_limit'].'">
        <span class="form-control-feedback">
            <i class="fa" aria-hidden="true"></i>
        </span>
        </div>
        </td>
        <td>
        <div class="form-group has-feedback">
        <input type="text" class="form-control input-sm sensor" id="low-'.$sensor['device_id'].'" data-device_id="'.$sensor['device_id'].'" data-value_type="sensor_limit_low" data-sensor_id="'.$sensor['sensor_id'].'" value="'.$sensor['sensor_limit_low'].'">
        <span class="form-control-feedback">
            <i class="fa" aria-hidden="true"></i>
        </span>
        </div>
        </td>
        <td>
        <input type="checkbox" name="alert-status" data-device_id="'.$sensor['device_id'].'" data-sensor_id="'.$sensor['sensor_id'].'" '.$alert_status.'>
        </td>
        <td>
        <a type="button" class="btn btn-danger btn-sm '.$custom.' remove-custom" id="remove-custom" name="remove-custom" data-sensor_id="'.$sensor['sensor_id'].'">Clear custom</a>
        </td>
        </tr>
        ';
}
?>
</table>
</form>
<form id="alert-reset">
<?php
foreach ($rollback as $reset_data) {
    echo '
        <input type="hidden" name="sensor_id[]" value="'.$reset_data['sensor_id'].'">
        <input type="hidden" name="sensor_limit[]" value="'.$reset_data['sensor_limit'].'">
        <input type="hidden" name="sensor_limit_low[]" value="'.$reset_data['sensor_limit_low'].'">
        <input type="hidden" name="sensor_alert[]" value="'.$reset_data['sensor_alert'].'">
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
      dataType: "html",
      success: function(data){
          //alert(data);
          location.reload(true);
      },
          error:function(){
              //alert('bad');
          }
    });
});
</script>
<script>

$( ".sensor" ).blur(function() {
    var sensor_type = $(this).attr('id');
    var device_id = $(this).data("device_id");
    var sensor_id = $(this).data("sensor_id");
    var value_type = $(this).data("value_type");
    var data = $(this).val();
    var $this = $(this);
    $.ajax({
        type: 'POST',
            url: 'ajax_form.php',
            data: { type: "health-update", device_id: device_id, data: data, sensor_id: sensor_id , value_type: value_type},
            dataType: "html",
            success: function(data){
                $this.closest('.form-group').addClass('has-success');
                $this.next().addClass('fa-check');
                setTimeout(function(){
                    $this.closest('.form-group').removeClass('has-success');
                    $this.next().removeClass('fa-check');
                }, 2000);
            },
                error:function(){
                    $(this).closest('.form-group').addClass('has-error');
                    $this.next().addClass('fa-times');
                    setTimeout(function(){
                        $this.closest('.form-group').removeClass('has-error');
                        $this.next().removeClass('fa-times');
                    }, 2000);
                }
    });
});

$("[name='alert-status']").bootstrapSwitch('offColor','danger');
$('input[name="alert-status"]').on('switchChange.bootstrapSwitch',  function(event, state) {
    event.preventDefault();
    var $this = $(this);
    var device_id = $(this).data("device_id");
    var sensor_id = $(this).data("sensor_id");
    $.ajax({
        type: 'POST',
            url: 'ajax_form.php',
            data: { type: "sensor-alert-update", device_id: device_id, sensor_id: sensor_id, state: state},
            dataType: "html",
            success: function(data){
                //alert('good');
            },
                error:function(){
                    //alert('bad');
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
            data: { type: "sensor-alert-update", sensor_id: sensor_id, sub_type: "remove-custom" },
            dataType: "html",
            success: function(data){
                $this.addClass('disabled');
            },
                error:function(){
                    //alert('bad');
                }
    });
});

</script>
