<?php

if($_POST['hostname'] && $_POST['community']) {
  if($_SESSION['userlevel'] > '5') {
    $hostname = $_POST['hostname'];
    $community = $_POST['community'];
    $snmpver = $_POST['snmpver'];
    echo("<p class='messagebox'>");
    echo("Adding host $hostname community $community</p>");
    addHost($hostname, $community, $snmpver);  
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
  <h1>Add Host</h1>
  <br />
  <p>Hostname: 
    <input type="text" name="hostname">
    <br>
    Community: 
    <input type="text" name="community">
    <br>
      SNMP Version: <select name="snmpver">
      <option value="v1">v1</option>
      <option value="v2c">v2c</option>
    </select>
  </p>
  <p>
    <input type="submit" name="Submit" value="Add Host">
</p>
</form>
