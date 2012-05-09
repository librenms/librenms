<?php

if ($_SESSION['userlevel'] < 10)
{
  include("includes/error-no-perm.inc.php");

  exit;
}

echo("<h2>Add Device</h2>");

if ($_POST['hostname'])
{
  if ($_SESSION['userlevel'] > '5')
  {
    $hostname = mres($_POST['hostname']);

    if ($_POST['snmpver'] === "v2c" or $_POST['snmpver'] === "v1")
    {
      if ($_POST['community'])
      {
        $config['snmp']['community'] = array($_POST['community']);
      }

      $snmpver = mres($_POST['snmpver']);
      if ($_POST['port']) { $port = mres($_POST['port']); } else { $port = "161"; }
      print_message("Adding host $hostname communit" . (count($config['snmp']['community']) == 1 ? "y" : "ies") . " "  . implode(', ',$config['snmp']['community']) . " port $port");
    }
    elseif ($_POST['snmpver'] === "v3")
    {
      $v3 = array (
        'authlevel' => mres($_POST['authlevel']),
        'authname' => mres($_POST['authname']),
        'authpass' => mres($_POST['authpass']),
        'authalgo' => mres($_POST['authalgo']),
        'cryptopass' => mres($_POST['cryptopass']),
        'cryptoalgo' => mres($_POST['cryptoalgo']),
      );

      array_push($config['snmp']['v3'], $v3);
      
      $snmpver = "v3";

      if ($_POST['port']) { $port = mres($_POST['port']); } else { $port = "161"; }
      print_message("Adding SNMPv3 host $hostname port $port");
    }
    else
    {
      print_error("Unsupported SNMP Version. There was a dropdown menu, how did you reach this error ?");
    }
    $result = addHost($hostname, $snmpver, $port);
    if ($result)
    {
      print_message("Device added ($result)");
    }
  } else {
    print_error("You don't have the necessary privileges to add hosts.");
  }
}

$pagetitle[] = "Add host";

?>

<form name="form1" method="post" action="">
  <p>Devices will be checked for Ping and SNMP reachability before being probed. Only devices with recognised OSes will be added.</p>

  <div style="padding: 10px; background: #f0f0f0;">
    <table cellpadding=2px>
      <tr>
        <td><strong>Hostname</strong></td>
        <td><input type="text" name="hostname" size="32"></td>
      </tr>
      <tr>
        <td><strong>SNMP Version</strong></td>
        <td>
          <select name="snmpver">
            <option value="v1">v1</option>
            <option value="v2c" selected>v2c</option>
            <option value="v3">v3</option>
          </select>
          &nbsp;<strong>Port</strong> <input type="text" name="port" size="16">
        </td>
      </tr>
      <tr>
        <td colspan=2><strong>SNMPv1/2c Configuration</strong></td>
      </tr>
      <tr>
        <td><strong>Community</strong></td>
        <td><input type="text" name="community" size="32"></td>
      </tr>
      <tr>
        <td colspan=2><strong>SNMPv3 Configuration</strong></td>
      </tr>
      <tr>
        <td><strong>Auth Level</strong></td>
        <td>
          <select name="authlevel">
            <option value="noAuthNoPriv" selected>NoAuthNoPriv</option>
            <option value="authNoPriv">AuthNoPriv</option>
            <option value="authPriv">AuthPriv</option>
          </select>
        </td>
      </tr>
      <tr>
        <td><strong>Auth User Name</strong></td>
        <td><input type="text" name="authname" size="32"></td>
      </tr>
      <tr>
        <td><strong>Auth Password</strong></td>
        <td><input type="text" name="authpass" size="32"></td>
      </tr>
      <tr>
        <td><strong>Auth Algorithm</strong></td>
        <td>
          <select name="authalgo">
            <option value="MD5" selected>MD5</option>
            <option value="SHA1">SHA1</option>
          </select>
        </td>
      </tr>
      <tr>
        <td><strong>Crypto Password</strong></td>
        <td><input type="text" name="cryptopass" size="32"></td>
      </tr>
      <tr>
        <td><strong>Crypto Algorithm</strong></td>
        <td>
          <select name="cryptoalgo">
            <option value="AES" selected>AES</option>
            <option value="DES">DES</option>
          </select>
        </td>
      </tr>
      <tr>
        <td></td><td><input type="submit" class="submit" name="Submit" value="Add Host"></td>
      </tr>
    </table>
  </div>
</form>
