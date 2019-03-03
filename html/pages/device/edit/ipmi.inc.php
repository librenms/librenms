<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       http://librenms.org
 * @copyright  2019 LibreNMS
 * @author     LibreNMS Contributors
*/

use LibreNMS\Authentication\LegacyAuth;

if ($_POST['editing']) {
    if (LegacyAuth::user()->hasGlobalAdmin()) {
        $ipmi_hostname = mres($_POST['ipmi_hostname']);
        $ipmi_username = mres($_POST['ipmi_username']);
        $ipmi_password = mres($_POST['ipmi_password']);

        if ($ipmi_hostname != '') {
            set_dev_attrib($device, 'ipmi_hostname', $ipmi_hostname);
        } else {
            del_dev_attrib($device, 'ipmi_hostname');
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
        include 'includes/error-no-perm.inc.php';
    }//end if
}//end if

print_optionbar_start();
echo "<span style='font-weight: bold;'>IPMI settings</span>";
echo '<div class="pull-right">';
echo '<span class="label label-danger">To disable IPMI polling, please clear the setting fields and click <b>Save</b></span>';
echo '</div>';
print_optionbar_end();

if ($updated && $update_message) {
    print_message($update_message);
} elseif ($update_message) {
    print_error($update_message);
}
?>

<form id="edit" name="edit" method="post" action="" role="form" class="form-horizontal">
    <input type="hidden" name="editing" value="yes">
    <div class="form-group">
        <label for="ipmi_hostname" class="col-sm-2 control-label">IPMI/BMC Hostname</label>
        <div class="col-sm-6">
            <input id="ipmi_hostname" name="ipmi_hostname" class="form-control" value="<?php echo get_dev_attrib($device, 'ipmi_hostname'); ?>"/>
        </div>
    </div>
    <div class="form-group">
        <label for="ipmi_username" class="col-sm-2 control-label">IPMI/BMC Username</label>
        <div class="col-sm-6">
            <input id="ipmi_username" name="ipmi_username" class="form-control" value="<?php echo get_dev_attrib($device, 'ipmi_username'); ?>"/>
        </div>
    </div>
    <div class="form-group">
        <label for="impi_password" class="col-sm-2 control-label">IPMI/BMC Password</label>
        <div class="col-sm-6">
            <input id="ipmi_password" name="ipmi_password" type="password" class="form-control" value="<?php echo get_dev_attrib($device, 'ipmi_password'); ?>"/>
        </div>
    </div>
    <div class="row">
        <div class="col-md-1 col-md-offset-2">
            <button type="submit" name="Submit" class="btn btn-success"><i class="fa fa-check"></i> Save</button>
        </div>
    </div>
    <br>
</form>
