<?php

if ($_POST['editing']) {
    if (Auth::user()->hasGlobalAdmin()) {
        $ipmi_hostname = $_POST['ipmi_hostname'];
        $ipmi_port = (int) $_POST['ipmi_port'];
        $ipmi_username = $_POST['ipmi_username'];
        $ipmi_password = $_POST['ipmi_password'];

        if ($ipmi_hostname != '') {
            set_dev_attrib($device, 'ipmi_hostname', $ipmi_hostname);
        } else {
            del_dev_attrib($device, 'ipmi_hostname');
        }

        if ($ipmi_port != '') {
            set_dev_attrib($device, 'ipmi_port', $ipmi_port);
        } else {
            set_dev_attrib($device, 'ipmi_port', '623'); // Default port
        }

        if ($ipmi_username != '') {
            set_dev_attrib($device, 'ipmi_username', $ipmi_username);
        } else {
            del_dev_attrib($device, 'ipmi_username');
        }

        if ($ipmi_password != '') {
            set_dev_attrib($device, 'ipmi_password', $ipmi_password);
        } else {
            del_dev_attrib($device, 'ipmi_password');
        }

        $update_message = 'Device IPMI data updated.';
        $updated = 1;
    } else {
        include 'includes/html/error-no-perm.inc.php';
    }//end if
}//end if

if ($updated && $update_message) {
    print_message($update_message);
} elseif ($update_message) {
    print_error($update_message);
}

?>

<h3>IPMI settings</h3>

<form id="edit" name="edit" method="post" action="" role="form" class="form-horizontal">
<?php echo csrf_field() ?>
<input type="hidden" name="editing" value="yes">
  <div class="form-group">
    <label for="ipmi_hostname" class="col-sm-2 control-label">IPMI/BMC Hostname</label>
    <div class="col-sm-6">
      <input id="ipmi_hostname" name="ipmi_hostname" class="form-control" value="<?php echo get_dev_attrib($device, 'ipmi_hostname'); ?>" />
    </div>
  </div>
  <div class="form-group">
    <label for="ipmi_port" class="col-sm-2 control-label">IPMI/BMC Port</label>
    <div class="col-sm-6">
      <input id="ipmi_port" name="ipmi_port" class="form-control" value="<?php echo get_dev_attrib($device, 'ipmi_port'); ?>" placeholder="623" />
    </div>
  </div>
  <div class="form-group">
    <label for="ipmi_username" class="col-sm-2 control-label">IPMI/BMC Username</label>
    <div class="col-sm-6">
      <input id="ipmi_username" name="ipmi_username" class="form-control" value="<?php echo get_dev_attrib($device, 'ipmi_username'); ?>" />
    </div>
  </div>
  <div class="form-group">
    <label for="impi_password" class="col-sm-2 control-label">IPMI/BMC Password</label>
    <div class="col-sm-6">
      <input id="ipmi_password" name="ipmi_password" type="password" class="form-control" value="<?php echo get_dev_attrib($device, 'ipmi_password'); ?>" />
    </div>
  </div>
  <div class="row">
    <div class="col-md-1 col-md-offset-2">
        <button type="submit" name="Submit"  class="btn btn-default"><i class="fa fa-check"></i> Save</button>
    </div>
  </div>
  <br><br>
  <div class="alert alert-info" role="alert">
    <p>To disable IPMI polling, please clear the setting fields and click <b>Save</b>.</p>
  </div>
</form>
