<?php
include '../includes/discovery/functions.inc.php';

if ($_POST['editing']) {
    if ($_SESSION['userlevel'] > '7') {
        $custom_oid = mres($_POST['custom_oid']);
        $sensor_class = mres($_POST['sensor_class']);
        $custom_descr = mres($_POST['custom_descr']);
        $custom_diviz = mres($_POST['custom_diviz']);
        $custom_multip = mres($_POST['custom_multip']);
        if (!empty($custom_oid) && !empty($custom_descr)) {
            if (substr($custom_oid, 0, 1) != '.') {
                $custom_oid = "." . $custom_oid;
            }
            $index_tmp  = dbFetchCell("SELECT `sensor_index` FROM `sensors` WHERE `device_id` = ? AND `sensor_type` LIKE 'custom_oid' ORDER BY `sensor_type` ASC", array($device['device_id']));
            $index_tmp ++;
            discover_sensor($valid['sensor'], $sensor_class, $device, $custom_oid, $index_tmp, 'custom_oid', $custom_descr, $custom_diviz, $custom_multip, null, null, null, null, null);

            $update_message = 'Custom graph added.';
            $updated        = 1;
        } else {
            $update_message = 'Oid and Description must be set';
        }
    } else {
        include 'includes/error-no-perm.inc.php';
    }//end if
}//end if

if ($_POST['remove_custom_id']) {
    if ($_SESSION['userlevel'] > '7') {
        $sensor_id_rem = mres($_POST['remove_custom_id']);
        $sensor_index_rem = mres($_POST['remove_custom_index']);
        $sensor_class_rem = mres($_POST['remove_custom_class']);
        dbDelete('sensors', "`sensor_id` = ?", array($sensor_id_rem));
        $ex = shell_exec("bash -c '(rm -vf ".trim(get_rrd_dir($device['hostname']))."/sensor-".$sensor_class_rem."-custom_oid-".$sensor_index_rem.".rrd 2>&1 ) && echo -n OK'");
        $tmp = explode("\n", $ex);
        if ($tmp[sizeof($tmp)-1] != "OK") {
            $update_message = "Could not remove files:\n$ex\n";
        } else {
            $update_message = "Remove files:\n$ex\n";
            $updated        = 1;
        }
    }
}

if ($updated && $update_message) {
    print_message($update_message);
} elseif ($update_message) {
    print_error($update_message);
}

?>
<h3>Custom Sensor Oid</h3>

<form id="edit" name="edit" method="post" action="" role="form" class="form-horizontal">
<input type="hidden" name="editing" value="yes">
  <div class="form-group">
    <label for="custom_oid" class="col-sm-2 control-label">Oid:</label>
    <div class="col-sm-6">
      <input id="custom_oid" name="custom_oid" class="form-control" value="" />
    </div>
  </div>
  <div class="form-group">
    <label for="sensor_class" class="col-sm-2 control-label">Sensor Class</label>
    <div class="col-sm-1">
    <select id='sensor_class' name='sensor_class' class='form-control input-sm' onChange='changeForm();'>
    <option value='airflow'>airflow</option>
    <option value='charge'>charge</option>
    <option value='cooling'>cooling</option>
    <option value='current'>current</option>
    <option value='dbm'>dbm</option>
    <option value='fanspeed'>fanspeed</option>
    <option value='frequency'>frequency</option>
    <option value='humidity'>humidity</option>
    <option value='load'>load</option>
    <option value='power'>power</option>
    <option value='pressure'>pressure</option>
    <option value='runtime'>runtime</option>
    <option value='signal'>signal</option>
    <option value='snr'>snr</option>
    <option value='state'>state</option>
    <option value='temperature'>temperature</option>
    <option value='voltage'>voltage</option>
    <option value='delay'>delay</option>
    <option value='quality_factor'>quality_factor</option>
    <option value='chromatic_disperision'>chromatic_disperision</option>
    <option value='ber'>ber</option>
    </select>
    </div>
  </div>
  <div class="form-group">
    <label for="custom_descr" class="col-sm-2 control-label">Description</label>
    <div class="col-sm-6">
      <input id="custom_descr" name="custom_descr" class="form-control" value="" />
    </div>
  </div>
 <div class="form-group">
    <label for="custom_diviz" class="col-sm-2 control-label">Divizior</label>
    <div class="col-sm-1">
      <input id="custom_diviz" name="custom_diviz" class="form-control" value="" />
    </div>
  </div>
 <div class="form-group">
    <label for="custom_multip" class="col-sm-2 control-label">Multiplier</label>
    <div class="col-sm-1">
      <input id="custom_multip" name="custom_multip" class="form-control" value="" />
    </div>
  </div>
  <div class="row">
    <div class="col-md-1 col-md-offset-2">
        <button type="submit" name="Submit"  class="btn btn-default"><i class="fa fa-check"></i> Save</button>
    </div>
  </div>
  <br><br>
  </div>


</form>
<form id="remove-custom" name="remove-custom" method="post" action="" role="form" class="form-inline">

<table class="table table-hover table-condensed table-bordered">
  <tr class="info">
    <th>Class</th>
    <th>Desc</th>
    <th></th>
  </tr>


<?php
$custom_graph = dbFetch("SELECT * FROM `sensors` WHERE `device_id` = ? AND `sensor_type` LIKE 'custom_oid' ORDER BY `sensor_type` ASC", array($device['device_id']));
foreach ($custom_graph as $key => $val) {
    echo '
        <form id="remove-custom" name="remove-custom" method="post" action="" role="form" class="form-inline">
        <tr>
        <td>'.$val['sensor_class'].'</td>
        <td>'.$val['sensor_descr'].'</td>
        <td>
        <input type="hidden" name="remove_custom_index" value="'.$val['sensor_index'].'" />
        <input type="hidden" name="remove_custom_class" value="'.$val['sensor_class'].'" />
        <button type="submit" class="btn btn-danger remove-custom" id="remove-custom" name="remove_custom_id" value="'.$val['sensor_id'].'">Delete</button>
        </td>
        </tr>
        </form>
    ';
}
?>

</form>
