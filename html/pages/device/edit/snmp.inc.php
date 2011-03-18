<?php

if ($_POST['editing'])
{
  if ($_SESSION['userlevel'] > "7")
  {
    $community = mres($_POST['community']);
    $snmpver = mres($_POST['snmpver']);
    $port = mres($_POST['port']);
    $timeout = mres($_POST['timeout']);
    $retries = mres($_POST['retries']);

    #FIXME needs more sanity checking! and better feedback
    $sql = "UPDATE `devices` SET `community` = '" . $community . "', `snmpver` = '" . $snmpver . "', `port` = '$port', ";
    if ($timeout) { $sql .= "`timeout` = '$timeout', "; } else { $sql .= "`timeout` = NULL, "; }
    if ($retries) { $sql .= "`retries` = '$retries'"; } else { $sql .= "`retries` = NULL"; }
    $sql .= " WHERE `device_id` = '".$device['device_id']."'";
    $query = mysql_query($sql);

    $rows_updated = mysql_affected_rows();

    if ($rows_updated > 0)
    {
      $update_message = mysql_affected_rows() . " Device record updated.";
      $updated = 1;
    } elseif ($rows_updated = '-1') {
      $update_message = "Device record unchanged. No update necessary.";
      $updated = -1;
    } else {
      $update_message = "Device record update error.";
      $updated = 0;
    }
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

echo("<table cellpadding=0 cellspacing=0><tr><td>

<form id='edit' name='edit' method='post' action=''>
  <input type=hidden name='editing' value='yes'>
  <table width='400' border='0'>
    <tr>
      <td width='150'><div align='right'>SNMP Community</div></td>
      <td><input name='community' size='20' value='" . $device['community'] . "'></input>
      </td>
    </tr>
    <tr>
    <td><div align=right>SNMP Version</div></td>
    <td><select name='snmpver'>
          <option value='v1'>v1</option>
          <option value='v2c'" . ($device['snmpver'] == 'v2c' ? 'selected=selected' : '') . ">v2c</option>
        </select>
      </td>
    </tr>
    <tr>
      <td><div align='right'>SNMP Port</div></td>
      <td><input name='port' size='20' value='" . $device['port'] . "'></input>
      </td>
    </tr>
    <tr>
      <td><div align='right'>SNMP Transport</div></td>
      <td>
        <select name='transport'>");

foreach ($config['snmp']['transports'] as $transport)
{
  echo ("<option value='".$transport."'");
  if ($transport == $device['transport']) { echo (" selected='selected'"); }
  echo (">".$transport."</option>");
}

echo("  </select>
      </td>
    </tr>
    <tr>
      <td><div align='right'>SNMP Timeout</div></td>
      <td><input name='timeout' size='20' value='" . ($device['timeout'] ? $device['timeout'] : '') . "'></input>&nbsp;
      <em>seconds</em>
      </td>
    </tr>
    <tr>
      <td><div align='right'>SNMP Retries</div></td>
      <td colspan='3'><input name='retries' size='20' value='" . ($device['timeout'] ? $device['retries'] : '') . "'></input>
      </td>
    </tr>");

echo('
  </table>
  <input type="submit" name="Submit" value="Save" />
  <label><br />
  </label>
</form>

</td>
<td width="50"></td><td></td></tr></table>');

?>