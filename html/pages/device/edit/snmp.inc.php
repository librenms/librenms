<?php

if ($_POST['editing'])
{
  if ($_SESSION['userlevel'] > "7")
  {
    $community = mres($_POST['community']);
    $snmpver = mres($_POST['snmpver']);
    $transport = mres($_POST['transport']);
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
      'port' => $port,
      'transport' => $transport
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

echo("
<form id='edit' name='edit' method='post' action='' role='form' class='form-horizontal'>
  <input type=hidden name='editing' value='yes'>
  <div class='form-group'>
    <label for='snmpver' class='col-sm-2 control-label'>SNMP Version</label>
    <div class='col-sm-6'>
      <select id='snmpver' name='snmpver' class='form-control'>
        <option value='v1'>v1</option>
        <option value='v2c' " . ($device['snmpver'] == 'v2c' ? 'selected' : '') . ">v2c</option>
        <option value='v3' " . ($device['snmpver'] == 'v3' ? 'selected' : '') . ">v3</option>
      </select>
    </div>
  </div>
  <div class='form-group'>
    <label class='col-sm-3 control-label text-left'><h4><strong>SNMPv1/v2c Configuration</strong></h4></label>
  </div>
  <div class='form-group'>
    <label for='community' class='col-sm-2 control-label'>SNMP Community</label>
    <div class='col-sm-6'>
      <input id='community' class='form-control' name='community' value='" . $device['community'] . "' />
    </div>
  </div>
  <div class='form-group'>
    <label class='col-sm-3 control-label'><h4><strong>SNMPv3 Configuration</strong></h4></label>
  </div>
  <div class='form-group'>
    <label for='authlevel' class='col-sm-2 control-label'>Auth Level</label>
    <div class='col-sm-6'>
      <select id='authlevel' name='authlevel' class='form-control'>
          <option value='NoAuthNoPriv'>NoAuthNoPriv</option>
          <option value='AuthNoPriv' " . ($device['authlevel'] == "authNoPriv" ? 'selected' : '') . ">AuthNoPriv</option>
          <option value='AuthPriv' " . ($device['authlevel'] == "authPriv" ? 'selected' : '') . ">AuthPriv</option>
      </select>
    </div>
  </div>
  <div class='form-group'>
    <label for='authname' class='col-sm-2 control-label'>Auth User Name</label>
    <div class='col-sm-6'>
      <input type='text' id='authname' name='authname' class='form-control' value='" . $device['authname']  . "'>
    </div>
  </div>
  <div class='form-group'>
    <label for='authpass' class='col-sm-2 control-label'>Auth Password</label>
    <div class='col-sm-6'>
      <input type='text' id='authpass' name='authpass' class='form-control' value='" . $device['authpass']  . "'>
    </div>
  </div>
  <div class='form-group'>
    <label for='authalgo' class='col-sm-2 control-label'>Auth Algorithm</label>
    <div class='col-sm-6'>
      <select id='authalgo' name='authalgo' class='form-control'>
        <option value='MD5'>MD5</option>
        <option value='SHA' " . ($device['authalgo'] === "SHA" ? 'selected' : '') . ">SHA</option>
      </select>
    </div>
  </div>
  <div class='form-group'>
    <label for='cryptopass' class='col-sm-2 control-label'>Crypto Password</label>
    <div class='col-sm-6'>
      <input type='text' id='cryptopass' name='cryptopass' class='form-control' value='" . $device['cryptopass']  . "'>
    </div>
  </div>
  <div class='form-group'>
    <label for='cryptoalgo' class='col-sm-2 control-label'>Crypto Algorithm</label>
    <div class='col-sm-6'>
      <select id='cryptoalgo' name='cryptoalgo' class='form-control'>
        <option value='AES'>AES</option>
        <option value='DES' " . ($device['cryptoalgo'] === "DES" ? 'selected' : '') . ">DES</option>
      </select>
    </div>
  </div>
  <div class='form-group'>
    <label class='col-sm-3 control-label'><h4><strong>SNMP Connectivity</strong></h4></label>
  </div>
  <div class='form-group'>
    <label for='transport' class='col-sm-2 control-label'>SNMP Transport</label>
    <div class='col-sm-6'>
      <select id='transport' name='transport' class='form-control'>");

foreach ($config['snmp']['transports'] as $transport)
{
  echo("<option value='".$transport."'");
  if ($transport == $device['transport']) { echo(" selected='selected'"); }
  echo(">".$transport."</option>");
}

echo("  </select>
    </div>
  </div>
  <div class='form-group'>
    <label for='port' class='col-sm-2 control-label'>SNMP Port</label>
    <div class='col-sm-6'>
      <input id='port' name='port' class='form-control' value='" . $device['port'] . "' />
    </div>
  </div>
  <div class='form-group'>
    <label for='timeout' class='col-sm-2 control-label'>SNMP Timeout</label>
    <div class='col-sm-6'>
      <input id='timeout' name='timeout' class='form-control' value='" . ($device['timeout'] ? $device['timeout'] : '') . "' /> <em>(milli)seconds</em>
    </div>
  </div>
  <div class='form-group'>
    <label for='retries' class='col-sm-2 control-label'>SNMP Retries</label>
    <div class='col-sm-6'>
      <input id='retries' name='retries' class='form-control' value='" . ($device['timeout'] ? $device['retries'] : '') . "' />
    </div>
  </div>");

echo('
  <button type="submit" name="Submit" class="btn btn-default">Save</button>
</form>

');

?>
