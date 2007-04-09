#!/usr/bin/php

### Clean up the database removing old IPs and links

<?
include("config.php");
include("includes/functions.php");

$query = "SELECT *,A.id as id FROM ipaddr AS A, interfaces as I, devices as D 
          WHERE A.interface_id = I.interface_id AND I.device_id = D.device_id AND D.status = '1'";

$data = mysql_query($query);
while($row = mysql_fetch_array($data)) {

  $mask = trim(`$ipcalc $row[addr]/$row[cidr] | grep Netmask: | cut -d " " -f 4`);
  $response = trim(`snmpget -v2c -Osq -c $row[community] $row[hostname] ipAdEntIfIndex.$row[addr] | cut -d " " -f 2`);
  $maskcheck = trim(`snmpget -v2c -Osq -c $row[community] $row[hostname] ipAdEntNetMask.$row[addr] | cut -d " " -f 2`);
  if($response == $row['ifIndex'] && $mask == $maskcheck) {
  } else {
    mysql_query("delete from ipaddr where id = '$row[id]'");
    echo("Deleted $row[addr] from $row[hostname]\n");
  }
}

$query = "SELECT * FROM interfaces AS I, devices as D 
          WHERE I.device_id = D.device_id AND D.status = '1'";
$data = mysql_query($query);
while($row = mysql_fetch_array($data)) {
  $index = $row[ifIndex];
  $hostname = $row['hostname'];
  $community = $row['community'];
  $response = trim(`snmpget -v2c -Osq -c $community $hostname ifIndex.$index | cut -d " " -f 2`);
  if($response != $index) {
    mysql_query("DELETE from interfaces where interface_id = '" . $row['interface_id'] . "'");
    mysql_query("DELETE from `adjacencies` WHERE `interface_id` = '" . $row['interface_id'] . "'");
    mysql_query("DELETE from `links` WHERE `src_if` = '" . $row['interface_id'] . "'");
    mysql_query("DELETE from `links` WHERE `dst_if` = '" . $row['interface_id'] . "'");
    mysql_query("DELETE from `ipaddr` WHERE `interface_id` = '" . $row['interface_id'] . "'");
    echo("Removed interface " . $row['ifDescr'] . " from " . $row['hostname'] . "<br />");
  }
}

echo(mysql_result(mysql_query("SELECT COUNT(*) FROM `interfaces`"), 0) . " interfaces at start\n");
$interface_query = mysql_query("SELECT interface_id,device_id FROM `interfaces`");
while ($interface = mysql_fetch_array($interface_query)) {
  $device_id = $interface['device_id'];
  $interface_id = $interface['interface_id'];
  if(mysql_result(mysql_query("SELECT COUNT(*) FROM `devices` WHERE `device_id` = '$device_id'"), 0) == '0') {
    mysql_query("delete from interfaces where `interface_id` = '$interface_id'");
    echo("Deleting if $interface_id \n");
  } 
}
echo(mysql_result(mysql_query("SELECT COUNT(*) FROM `interfaces`"), 0) . " interfaces at end\n");

echo(mysql_result(mysql_query("SELECT COUNT(id) FROM `links`"), 0) . " links at start\n");
$link_query = mysql_query("SELECT id,src_if,dst_if FROM `links`");
while ($link = mysql_fetch_array($link_query)) {
  $id = $link['id'];
  $src = $link['src_if'];
  $dst = $link['dst_if'];
  if(mysql_result(mysql_query("SELECT COUNT(interface_id) FROM `interfaces` WHERE `interface_id` = '$src'"), 0) == '0' || mysql_result(mysql_query("SELECT COUNT(*) FROM `interfaces` WHERE `interface_id` = '$dst'"), 0) == '0') {
    mysql_query("delete from links where `id` = '$id'");
    echo("Deleting link $id \n");
  }
}
echo(mysql_result(mysql_query("SELECT COUNT(id) FROM `links`"), 0) . " links at end\n");

echo(mysql_result(mysql_query("SELECT COUNT(adj_id) FROM `adjacencies`"), 0) . " adjacencies at start\n");
$link_query = mysql_query("SELECT * FROM `adjacencies` AS A, `interfaces` AS I, `devices` AS D, networks AS N WHERE I.interface_id = A.interface_id AND D.id = I.device_id AND N.id = A.network_id;");
while ($link = mysql_fetch_array($link_query)) {
  $id = $link['adj_id'];
  $netid = $link['network_id'];
  $ifid = $link['interface_id'];
  if(mysql_result(mysql_query("SELECT COUNT(*) FROM `interfaces` WHERE `interface_id` = '$ifid'"), 0) == '0' || mysql_result(mysql_query("SELECT COUNT(id) FROM `networks` WHERE `id` = '$netid'"), 0) == '0') {
    $remove = 1;
    echo("Removed Interface!\n");
  }

  echo($link['if'] . " (" . $link['interface_id'] . ") -> " . $link['cidr'] . " \n");

  $q = mysql_query("SELECT * FROM `ipaddr` WHERE `interface_id` = '" . $link['interface_id'] . "'");

  if($link['cidr'] == "") { $remove = 1; echo("Broken CIDR entry!"); }


  if($remove) {
    mysql_query("delete from adjacencies where `adj_id` = '$id'");
    echo("Deleting link $id \n");
  }
  unset($remove);
}
echo(mysql_result(mysql_query("SELECT COUNT(adj_id) FROM `adjacencies`"), 0) . " adjacencies at end\n");


?>
