<?php

if ($_POST['editing'])
{
  if ($_SESSION['userlevel'] > "7")
  {
    $override_sysContact_bool = mres($_POST['override_sysContact']);
    if (isset($_POST['sysContact'])) { $override_sysContact_string  = mres($_POST['sysContact']); }
    $disable_notify  = mres($_POST['disable_notify']);

    if ($override_sysContact_bool) { set_dev_attrib($device, 'override_sysContact_bool', '1'); } else { del_dev_attrib($device, 'override_sysContact_bool'); }
    if (isset($override_sysContact_string)) { set_dev_attrib($device, 'override_sysContact_string', $override_sysContact_string); };
    if ($disable_notify) { set_dev_attrib($device, 'disable_notify', '1'); } else { del_dev_attrib($device, 'disable_notify'); }

    $update_message = "Device alert settings updated.";
    $updated = 1;
  }
  else
  {
    include("includes/error-no-perm.inc.php");
  }
}

if ($updated && $update_message)
{
  print_message($update_message);
} elseif ($update_message) {
  print_error($update_message);
}

$override_sysContact_bool = get_dev_attrib($device,'override_sysContact_bool');
$override_sysContact_string = get_dev_attrib($device,'override_sysContact_string');
$disable_notify = get_dev_attrib($device,'disable_notify');
?>

<h3>Alert settings</h3>

<table cellpadding="0" cellspacing="0">
  <tr>
    <td>
      <form id="edit" name="edit" method="post" action="">
        <input type="hidden" name="editing" value="yes">
        <table width="500" border="0">
          <tr>
            <td width="50"><div style="padding-right: 5px; text-align: right"><input onclick="edit.sysContact.disabled=!edit.override_sysContact.checked" type="checkbox" name="override_sysContact"<?php if ($override_sysContact_bool) { echo(' checked="1"'); } ?> /></div></td>
            <td width="150">Override sysContact:</td>
            <td><input name="sysContact" size="32"<?php if (!$override_sysContact_bool) { echo(' disabled="1"'); } ?> value="<?php echo($override_sysContact_string); ?>" /></td>
          </tr>
          <tr>
            <td width="50"><div style="padding-right: 5px; text-align: right"><input type="checkbox" name="disable_notify"<?php if ($disable_notify) { echo(' checked="1"'); } ?> /></div></td>
            <td colspan="2">Disable all alerting for this host</td>
          </tr>
          <tr>
            <td></td>
            <td>
              <br />
              <input type="submit" name="Submit" value="Save" />
            </td>
          </tr>
        </table>
        <br />
      </form>
    </td>
    <td width="50"></td>
    <td></td>
  </tr>
</table>
