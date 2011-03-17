<?php

if ($_SESSION['userlevel'] < 10)
{
  # FIXME generic box?
  echo("<span class='alert'>You are not permitted to perform this function</span>");
  exit;
}

echo("<h2>Add Device</h2>");

if ($_POST['hostname'] && $_POST['community'])
{
  if ($_SESSION['userlevel'] > '5')
  {
    $hostname = mres($_POST['hostname']);
    $community = mres($_POST['community']);
    $snmpver = mres($_POST['snmpver']);
    if ($_POST['port']) { $port = mres($_POST['port']); } else { $port = "161"; }
    echo("<p class='messagebox'>");
    echo("Adding host $hostname community $community port $port</p>");
    $result = addHost($hostname, $community, $snmpver, $port);
    echo("</p>");
  } else {
    echo("<p class='errorbox'><b>Error:</b> You don't have the necessary privileges to add hosts.</p>");
  }
} elseif ($_POST['hostname'] && !$_POST['community'] ) {
  echo("<p class='errorbox'><b>Error:</b> A community string is required.</p>");
} elseif (!$_POST['hostname'] && $_POST['community'] ) {
  echo("<p class='errorbox'><b>Error:</b> A hostname is required.</p>");
}

?>

<form name="form1" method="post" action="<?php echo($config['base_url']);  ?>/addhost/">
  <p>Devices will be checked for Ping and SNMP reachability before being probed. Only devices with recognised OSes will be added.</p>

 <div style="padding: 10px; background: #f0f0f0;">
  <table cellpadding=2px>
  <tr>
    <td><strong>Hostname</strong></td>
    <td><input type="text" name="hostname" size="32"></td>
  </tr>
    <td><strong>Community</strong></td>
    <td><input type="text" name="community" size="32"></td>
  </tr>
  <tr>
    <td><strong>SNMP Version</strong></td>
    <td><select name="snmpver">
          <option value="v1">v1</option>
          <option value="v2c" selected>v2c</option>
        </select>
	&nbsp;<strong>Port</strong> <input type="text" name="port" size="16">
    </td>
  </tr>
  <tr>
    <td></td><td><input type="submit" class="submit" name="Submit" value="Add Host"></td>
  </tr>
  </table>
 </div>
</form>
