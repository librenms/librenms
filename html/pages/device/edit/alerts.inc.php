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
        $override_sysContact_bool = mres($_POST['override_sysContact']);
        if (isset($_POST['sysContact'])) {
            $override_sysContact_string = mres($_POST['sysContact']);
        }

        $disable_notify = mres($_POST['disable_notify']);

        if ($override_sysContact_bool) {
            set_dev_attrib($device, 'override_sysContact_bool', '1');
        } else {
            del_dev_attrib($device, 'override_sysContact_bool');
        }

        if (isset($override_sysContact_string)) {
            set_dev_attrib($device, 'override_sysContact_string', $override_sysContact_string);
        };
        if ($disable_notify) {
            set_dev_attrib($device, 'disable_notify', '1');
        } else {
            del_dev_attrib($device, 'disable_notify');
        }

        $update_message = 'Device alert settings updated.';
        $updated        = 1;
    } else {
        include 'includes/error-no-perm.inc.php';
    }//end if
}//end if

$override_sysContact_bool   = get_dev_attrib($device, 'override_sysContact_bool');
$override_sysContact_string = get_dev_attrib($device, 'override_sysContact_string');
$disable_notify             = get_dev_attrib($device, 'disable_notify');

print_optionbar_start();
echo "<span style='font-weight: bold;'>Alert settings</span>";
echo "<div class='pull-right'>";
echo '<button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#create-alert" data-device_id="'.$device['device_id'].'"><i class="fa fa-plus"></i> Create new alert rule</button>';
echo "</div>";
print_optionbar_end();

if ($updated && $update_message) {
    print_message($update_message);
} elseif ($update_message) {
    print_error($update_message);
}
?>


<div class="row">
    <div class="col-md-12">
        <span id="message"></span>
    </div>
</div>



<form id="edit" name="edit" method="post" action="" role="form" class="form-horizontal">
  <input type="hidden" name="editing" value="yes">
  <div class="form-group">
    <label for="override_sysContact" class="col-sm-3 control-label">Override sysContact:</label>
    <div class="col-sm-6">
      <input onclick="edit.sysContact.disabled=!edit.override_sysContact.checked" type="checkbox" id="override_sysContact" name="override_sysContact"
<?php
if ($override_sysContact_bool) {
    echo ' checked="1"';
};
?>
 />
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-3">
    </div>
    <div class="col-sm-6">
      <input id="sysContact" class="form-control" name="sysContact" size="32"
<?php
if (!$override_sysContact_bool) {
    echo ' disabled="1"';
};
?>
 value="<?php echo $override_sysContact_string; ?>" />
    </div>
  </div>
  <div class="form-group">
    <label for="disable_notify" class="col-sm-3 control-label">Disable all alerting for this host: </label>
    <div class="col-sm-6">
      <input id="disable_notify" type="checkbox" name="disable_notify"
<?php
if ($disable_notify) {
    echo ' checked="1"';
};
?>
 />
    </div>
  </div>
<div class="row">
    <div class="col-md-1 col-md-offset-3">
        <button type="submit" name="Submit" class="btn btn-success"><i class="fa fa-check"></i> Save</button>
    </div>
</div>
<br>
</form>

<?php
require_once 'includes/modal/new_alert_rule.inc.php';
?>

