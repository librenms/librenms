<?php

if($_SESSION['userlevel'] < 10) { 

echo("<span class='alert'>You are not permitted to perform this function</span>");
exit;

}

echo("<h1>Add Device</h1>");

if($_POST['hostname'] && $_POST['community']) {
  if($_SESSION['userlevel'] > '5') {
    $hostname = $_POST['hostname'];
    $community = $_POST['community'];
    $snmpver = $_POST['snmpver'];
    echo("<p class='messagebox'>");
    echo("Adding host $hostname community $community</p>");
    $result = addHost($hostname, $community, $snmpver);  
    echo("$result");
    echo("</p>");
  } else {
    echo("<p class='errorbox'><b>Error:</b> You don't have the necessary privileges to add hosts.</p>");
  }
} elseif ( $_POST['hostname'] && !$_POST['community'] ) {
  echo("<p class='errorbox'><b>Error:</b> A community string is required.</p>");
} elseif ( !$_POST['hostname'] && $_POST['community'] ) {
echo("<p class='errorbox'><b>Error:</b> A hostname is required.</p>");
}

?>

<form name="form1" method="post" action="/?page=addhost">
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
    </td>
  </tr>
  <tr>
    <td></td><td><input type="submit" name="Submit" value="Add Host"></td>
  </tr>   
  </table>
 </div>
</form>
