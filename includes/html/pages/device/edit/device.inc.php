<?php

use App\Models\Device;
use App\Models\Location;

$device_model = Device::find($device['device_id']);

if ($_POST['editing']) {
    if (Auth::user()->hasGlobalAdmin()) {
        if (isset($_POST['parent_id'])) {
            $parents = array_diff((array)$_POST['parent_id'], ['0']);
            // TODO avoid loops!
            $device_model->parents()->sync($parents);
        }

        $override_sysLocation = (int)isset($_POST['override_sysLocation']);
        $override_sysLocation_string = isset($_POST['sysLocation']) ? $_POST['sysLocation'] : null;

        if ($override_sysLocation) {
            if ($override_sysLocation_string) {
                $location = Location::firstOrCreate(['location' => $override_sysLocation_string]);
                $device_model->location()->associate($location);
            } else {
                $device_model->location()->dissociate();
            }
        } elseif ($device_model->override_sysLocation) {
            // no longer overridden, clear location
            $device_model->location()->dissociate();
        }

        $device_model->override_sysLocation = $override_sysLocation;
        $device_model->purpose = $_POST['descr'];
        $device_model->ignore = (int)isset($_POST['ignore']);
        $device_model->disabled = (int)isset($_POST['disabled']);
        $device_model->type = $_POST['type'];

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
                if ($result == "") {
                    Toastr::success("Hostname updated from {$device['hostname']} to {$_POST['hostname']}");
                    echo '
                        <script>
                            var loc = window.location;
                            window.location.replace(loc.protocol + "//" + loc.host + loc.pathname + loc.search);
                        </script>
                    ';
                } else {
                    Toastr::error($result . ".  Does your web server have permission to modify the rrd files?");
                }
            } else {
                Toastr::error('Only administrative users may update the device hostname');
            }
        }
    } else {
        include 'includes/html/error-no-perm.inc.php';
    }
}

?>
<h3> Device Settings </h3>
<div class="row">
    <div class="col-md-1 col-md-offset-2">
        <form id="delete_host" name="delete_host" method="post" action="delhost/" role="form">
            <?php echo csrf_field() ?>
            <input type="hidden" name="id" value="<?php echo($device['device_id']); ?>">
            <button type="submit" class="btn btn-danger" name="Submit"><i class="fa fa-trash"></i> Delete device</button>
        </form>
    </div>
    <div class="col-md-1 col-md-offset-2">
        <?php
        if (\LibreNMS\Config::get('enable_clear_discovery') == 1 && !$device['snmp_disable']) {
        ?>
            <button type="submit" id="rediscover" data-device_id="<?php echo($device['device_id']); ?>" class="btn btn-primary" name="rediscover"><i class="fa fa-retweet"></i> Rediscover device</button>
        <?php
        }
        ?>
    </div>
</div>
<br>
<form id="edit" name="edit" method="post" action="" role="form" class="form-horizontal">
<?php echo csrf_field() ?>
<input type=hidden name="editing" value="yes">
    <div class="form-group" data-toggle="tooltip" data-container="body" data-placement="bottom" title="Change the hostname used for name resolution" >
        <label for="edit-hostname-input" class="col-sm-2 control-label" >Hostname:</label>
        <div class="col-sm-6">
            <input type="text" id="edit-hostname-input" name="hostname" class="form-control" disabled value=<?php echo(display($device['hostname'])); ?> />
        </div>
        <div class="col-sm-2">
            <button name="hostname-edit-button" id="hostname-edit-button" class="btn btn-danger"> <i class="fa fa-pencil"></i> </button>
        </div>
    </div>
     <div class="form-group">
        <label for="descr" class="col-sm-2 control-label">Description:</label>
        <div class="col-sm-6">
            <textarea id="descr" name="descr" class="form-control"><?php echo(display($device_model->purpose)); ?></textarea>
        </div>
    </div>
    <div class="form-group">
        <label for="type" class="col-sm-2 control-label">Type:</label>
        <div class="col-sm-6">
            <select id="type" name="type" class="form-control">
                <?php
                $unknown = 1;

                foreach (\LibreNMS\Config::get('device_types') as $type) {
                    echo('          <option value="'.$type['type'].'"');
                    if ($device_model->type == $type['type']) {
                        echo(' selected="1"');
                        $unknown = 0;
                    }
                    echo(' >' . ucfirst($type['type']) . '</option>');
                }
                if ($unknown) {
                    echo('          <option value="other">Other</option>');
                }
                ?>
            </select>
       </div>
    </div>
    <div class="form-group">
        <label for="sysLocation" class="col-sm-2 control-label">Override sysLocation:</label>
        <div class="col-sm-6">
          <input onclick="edit.sysLocation.disabled=!edit.override_sysLocation.checked; edit.sysLocation.select()" type="checkbox" name="override_sysLocation"
                <?php
                if ($device_model->override_sysLocation) {
                    echo(' checked="1"');
                }
                ?> />
        </div>
    </div>
    <div class="form-group" title="To set coordinates, include [latitude,longitude]">
        <div class="col-sm-2"></div>
        <div class="col-sm-6">
          <input id="sysLocation" name="sysLocation" class="form-control"
                <?php
                if (!$device_model->override_sysLocation) {
                    echo(' disabled="1"');
                }
                ?> value="<?php echo display($device_model->location); ?>" />
        </div>
    </div>
    <div class="form-group">
        <label for="parent_id" class="col-sm-2 control-label">This device depends on:</label>
        <div class="col-sm-6">
            <select multiple name="parent_id[]" id="parent_id" class="form-control">
                <?php
                $dev_parents = dbFetchColumn('SELECT device_id from devices WHERE device_id IN (SELECT dr.parent_device_id from devices as d, device_relationships as dr WHERE d.device_id = dr.child_device_id AND d.device_id = ?)', array($device['device_id']));
                if (!$dev_parents) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
                ?>
                <option value="0" <?=$selected?>>None</option>
                <?php
                $available_devs = dbFetchRows('SELECT `device_id`,`hostname`,`sysName` FROM `devices` WHERE `device_id` <> ? ORDER BY `hostname` ASC', array($device['device_id']));
                foreach ($available_devs as $dev) {
                    if (in_array($dev['device_id'], $dev_parents)) {
                        $selected = 'selected="selected"';
                    } else {
                        $selected = '';
                    }
                    echo "<option value=". $dev['device_id']. " " . $selected . ">" . $dev['hostname'] . " (" . $dev['sysName'] .")</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="disabled" class="col-sm-2 control-label">Disable:</label>
        <div class="col-sm-6">
          <input name="disabled" type="checkbox" id="disabled" value="1"
                <?php
                if ($device_model->disabled) {
                    echo("checked=checked");
                }
                ?> />
        </div>
    </div>
    <div class="form-group">
        <label for="ignore" class="col-sm-2 control-label">Ignore</label>
        <div class="col-sm-6">
           <input name="ignore" type="checkbox" id="ignore" value="1"
                <?php
                if ($device_model->ignore) {
                    echo("checked=checked");
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
    $("#rediscover").click(function() {
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
    $('#hostname-edit-button').click(function(e) {
        e.preventDefault();
        disabled_state = document.getElementById('edit-hostname-input').disabled;
        if (disabled_state == true) {
            document.getElementById('edit-hostname-input').disabled = false;
        } else {
            document.getElementById('edit-hostname-input').disabled = true;
        }
    });
    $('#sysLocation').keypress(function (e) {
        if(e.keyCode === 13) {
            e.preventDefault();
            $('#edit').submit();
        }
    });
    $('#parent_id').select2({
        width: 'resolve'
    });
</script>
<?php
print_optionbar_start();
list($sizeondisk, $numrrds) = foldersize(get_rrd_dir($device['hostname']));
echo("Size on Disk: <b>" . formatStorage($sizeondisk) . "</b> in <b>" . $numrrds . " RRD files</b>.");
print_optionbar_end();
?>
