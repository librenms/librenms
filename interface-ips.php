#!/usr/bin/php
<?php
include("config.php");
include("includes/functions.php");
  
$sql = "SELECT * FROM devices WHERE hostname LIKE '%$argv[1]%'  order by id ASC";
$q = mysql_query($sql);
while ($device = mysql_fetch_array($q)) {
  $hostname = $device['hostname'];
  $hostid = $device['id'];
#  echo("$hostname\n");
  $oids = `snmpwalk -v2c -Osq -c $community $hostname ipAdEntIfIndex | sed s/ipAdEntIfIndex.//g`;
  $oids = trim($oids);
  foreach(explode("\n", $oids) as $data) {
    $data = trim($data);
    list($oid,$snmpid) = explode(" ", $data);
    $temp = `snmpget -O qv -v2c -c $community $hostname ipAdEntNetMask.$oid`;
    $mask = trim($temp);
    $address = $oid;
    $cidr = netmask2cidr($netmask);
    $network = trim(`$ipcalc $address/$mask | grep Network | cut -d" " -f 4`);
    if (match_network($config['nets'], $address) && $network != "") {
      if (mysql_result(mysql_query("SELECT COUNT(*) FROM `networks` WHERE `cidr` = '$network'"), 0) < '1') {
        $woo = mysql_query("INSERT INTO `networks` (`id`, `cidr`) VALUES ('', '$network')");
        echo("Create Subnet $network\n");
      } else {
        $interface_id = @mysql_result(mysql_query("SELECT I.id FROM `interfaces` AS I, `devices` AS D WHERE I.host = D.id AND `snmpid` = '$snmpid' AND D.hostname = '$hostname'"), 0);
        $network_id = @mysql_result(mysql_query("SELECT id from `networks` WHERE `cidr` = '$network'"), 0);
        if (mysql_result(mysql_query("SELECT count(id) FROM `interfaces` WHERE snmpid = '$snmpid' AND host = '$hostid'"),0) == '1') {
          if (mysql_result(mysql_query("SELECT COUNT(*) FROM `adjacencies` WHERE `network_id` = '$network_id' AND `interface_id` = '$interface_id'"), 0) < '1') {
            mysql_query("INSERT INTO `adjacencies` (`network_id`, `interface_id`) VALUES ('$network_id', '$interface_id')");
            echo("Create Adjacency : $hostname $network '$network_id', '$interface_id'\n");
          }
	} else { echo("$hostname snmpid $snmpid doesn't exist to link to $network/$cidr\n"); }
      }
    } 
  }
}
?>
