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
    $update = array('community' => $_POST['community'], 'snmpver' => $_POST['snmpver'], 'port' => $_POST['port']);
    if ($_POST['timeout']) { $update['timeout'] = $_POST['timeout']; } else { $update['timeout'] = array(NULL); }
    if ($_POST['retries'])  { $update['retries'] = $_POST['retries']; } else { $update['retries'] = array(NULL); }

    $rows_updated = dbUpdate($update, 'devices', '`device_id` = ?',array($device['device_id']));

    if ($rows_updated > 0)
    {
      $update_message = $rows_updated . " Device record updated.";
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

$device = dbFetchRow("SELECT * FROM `devices` WHERE `device_id` = ?", array($device['device_id']));
$descr  = $device['purpose'];

if ($updated && $update_message)
{
  print_message($update_message);
} elseif ($update_message) {
  print_error($update_message);
}

echo("<br /><table cellpadding=0 cellspacing=0><tr><td>

<form id='edit' name='edit' method='post' action=''>
  <input type=hidden name='editing' value='yes'>
  <table width='500' border='0'>
    <tr>
      <td width='150'><div align='right'>SNMP Community</div></td>
      <td><input name='community' size='20' value='" . $device['community'] . "' />
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
      <td><input name='port' size='20' value='" . $device['port'] . "' />
      </td>
    </tr>
    <tr>
      <td><div align='right'>SNMP Transport</div></td>
      <td>
        <select name='transport'>");

foreach ($config['snmp']['transports'] as $transport)
{
  echo("<option value='".$transport."'");
  if ($transport == $device['transport']) { echo(" selected='selected'"); }
  echo(">".$transport."</option>");
}

echo("  </select>
      </td>
    </tr>
    <tr>
      <td><div align='right'>SNMP Timeout</div></td>
      <td><input name='timeout' size='20' value='" . ($device['timeout'] ? $device['timeout'] : '') . "' />&nbsp;
      <em>(milli)seconds</em>
      </td>
    </tr>
    <tr>
      <td><div align='right'>SNMP Retries</div></td>
      <td colspan='3'><input name='retries' size='20' value='" . ($device['timeout'] ? $device['retries'] : '') . "' />
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
