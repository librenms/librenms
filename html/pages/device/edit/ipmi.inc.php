<?php

if ($_POST['editing'])
{
  if ($_SESSION['userlevel'] > "7")
  {
    include("includes/device-ipmi-edit.inc.php");
  }
}

$device = mysql_fetch_assoc(mysql_query("SELECT * FROM `devices` WHERE `device_id` = '".$device['device_id']."'"));
$descr  = $device['purpose'];

if ($updated && $update_message)
{
  print_message($update_message);
} elseif ($update_message) {
  print_error($update_message);
}

?>

<table cellpadding=0 cellspacing=0>
  <tr>
    <td>
      <form id="edit" name="edit" method="post" action="">
        <input type=hidden name="editing" value="yes">
        <table width="500" border="0">
          <tr>
            <td width="150"><div align="right">IPMI/BMC Hostname</div></td>
            <td colspan="3"><input name="ipmi_hostname" size="32" value="<?php echo get_dev_attrib($device,'ipmi_hostname'); ?>"></input></td>
          </tr>
          <tr>
            <td><div align="right">IPMI/BMC Username</div></td>
            <td colspan="3"><input name="ipmi_username" size="32" value="<?php echo get_dev_attrib($device,'ipmi_username'); ?>"></input></td>
            </td>
          </tr>
          <tr>
            <td><div align="right">IPMI/BMC Password</div></td>
            <td colspan="3"><input name="ipmi_password" type="password" size="32" value="<?php echo get_dev_attrib($device,'ipmi_password'); ?>"></input></td>
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
