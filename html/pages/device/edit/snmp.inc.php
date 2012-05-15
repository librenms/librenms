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
    $v3 = array (
      'authlevel' => mres($_POST['authlevel']),
      'authname' => mres($_POST['authname']),
      'authpass' => mres($_POST['authpass']),
      'authalgo' => mres($_POST['authalgo']),
      'cryptopass' => mres($_POST['cryptopass']),
      'cryptoalgo' => mres($_POST['cryptoalgo'])
    );

    #FIXME needs better feedback
    $update = array(
      'community' => $community,
      'snmpver' => $snmpver,
      'port' => $port
    );

    if ($_POST['timeout']) { $update['timeout'] = $timeout; }
      else { $update['timeout'] = array('NULL'); }
    if ($_POST['retries']) { $update['retries'] = $retries; }
      else { $update['retries'] = array('NULL'); }

    $update = array_merge($update, $v3);

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
      <td><div align=right>SNMP Version</div></td>
      <td>
        <select name='snmpver'>
          <option value='v1'>v1</option>
          <option value='v2c' " . ($device['snmpver'] == 'v2c' ? 'selected' : '') . ">v2c</option>
          <option value='v3' " . ($device['snmpver'] == 'v3' ? 'selected' : '') . ">v3</option>
        </select>
      </td>
    </tr>
  <div id='snmpv12'>
  <!-- To be able to hide it -->
    <tr>
      <td colspan='2'><strong>SNMPv1/v2c Configuration</strong></td>
    </tr>
    <tr>
      <td width='150'><div align='right'>SNMP Community</div></td>
      <td><input name='community' size='32' value='" . $device['community'] . "' />
      </td>
    </tr>
  </div>
  <div id='snmpv3'>
  <!-- To be able to hide it -->
    <tr>
      <td colspan='2'><strong>SNMPv3 Configuration</strong></td>
    </tr>
    <tr>
      <td width='150'><div align='right'>Auth Level</div></td>
      <td>
        <select name='authlevel'>
          <option value='NoAuthNoPriv'>NoAuthNoPriv</option>
          <option value='AuthNoPriv' " . ($device['authlevel'] == "authNoPriv" ? 'selected' : '') . ">AuthNoPriv</option>
          <option value='AuthPriv' " . ($device['authlevel'] == "authPriv" ? 'selected' : '') . ">AuthPriv</option>
        </select>
      </td>
    </tr>
    <tr>
      <td width='150'><div align='right'>Auth User Name</div></td>
      <td><input type='text' name='authname' size='32' value='" . $device['authname']  . "'></td>
    </tr>
    <tr>
      <td width='150'><div align='right'>Auth Password</div></td>
      <td><input type='text' name='authpass' size='32' value='" . $device['authpass']  . "'></td>
    </tr>
    <tr>
      <td width='150'><div align='right'>Auth Algorithm</strong></td>
      <td>
        <select name='authalgo'>
          <option value='MD5'>MD5</option>
          <option value='SHA' " . ($device['authalgo'] === "SHA" ? 'selected' : '') . ">SHA</option>
        </select>
      </td>
    </tr>
    <tr>
      <td width='150'><div align='right'>Crypto Password</div></td>
      <td><input type='text' name='cryptopass' size='32' value='" . $device['cryptopass']  . "'></td>
    </tr>
    <tr>
      <td width='150'><div align='right'>Crypto Algorithm</div></td>
      <td>
        <select name='cryptoalgo'>
          <option value='AES'>AES</option>
          <option value='DES' " . ($device['cryptoalgo'] === "DES" ? 'selected' : '') . ">DES</option>
        </select>
      </td>
    </tr>
  </div>
    <tr>
      <td colspan='2'><strong>SNMP Connectivity</strong></td>
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
      <td><div align='right'>SNMP Port</div></td>
      <td><input name='port' size='32' value='" . $device['port'] . "' />
      </td>
    </tr>
    <tr>
      <td><div align='right'>SNMP Timeout</div></td>
      <td><input name='timeout' size='32' value='" . ($device['timeout'] ? $device['timeout'] : '') . "' />&nbsp;
      <em>(milli)seconds</em>
      </td>
    </tr>
    <tr>
      <td><div align='right'>SNMP Retries</div></td>
      <td colspan='3'><input name='retries' size='32' value='" . ($device['timeout'] ? $device['retries'] : '') . "' />
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
