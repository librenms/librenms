@extends('layouts.librenmsv1')

@section('content')
    <?php

    use App\Models\Device;

    /** Device $device **/
    $device ??= new Device;

    if (! empty($_POST['editing'])) {
        if (Auth::user()->hasGlobalAdmin()) {
            $reload = false;
            if (isset($_POST['parent_id'])) {
                $parents = array_diff((array) $_POST['parent_id'], ['0']);
                // TODO avoid loops!
                $device->parents()->sync($parents);
            }

            $override_sysLocation = (int) isset($_POST['override_sysLocation']);
            $override_sysLocation_string = $_POST['sysLocation'] ?? null;

            if ($override_sysLocation) {
                $device->override_sysLocation = false;  // allow override (will be set to actual value later)
                $device->setLocation($override_sysLocation_string, true);
                optional($device->location)->save();
            } elseif ($device->override_sysLocation) {
                // no longer overridden, clear location
                $device->location()->dissociate();
            }

            $device->override_sysLocation = $override_sysLocation;
            $device->display = empty($_POST['display']) ? null : $_POST['display'];
            $device->purpose = $_POST['descr'];
            $device->poller_group = $_POST['poller_group'];
            $device->ignore = (int) isset($_POST['ignore']);
            $device->ignore_status = (int) isset($_POST['ignore_status']);
            $device->disabled = (int) isset($_POST['disabled']);
            $device->disable_notify = (int) isset($_POST['disable_notify']);
            $device->type = $_POST['type'];
            $device->overwrite_ip = $_POST['overwrite_ip'];

            if ($device->isDirty('type')) {
                set_dev_attrib($device, 'override_device_type', true);
            }

            if ($device->isDirty('display')) {
                $reload = true;
            }

            if ($device->isDirty()) {
                if ($device->save()) {
                    toast()->success(__('Device record updated'));
                } else {
                    toast()->error(__('Device record update error'));
                }
            }

            if (isset($_POST['hostname']) && $_POST['hostname'] !== '' && $_POST['hostname'] !== $device->hostname) {
                if (Auth::user()->hasGlobalAdmin()) {
                    $result = renamehost($device->device_id, trim($_POST['hostname']), 'webui');
                    if ($result == '') {
                        toast()->success("Hostname updated from {$device->hostname} to {$_POST['hostname']}");
                        $reload = true;
                    } else {
                        toast()->error($result . '.  Does your web server have permission to modify the rrd files?');
                    }
                } else {
                    toast()->error('Only administrative users may update the device hostname');
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

            // some changed data not stateful, just reload the page
            if ($reload) {
                echo '<script>window.location.reload();</script>';
            }
        } else {
            include 'includes/html/error-no-perm.inc.php';
        }
    }

    $override_sysContact_bool = $device->getAttrib('override_sysContact_bool');
    $override_sysContact_string = $device->getAttrib('override_sysContact_string') ?? '';
    $disable_notify = $device->getAttrib('disable_notify');

    ?>
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" />

        <div class="row">
            <!-- Bootstrap 3 doesn't support mediaqueries for text aligns (e.g. text-md-left), which makes these buttons stagger on sm or xs screens -->
            <div class="col-md-2 col-md-offset-2">
                <form id="delete_host" name="delete_host" method="post" action="delhost/" role="form">
                    <?php echo csrf_field() ?>
                    <input type="hidden" name="id" value="<?php echo $device->device_id; ?>">
                    <button type="submit" class="btn btn-danger" name="Submit"><i class="fa fa-trash"></i> Delete device</button>
                </form>
            </div>
            <div class="col-md-2 text-center">
                <?php
                if (\App\Facades\LibrenmsConfig::get('enable_clear_discovery') == 1 && ! $device->snmp_disable) {
                    ?>
                <button type="submit" id="rediscover" data-device_id="<?php echo $device->device_id; ?>" class="btn btn-primary" name="rediscover" title="Schedule the device for immediate rediscovery by the poller"><i class="fa fa-retweet"></i> Rediscover device</button>
                    <?php
                }
                ?>
            </div>
            <div class="col-md-2 text-right">
                <button type="submit" id="reset_port_state" data-device_id="<?php echo $device->device_id; ?>" class="btn btn-info" name="reset_ports"          <button type="submit" id="reset_port_state" data-device_id="<?php echo $device->device_id; ?>" class="btn btn-info" name="reset_ports" title="Reset interface speed, admin up/down, and link up/down history, clearing associated alarms"><i class="fa fa-recycle"></i> Reset Port State</button>
            </div>
        </div>
        <br>
        <form id="edit" name="edit" method="post" action="" role="form" class="form-horizontal">
            @method('PUT')
            @csrf
            <div class="form-group" data-toggle="tooltip" data-container="body" data-placement="bottom" title="Change the hostname used for name resolution" >
                <label for="edit-hostname-input" class="col-sm-2 control-label" >Hostname / IP</label>
                <div class="col-sm-6">
                    <input type="text" id="edit-hostname-input" name="hostname" class="form-control" disabled value="<?php echo htmlentities($device->hostname); ?>" />
                </div>
                <div class="col-sm-2">
                    <button type="button" name="hostname-edit-button" id="hostname-edit-button" class="btn btn-danger" onclick="toggleHostnameEdit()"> <i class="fa fa-pencil"></i> </button>
                </div>
            </div>

            <div class="form-group" data-toggle="tooltip" data-container="body" data-placement="bottom" title="Display Name for this device.  Keep short. Available placeholders: hostname, sysName, sysName_fallback, ip (e.g. '@{{ $sysName }}')" >
                <label for="edit-display-input" class="col-sm-2 control-label" >Display Name</label>
                <div class="col-sm-6">
                    <input type="text" id="edit-display-input" name="display" class="form-control" placeholder="System Default" value="<?php echo htmlentities($device->display ?? ''); ?>">
                </div>
            </div>

            <div class="form-group" data-toggle="tooltip" data-container="body" data-placement="bottom" title="Use this IP instead of resolved one for polling" >
                <label for="edit-overwrite_ip-input" class="col-sm-2 control-label text-danger" >Overwrite IP (do not use)</label>
                <div class="col-sm-6">
                    <input type="text" id="edit-overwrite_ip-input" name="overwrite_ip" class="form-control" value="<?php echo htmlentities($device->overwrite_ip ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="descr" class="col-sm-2 control-label">Description</label>
                <div class="col-sm-6">
                    <textarea id="descr" name="descr" class="form-control"><?php echo \LibreNMS\Util\Clean::html($device->purpose, []); ?></textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="type" class="col-sm-2 control-label">Type</label>
                <div class="col-sm-6">
                    <select id="type" name="type" class="form-control">
                        <?php
                        $unknown = 1;

                        foreach (\App\Facades\LibrenmsConfig::get('device_types') as $type) {
                            echo '          <option value="' . $type['type'] . '"';
                            if ($device->type == $type['type']) {
                                echo ' selected="1"';
                                $unknown = 0;
                            }
                            echo ' >' . ucfirst($type['type']) . '</option>';
                        }
                        if ($unknown) {
                            if (! is_null($device->type)) {
                                $device_type = htmlspecialchars($device->type);
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
                <label for="sysLocation" class="col-sm-2 control-label">Override sysLocation</label>
                <div class="col-sm-6">
                    <input onChange="edit.sysLocation.disabled=!edit.override_sysLocation.checked; edit.sysLocation.select()" type="checkbox" name="override_sysLocation" data-size="small"
                        <?php
                        if ($device->override_sysLocation) {
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
                           if (! $device->override_sysLocation) {
                               echo ' disabled="1"';
                           }
                           ?> value="<?php echo htmlentities($device->location); ?>" />
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
                           value="<?php echo htmlentities($override_sysContact_string); ?>" />
                </div>
            </div>
            <div class="form-group">
                <label for="parent_id" class="col-sm-2 control-label">This device depends on</label>
                <div class="col-sm-6">
                    <select multiple name="parent_id[]" id="parent_id" class="form-control" style="width: 100%">
                        <?php
                        $dev_parents = $device->parents()->pluck('device_id');
                        if (! $dev_parents) {
                            $selected = 'selected="selected"';
                        } else {
                            $selected = '';
                        }
                        ?>
                        <option value="0" <?=$selected?>>None</option>
                        <?php
                        $available_devs = Device::orderBy('hostname')->whereNot('device_id', $device->device_id)->select(['device_id', 'hostname', 'sysName'])->get();
                        foreach ($available_devs as $dev) {
                            if ($dev_parents->contains($dev->device_id)) {
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
            if (\App\Facades\LibrenmsConfig::get('distributed_poller') === true) {
                ?>
            <div class="form-group">
                <label for="poller_group" class="col-sm-2 control-label">Poller Group</label>
                <div class="col-sm-6">
                    <select name="poller_group" id="poller_group" class="form-control input-sm">
                        <option value="0">General<?=\App\Facades\LibrenmsConfig::get('distributed_poller_group') == 0 ? ' (default Poller)' : ''?></option>
                            <?php
                                foreach(\App\Models\PollerGroup::orderBy('group_name')->pluck('group_name', 'id') as $group_id => $group_name) {
                                echo '<option value="' . $group_id . '"' .
                                    ($device->poller_group == $group_id ? ' selected' : '') . '>' . htmlentities($group_name);
                                echo \App\Facades\LibrenmsConfig::get('distributed_poller_group') == $group_id ? ' (default Poller)' : '';
                                echo '</option>';
                            } ?>
                    </select>
                </div>
            </div>
                <?php
            }//endif
            ?>
            <div class="form-group">
                <label for="disabled" class="col-sm-2 control-label">Disable polling and alerting</label>
                <div class="col-sm-6">
                    <input name="disabled" type="checkbox" id="disabled" value="1" data-size="small"
                        <?php
                        if ($device->disabled) {
                            echo 'checked=checked';
                        }
                        ?> />
                </div>
            </div>
            <div class="form-group">
                <label for="maintenance" class="col-sm-2 control-label"></label>
                <div class="col-sm-6">
                    <button type="button"
                            id="maintenance"
                            data-device_id="<?php echo $device->device_id; ?>"
                            <?= DeviceCache::get($device->device_id)->isUnderMaintenance()
                                ? 'disabled class="btn btn-warning"'
                                : 'class="btn btn-success"'
                            ?>
                            name="maintenance">
                        <?= DeviceCache::get($device->device_id)->isUnderMaintenance()
                            ? '<i class="fa fa-wrench"></i> Device already in Maintenance'
                            : '<i class="fa fa-wrench"></i> Maintenance Mode';
                        ?>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="disable_notify" class="col-sm-2 control-label">Disable alerting</label>
                <div class="col-sm-6">
                    <input id="disable_notify" type="checkbox" name="disable_notify" data-size="small"
                        <?php
                        if ($device->disable_notify) {
                            echo 'checked=checked';
                        }
                        ?> />
                </div>
            </div>
            <div class="form-group">
                <label for="ignore" class="col-sm-2 control-label" title="Tag device to ignore alerts. Alert checks will still run.
However, ignore tag can be read in alert rules.
If `devices.ignore = 0` or `macros.device = 1` condition is is set and ignore alert tag is on, the alert rule won't match.">Ignore alert tag</label>
                <div class="col-sm-6">
                    <input name="ignore" type="checkbox" id="ignore" value="1" data-size="small"
                        <?php
                        if ($device->ignore) {
                            echo 'checked=checked';
                        }
                        ?> />
                </div>
            </div>
            <div class="form-group">
                <label for="ignore_status" class="col-sm-2 control-label" title="Tag device to ignore Status. It will always be shown as online.">Ignore Device Status</label>
                <div class="col-sm-6">
                    <input name="ignore_status" type="checkbox" id="ignore_status" value="1" data-size="small"
                        <?php
                        if ($device->ignore_status) {
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
            function toggleHostnameEdit() {
                document.getElementById('edit-hostname-input').disabled = ! document.getElementById('edit-hostname-input').disabled;
            }
            $('#parent_id').select2({
                width: 'resolve'
            });
        </script>
        <div class="panel panel-default">
            <div class="panel-heading">
        <?php
        [$sizeondisk, $numrrds] = \LibreNMS\Util\File::getFolderSize(Rrd::dirFromHost($device->hostname));
        echo 'Size on Disk: <b>' . \LibreNMS\Util\Number::formatBi($sizeondisk) . '</b> in <b>' . $numrrds . ' RRD files</b>.';
        echo ' | Last polled: <b>' . $device->last_polled . '</b>';
        if ($device->last_discovered) {
            echo ' | Last discovered: <b>' . $device->last_discovered . '</b>';
        }
        ?>
            </div>
        </div>
    </x-device.page>
@endsection
