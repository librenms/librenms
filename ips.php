#!/usr/bin/php
<?php
include("config.php");
include("includes/functions.php");
  
$sql = "SELECT * FROM devices WHERE id LIKE '%$argv[1]' AND status = '1' AND os != 'Snom' order by id DESC";
$q = mysql_query($sql);
while ($device = mysql_fetch_array($q)) {
  $hostname = $device['hostname'];
  $hostid = $device['id'];
  $community = $device['community'];
  echo("$hostname\n");
  $oids = `snmpwalk -v2c -Osq -c $community $hostname ipAdEntIfIndex | sed s/ipAdEntIfIndex.//g`;
  $oids = trim($oids);
  foreach(explode("\n", $oids) as $data) {
    $data = trim($data);
    list($oid,$ifIndex) = explode(" ", $data);
    $temp = `snmpget -O qv -v2c -c $community $hostname ipAdEntNetMask.$oid`;
    $mask = trim($temp);
    $address = $oid;
    $network = trim(`$ipcalc $address/$mask | grep Network | cut -d" " -f 4`);
    list($net,$cidr) = explode("/", $network);
    $cidr = trim($cidr);
    if($mask == "255.255.255.255") { $cidr = "32"; $network = "$address/$cidr"; }
    if (mysql_result(mysql_query("SELECT count(id) FROM `interfaces` WHERE host = '$hostid' AND `ifIndex` = '$ifIndex'"), 0) != '0') {
      $i_query = "SELECT id FROM `interfaces` WHERE host = '$hostid' AND `ifIndex` = '$ifIndex'";
      $interface_id = mysql_result(mysql_query($i_query), 0);
      if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ipaddr` WHERE `addr` = '$address' AND `cidr` = '$cidr' AND `interface_id` = '$interface_id'"), 0) == '0') {
       mysql_query("INSERT INTO `ipaddr` (`addr`, `cidr`, `network`, `interface_id`) VALUES ('$address', '$cidr', '$net', '$interface_id')");
       echo("Added $address/$cidr to $interface_id ( $hostname $ifIndex )\n $i_query\n");
      }
      if (mysql_result(mysql_query("SELECT COUNT(*) FROM `networks` WHERE `cidr` = '$network'"), 0) < '1') {
        mysql_query("INSERT INTO `networks` (`id`, `cidr`) VALUES ('', '$network')");
        echo("Create Subnet $network\n");
      }
     $network_id = mysql_result(mysql_query("SELECT id from `networks` WHERE `cidr` = '$network'"), 0);
      if (match_network($nets, $address) && mysql_result(mysql_query("SELECT COUNT(*) FROM `adjacencies` WHERE `network_id` = '$network_id' AND `interface_id` = '$interface_id'"), 0) < '1') {
        mysql_query("INSERT INTO `adjacencies` (`network_id`, `interface_id`) VALUES ('$network_id', '$interface_id')");
        echo("Create Adjacency : $hostname, $interface_id, $network_id, $network, $ifIndex\n");
      }

    } else { }
  }
}
?>
