<?php

if ($_POST['editing'])
{
  if ($_SESSION['userlevel'] > "7")
  {
    $ipmi_hostname = mres($_POST['ipmi_hostname']);
    $ipmi_username = mres($_POST['ipmi_username']);
    $ipmi_password = mres($_POST['ipmi_password']);

    if ($ipmi_hostname != '') { set_dev_attrib($device, 'ipmi_hostname', $ipmi_hostname); } else { del_dev_attrib($device, 'ipmi_hostname'); }
    if ($ipmi_username != '') { set_dev_attrib($device, 'ipmi_username', $ipmi_username); } else { del_dev_attrib($device, 'ipmi_username'); }
    if ($ipmi_password != '') { set_dev_attrib($device, 'ipmi_password', $ipmi_password); } else { del_dev_attrib($device, 'ipmi_password'); }

    $update_message = "Device IPMI data updated.";
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

?>

<h3>IPMI settings</h3>

<table cellpadding="0" cellspacing="0">
  <tr>
    <td>
      <form id="edit" name="edit" method="post" action="">
        <input type="hidden" name="editing" value="yes">
        <table width="500" border="0">
          <tr>
            <td width="150"><div align="right">IPMI/BMC Hostname</div></td>
            <td colspan="3"><input name="ipmi_hostname" size="32" value="<?php echo(get_dev_attrib($device,'ipmi_hostname')); ?>" /></td>
          </tr>
          <tr>
            <td><div align="right">IPMI/BMC Username</div></td>
            <td colspan="3"><input name="ipmi_username" size="32" value="<?php echo(get_dev_attrib($device,'ipmi_username')); ?>" /></td>
            </td>
          </tr>
          <tr>
            <td><div align="right">IPMI/BMC Password</div></td>
            <td colspan="3"><input name="ipmi_password" type="password" size="32" value="<?php echo(get_dev_attrib($device,'ipmi_password')); ?>" /></td>
            </td>
          </tr>
          <tr>
            <td></td>
            <td colspan="3">
              <br />
              <input type="submit" name="Submit" value="Save" />
            </td>
          </tr>
          <tr>
            <td colspan="4">
              To disable IPMI polling, please clear the setting fields and click <b>Save</b>.
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
