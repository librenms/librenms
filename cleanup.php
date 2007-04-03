#!/usr/bin/php

### Clean up the database removing old IPs and links

<?
include("config.php");
include("includes/functions.php");

$query = "SELECT *,A.id as id FROM ipaddr AS A, interfaces as I, devices as D 
          WHERE A.interface_id = I.id AND I.host = D.id AND D.status = '1' AND I.id LIKE '%$argv[1]'";

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

$query = "SELECT *, I.id as id FROM interfaces AS I, devices as D 
          WHERE I.host = D.id AND D.status = '1'";
$data = mysql_query($query);
while($row = mysql_fetch_array($data)) {
  $id = $row['id'];
  $index = $row[ifIndex];
  $hostname = $row['hostname'];
  $community = $row['community'];
  $response = trim(`snmpget -v2c -Osq -c $community $hostname ifIndex.$index | cut -d " " -f 2`);
  if($response != $index) {
    mysql_query("delete from interfaces where id = '$id'");
    echo("Deleted $row[if] from $hostname\n");
  }
}

echo(mysql_result(mysql_query("SELECT COUNT(*) FROM `interfaces`"), 0) . " interfaces at start\n");
$interface_query = mysql_query("SELECT id,host FROM `interfaces`");
while ($interface = mysql_fetch_array($interface_query)) {
  $host = $interface['host'];
  $id = $interface['id'];
  if(mysql_result(mysql_query("SELECT COUNT(*) FROM `devices` WHERE `id` = '$host'"), 0) == '0') {
    mysql_query("delete from interfaces where `id` = '$id'");
    echo("Deleting if $id \n");
  } 
}
echo(mysql_result(mysql_query("SELECT COUNT(*) FROM `interfaces`"), 0) . " interfaces at end\n");

echo(mysql_result(mysql_query("SELECT COUNT(id) FROM `links`"), 0) . " links at start\n");
$link_query = mysql_query("SELECT id,src_if,dst_if FROM `links`");
while ($link = mysql_fetch_array($link_query)) {
  $id = $link['id'];
  $src = $link['src_if'];
  $dst = $link['dst_if'];
  if(mysql_result(mysql_query("SELECT COUNT(id) FROM `interfaces` WHERE `id` = '$src'"), 0) == '0' || mysql_result(mysql_query("SELECT COUNT(id) FROM `interfaces` WHERE `id` = '$dst'"), 0) == '0') {
    mysql_query("delete from links where `id` = '$id'");
    echo("Deleting link $id \n");
  }
}
echo(mysql_result(mysql_query("SELECT COUNT(id) FROM `links`"), 0) . " links at end\n");

echo(mysql_result(mysql_query("SELECT COUNT(adj_id) FROM `adjacencies`"), 0) . " adjacencies at start\n");
$link_query = mysql_query("SELECT * FROM `adjacencies`");
while ($link = mysql_fetch_array($link_query)) {
  $id = $link['adj_id'];
  $netid = $link['network_id'];
  $ifid = $link['interface_id'];
  if(mysql_result(mysql_query("SELECT COUNT(id) FROM `interfaces` WHERE `id` = '$ifid'"), 0) == '0' || mysql_result(mysql_query("SELECT COUNT(id) FROM `networks` WHERE `id` = '$netid'"), 0) == '0') {
    mysql_query("delete from adjacencies where `adj_id` = '$id'");
    echo("Deleting link $id \n");
  }
}
echo(mysql_result(mysql_query("SELECT COUNT(adj_id) FROM `adjacencies`"), 0) . " adjacencies at end\n");

?>
