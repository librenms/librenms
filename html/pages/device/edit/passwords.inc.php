<?php
if ($_POST['editing']) {
    if ($_SESSION['userlevel'] > "7") {
        $updated = 0;


        if ($device['type'] != $vars['type']) {
            $param['type'] = $vars['type'];
            $update_type = true;
        }

        #FIXME needs more sanity checking! and better feedback

        $param['username']  = $vars['username'];
        $param['password']  = $vars['password'];
		$param['enable']  = $vars['enable'];

        $rows_updated = dbUpdate($param, 'devices', '`device_id` = ?', array($device['device_id']));

        if ($rows_updated > 0 || $updated) {
            if ($update_type === true) {
                set_dev_attrib($device, 'override_device_type', true);
            }
            $update_message = "Device record updated.";
            $updated = 1;
            $device = dbFetchRow("SELECT * FROM `devices` WHERE `device_id` = ?", array($device['device_id']));
        } elseif ($rows_updated == 0) {
            $update_message = "Device record unchanged. No update necessary.";
            $updated = -1;
        } else {
            $update_message = "Device record update error.";
        }
        if (isset($_POST['hostname']) && $_POST['hostname'] !== '' && $_POST['hostname'] !== $device['hostname']) {
            if (is_admin()) {
                $result = renamehost($device['device_id'], $_POST['hostname'], 'webui');
                if ($result == "") {
                    print_message("Hostname updated from {$device['hostname']} to {$_POST['hostname']}");
                    echo '
                        <script>
                            var loc = window.location;
                            window.location.replace(loc.protocol + "//" + loc.host + loc.pathname + loc.search);
                        </script>
                    ';
                } else {
                    print_error($result . ".  Does your web server have permission to modify the rrd files?");
                }
            } else {
                print_error('Only administrative users may update the device hostname');
            }
        }
    } else {
        include 'includes/error-no-perm.inc.php';
    }
}

$username  = $device['username'];
$password  = $device['password'];
$enable  = $device['enable'];

if ($updated && $update_message) {
    print_message($update_message);
} elseif ($update_message) {
    print_error($update_message);
}

?>
<h3> Device Settings </h3>
<form id="edit" name="edit" method="post" action="" role="form" class="form-horizontal">
<input type=hidden name="editing" value="yes">
<div class="row">
     <div class="form-group">
        <label for="username" class="col-sm-2 control-label">Username:</label>
        <div class="col-sm-6">
            <textarea id="username" name="username" class="form-control"><?php echo(display($device['username'])); ?></textarea>
        </div>
    </div>
     <div class="form-group">
        <label for="password" class="col-sm-2 control-label">Password:</label>
        <div class="col-sm-6">
            <textarea id="password" name="password" class="form-control"><?php echo(display($device['password'])); ?></textarea>
        </div>
    </div>
     <div class="form-group">
        <label for="enable" class="col-sm-2 control-label">Enable Password:</label>
        <div class="col-sm-6">
            <textarea id="enable" name="enable" class="form-control"><?php echo(display($device['enable'])); ?></textarea>
        </div>
    </div>	
    <div class="col-md-1 col-md-offset-2">
        <button type="submit" name="Submit"  class="btn btn-default"><i class="fa fa-check"></i> Save</button>
    </div>
</div>
</form>
<br />
<?php
print_optionbar_start();
list($sizeondisk, $numrrds) = foldersize(get_rrd_dir($device['hostname']));
echo("Size on Disk: <b>" . formatStorage($sizeondisk) . "</b> in <b>" . $numrrds . " RRD files</b>.");
print_optionbar_end();
?>
