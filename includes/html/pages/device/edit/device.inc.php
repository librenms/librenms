<?php

use App\Models\Device;

require_once 'includes/html/modal/device_maintenance.inc.php';

$device_model = Device::find($device['device_id']);

if ($_POST['editing']) {
    if (Auth::user()->hasGlobalAdmin()) {
        if (isset($_POST['parent_id'])) {
            $parents = array_diff((array) $_POST['parent_id'], ['0']);
            // TODO avoid loops!
            $device_model->parents()->sync($parents);
        }

        $override_sysLocation = (int) isset($_POST['override_sysLocation']);
        $override_sysLocation_string = $_POST['sysLocation'] ?? null;

        if ($override_sysLocation) {
            $device_model->override_sysLocation = false;  // allow override (will be set to actual value later)
            $device_model->setLocation($override_sysLocation_string, true);
            optional($device_model->location)->save();
        } elseif ($device_model->override_sysLocation) {
            // no longer overridden, clear location
            $device_model->location()->dissociate();
        }

        $device_model->override_sysLocation = $override_sysLocation;
        $device_model->purpose = $_POST['descr'];
        $device_model->poller_group = $_POST['poller_group'];
        $device_model->ignore = (int) isset($_POST['ignore']);
        $device_model->disabled = (int) isset($_POST['disabled']);
        $device_model->disable_notify = (int) isset($_POST['disable_notify']);
        $device_model->type = $_POST['type'];
        $device_model->overwrite_ip = $_POST['overwrite_ip'];

        if ($device_model->isDirty('type')) {
            set_dev_attrib($device, 'override_device_type', true);
        }

        if ($device_model->isDirty()) {
            if ($device_model->save()) {
                Toastr::success(__('Device record updated'));
            } else {
                Toastr::error(__('Device record update error'));
            }
        }

        if (isset($_POST['hostname']) && $_POST['hostname'] !== '' && $_POST['hostname'] !== $device['hostname']) {
            if (Auth::user()->hasGlobalAdmin()) {
                $result = renamehost($device['device_id'], $_POST['hostname'], 'webui');
                if ($result == '') {
                    Toastr::success("Hostname updated from {$device['hostname']} to {$_POST['hostname']}");
                    echo '
                        <script>
                            var loc = window.location;
                            window.location.replace(loc.protocol + "//" + loc.host + loc.pathname + loc.search);
                        </script>
                    ';
                } else {
                    Toastr::error($result . '.  Does your web server have permission to modify the rrd files?');
                }
            } else {
                Toastr::error('Only administrative users may update the device hostname');
            }
        }

        $override_sysContact_bool = $_POST['override_sysContact'];
        if (isset($_POST['sysContact'])) {
            $override_sysContact_string = $_POST['sysContact'];
        }

        if ($override_sysContact_bool) {
            set_dev_attrib($device, 'override_sysContact_bool', '1');
        } else {
            set_dev_attrib($device, 'override_sysContact_bool', '0');
        }

        if (isset($override_sysContact_string)) {
            set_dev_attrib($device, 'override_sysContact_string', $override_sysContact_string);
        }
    } else {
        include 'includes/html/error-no-perm.inc.php';
    }
}

$override_sysContact_bool = get_dev_attrib($device, 'override_sysContact_bool');
$override_sysContact_string = get_dev_attrib($device, 'override_sysContact_string');
$disable_notify = get_dev_attrib($device, 'disable_notify');

?>

<h3> Device Settings </h3>
<div class="row">
    <!-- Bootstrap 3 doesn't support mediaqueries for text aligns (e.g. text-md-left), which makes these buttons stagger on sm or xs screens -->
    <div class="col-md-2 col-md-offset-2">
        <form id="delete_host" name="delete_host" method="post" action="delhost/" role="form">
            <?php echo csrf_field() ?>
            <input type="hidden" name="id" value="<?php echo $device['device_id']; ?>">
            <button type="submit" class="btn btn-danger" name="Submit"><i class="fa fa-trash"></i> Delete device</button>
        </form>
    </div>
    <div class="col-md-2 text-center">
        <?php
        if (\LibreNMS\Config::get('enable_clear_discovery') == 1 && ! $device['snmp_disable']) {
            ?>
            <button type="submit" id="rediscover" data-device_id="<?php echo $device['device_id']; ?>" class="btn btn-primary" name="rediscover" title="Schedule the device for immediate rediscovery by the poller"><i class="fa fa-retweet"></i> Rediscover device</button>
            <?php
        }
        ?>
    </div>
    <div class="col-md-2 text-right">
        <button type="submit" id="reset_port_state" data-device_id="<?php echo $device['device_id']; ?>" class="btn btn-info" name="reset_ports"          <button type="submit" id="reset_port_state" data-device_id="<?php echo $device['device_id']; ?>" class="btn btn-info" name="reset_ports" title="Reset interface speed, admin up/down, and link up/down history, clearing associated alarms"><i class="fa fa-recycle"></i> Reset Port State</button>
    </div>
</div>
<br>
<form id="edit" name="edit" method="post" action="" role="form" class="form-horizontal">
<?php echo csrf_field() ?>
<input type=hidden name="editing" value="yes">
    <div class="form-group" data-toggle="tooltip" data-container="body" data-placement="bottom" title="Change the hostname used for name resolution" >
        <label for="edit-hostname-input" class="col-sm-2 control-label" >Hostname:</label>
        <div class="col-sm-6">
            <input type="text" id="edit-hostname-input" name="hostname" class="form-control" disabled value=<?php echo \LibreNMS\Util\Clean::html($device['hostname'], []); ?> />
        </div>
        <div class="col-sm-2">
            <button name="hostname-edit-button" id="hostname-edit-button" class="btn btn-danger"> <i class="fa fa-pencil"></i> </button>
        </div>
    </div>
    <div class="form-group" data-toggle="tooltip" data-container="body" data-placement="bottom" title="Use this IP instead of resolved one for polling" >
        <label for="edit-overwrite_ip-input" class="col-sm-2 control-label" >Overwrite IP:</label>
        <div class="col-sm-6">
            <input type="text" id="edit-overwrite_up-input" name="overwrite_ip" class="form-control" value=<?php echo $device_model->overwrite_ip; ?>>
        </div>
    </div>
     <div class="form-group">
        <label for="descr" class="col-sm-2 control-label">Description:</label>
        <div class="col-sm-6">
            <textarea id="descr" name="descr" class="form-control"><?php echo \LibreNMS\Util\Clean::html($device_model->purpose, []); ?></textarea>
        </div>
    </div>
    <div class="form-group">
        <label for="type" class="col-sm-2 control-label">Type:</label>
        <div class="col-sm-6">
            <select id="type" name="type" class="form-control">
                <?php
                $unknown = 1;

                foreach (\LibreNMS\Config::get('device_types') as $type) {
                    echo '          <option value="' . $type['type'] . '"';
                    if ($device_model->type == $type['type']) {
                        echo ' selected="1"';
                        $unknown = 0;
                    }
                    echo ' >' . ucfirst($type['type']) . '</option>';
                }
                if ($unknown) {
                    if (! is_null($device_model->type)) {
                        $device_type = htmlspecialchars($device_model->type);
                        echo '          <option value="' . $device_type . '" selected="1" >' . ucfirst($device_type) . '</option>';
                    } else {
                        echo '          <option value="other">Other</option>';
                    }
                }
                ?>
            </select>
       </div>
    </div>
    <div class="form-group">
        <label for="sysLocation" class="col-sm-2 control-label">Override sysLocation:</label>
        <div class="col-sm-6">
          <input onChange="edit.sysLocation.disabled=!edit.override_sysLocation.checked; edit.sysLocation.select()" type="checkbox" name="override_sysLocation" data-size="small"
                <?php
                if ($device_model->override_sysLocation) {
                    echo ' checked="1"';
                }
                ?> />
        </div>
    </div>
    <div class="form-group" title="To set coordinates, include [latitude,longitude]">
        <div class="col-sm-2"></div>
        <div class="col-sm-6">
          <input id="sysLocation" name="sysLocation" class="form-control"
                <?php
                if (! $device_model->override_sysLocation) {
                    echo ' disabled="1"';
                }
                ?> value="<?php echo \LibreNMS\Util\Clean::html($device_model->location, []); ?>" />
        </div>
    </div>
    <div class="form-group">
      <label for="override_sysContact" class="col-sm-2 control-label">Override sysContact</label>
      <div class="col-sm-6">
        <input onChange="edit.sysContact.disabled=!edit.override_sysContact.checked" type="checkbox" id="override_sysContact" name="override_sysContact" data-size="small"
    <?php
    if ($override_sysContact_bool) {
        echo ' checked="1"';
    }
    ?>
   />
      </div>
    </div>
    <div class="form-group">
      <div class="col-sm-2">
      </div>
      <div class="col-sm-6">
        <input id="sysContact" class="form-control" name="sysContact" size="32"
    <?php
    if (! $override_sysContact_bool) {
        echo ' disabled="1"';
    }
    ?>
    value="<?php echo $override_sysContact_string; ?>" />
      </div>
    </div>
    <div class="form-group">
        <label for="parent_id" class="col-sm-2 control-label">This device depends on:</label>
        <div class="col-sm-6">
            <select multiple name="parent_id[]" id="parent_id" class="form-control" style="width: 100%">
                <?php
                $dev_parents = dbFetchColumn('SELECT device_id from devices WHERE device_id IN (SELECT dr.parent_device_id from devices as d, device_relationships as dr WHERE d.device_id = dr.child_device_id AND d.device_id = ?)', [$device['device_id']]);
                if (! $dev_parents) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
                ?>
                <option value="0" <?=$selected?>>None</option>
                <?php
                $available_devs = dbFetchRows('SELECT `device_id`,`hostname`,`sysName` FROM `devices` WHERE `device_id` <> ? ORDER BY `hostname` ASC', [$device['device_id']]);
                foreach ($available_devs as $dev) {
                    if (in_array($dev['device_id'], $dev_parents)) {
                        $selected = 'selected="selected"';
                    } else {
                        $selected = '';
                    }
                    echo '<option value=' . $dev['device_id'] . ' ' . $selected . '>' . $dev['hostname'] . ' (' . $dev['sysName'] . ')</option>';
                }
                ?>
            </select>
        </div>
    </div>
<?php
if (\LibreNMS\Config::get('distributed_poller') === true) {
                    ?>
   <div class="form-group">
       <label for="poller_group" class="col-sm-2 control-label">Poller Group</label>
       <div class="col-sm-6">
           <select name="poller_group" id="poller_group" class="form-control input-sm">
           <option value="0">General<?=\LibreNMS\Config::get('distributed_poller_group') == 0 ? ' (default Poller)' : ''?></option>
    <?php
    foreach (dbFetchRows('SELECT `id`,`group_name` FROM `poller_groups` ORDER BY `group_name`') as $group) {
        echo '<option value="' . $group['id'] . '"' .
        ($device_model->poller_group == $group['id'] ? ' selected' : '') . '>' . $group['group_name'];
        echo \LibreNMS\Config::get('distributed_poller_group') == $group['id'] ? ' (default Poller)' : '';
        echo '</option>';
    } ?>
           </select>
       </div>
   </div>
    <?php
                }//endif
?>
    <div class="form-group">
        <label for="disabled" class="col-sm-2 control-label">Disable polling and alerting:</label>
        <div class="col-sm-6">
          <input name="disabled" type="checkbox" id="disabled" value="1" data-size="small"
                <?php
                if ($device_model->disabled) {
                    echo 'checked=checked';
                }
                ?> />
        </div>
    </div>
    <div class="form-group">
      <label for="maintenance" class="col-sm-2 control-label"></label>
      <div class="col-sm-6">
      <button type="button" id="maintenance" data-device_id="<?php echo $device['device_id']; ?>" <?php echo \LibreNMS\Alert\AlertUtil::isMaintenance($device['device_id']) ? 'disabled class="btn btn-warning"' : 'class="btn btn-success"'?> name="maintenance"><i class="fa fa-wrench"></i> Maintenance Mode</button>
      </div>
    </div>

    <div class="form-group">
      <label for="disable_notify" class="col-sm-2 control-label">Disable alerting:</label>
      <div class="col-sm-6">
        <input id="disable_notify" type="checkbox" name="disable_notify" data-size="small"
                <?php
                if ($device_model->disable_notify) {
                    echo 'checked=checked';
                }
                ?> />
      </div>
    </div>
    <div class="form-group">
        <label for="ignore" class="col-sm-2 control-label" title="Tag device to ignore alerts. Alert checks will still run.
However, ignore tag can be read in alert rules.
If `devices.ignore = 0` or `macros.device = 1` condition is is set and ignore alert tag is on, the alert rule won't match.">Ignore alert tag:</label>
        <div class="col-sm-6">
           <input name="ignore" type="checkbox" id="ignore" value="1" data-size="small"
                <?php
                if ($device_model->ignore) {
                    echo 'checked=checked';
                }
                ?> />
        </div>
    </div>
    <div class="row">
        <div class="col-md-1 col-md-offset-2">
            <button type="submit" name="Submit"  class="btn btn-default"><i class="fa fa-check"></i> Save</button>
        </div>
    </div>
</form>
<br />
<script>
    $('[type="checkbox"]').bootstrapSwitch('offColor', 'danger');

    $("#maintenance").on("click", function() {
        $("#device_maintenance_modal").modal('show');
    });
    $("#rediscover").on("click", function() {
        var device_id = $(this).data("device_id");
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: { type: "rediscover-device", device_id: device_id },
            dataType: "json",
            success: function(data){
                if(data['status'] == 'ok') {
                    toastr.success(data['message']);
                } else {
                    toastr.error(data['message']);
                }
            },
            error:function(){
                toastr.error('An error occured setting this device to be rediscovered');
            }
        });
    });
    $("#reset_port_state").on("click", function() {
        var device_id = $(this).data("device_id");
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: { type: "reset-port-state", device_id: device_id },
            dataType: "json",
            success: function(data){
                if(data['status'] == 'ok') {
                    toastr.success(data['message']);
                } else {
                    toastr.error(data['message']);
                }
            },
            error:function(){
                toastr.error('An error occured while attempting to reset port state alarms');
            }
        });
    });
    $('#hostname-edit-button').on("click", function(e) {
        e.preventDefault();
        disabled_state = document.getElementById('edit-hostname-input').disabled;
        if (disabled_state == true) {
            document.getElementById('edit-hostname-input').disabled = false;
        } else {
            document.getElementById('edit-hostname-input').disabled = true;
        }
    });
    $('#sysLocation').on('keypress', function (e) {
        if(e.keyCode === 13) {
            e.preventDefault();
            $('#edit').trigger( "submit" );
        }
    });
    $('#parent_id').select2({
        width: 'resolve'
    });
</script>
<?php
print_optionbar_start();
[$sizeondisk, $numrrds] = foldersize(Rrd::dirFromHost($device['hostname']));
echo 'Size on Disk: <b>' . \LibreNMS\Util\Number::formatBi($sizeondisk, 2, 3) . '</b> in <b>' . $numrrds . ' RRD files</b>.';
echo ' | Last polled: <b>' . $device['last_polled'] . '</b>';
if ($device['last_discovered']) {
    echo ' | Last discovered: <b>' . $device['last_discovered'] . '</b>';
}
print_optionbar_end();
?>
