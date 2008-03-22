<?php
  
  echo("IP Addresses : ");

  $oids = shell_exec($config['snmpwalk'] . " -".$device['snmpver']." -Osq -c ".$device['community']." ".$device['hostname']." ipAdEntIfIndex");
  $oids = trim($oids);
  $oids = str_replace("ipAdEntIfIndex.", "", $oids);
  foreach(explode("\n", $oids) as $data) {
    $data = trim($data);
    list($oid,$ifIndex) = explode(" ", $data);
    $mask = shell_exec($config['snmpget']." -O qv -".$device['snmpver']." -c ".$device['community']." ".$device['hostname']." ipAdEntNetMask.$oid");
    $mask = trim($mask);
    $network = trim(`$ipcalc $oid/$mask | grep Network | cut -d" " -f 4`);
    list($net,$cidr) = explode("/", $network);
    $cidr = trim($cidr);
    if($mask == "255.255.255.255") { $cidr = "32"; $network = "$oid/$cidr"; }

    if (mysql_result(mysql_query("SELECT count(*) FROM `interfaces` WHERE device_id = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'"), 0) != '0') {
      $i_query = "SELECT interface_id FROM `interfaces` WHERE device_id = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'";
      $interface_id = mysql_result(mysql_query($i_query), 0);

      if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ipaddr` WHERE `addr` = '$oid' AND `cidr` = '$cidr' AND `interface_id` = '$interface_id'"), 0) == '0') {
        mysql_query("INSERT INTO `ipaddr` (`addr`, `cidr`, `network`, `interface_id`) VALUES ('$oid', '$cidr', '$net', '$interface_id')");
        #echo("Added $oid/$cidr to $interface_id ( $hostname $ifIndex )\n $i_query\n");
        echo("+");
      } else { echo("."); }

      if (mysql_result(mysql_query("SELECT COUNT(*) FROM `networks` WHERE `cidr` = '$network'"), 0) < '1') {
        mysql_query("INSERT INTO `networks` (`id`, `cidr`) VALUES ('', '$network')");
        #echo("Create Subnet $network\n");
        echo("S");
      }

      $network_id = mysql_result(mysql_query("SELECT id from `networks` WHERE `cidr` = '$network'"), 0);
      if (match_network($config['nets'], $oid) && mysql_result(mysql_query("SELECT COUNT(*) FROM `adjacencies` WHERE `network_id` = '$network_id' AND `interface_id` = '$interface_id'"), 0) < '1') {
        mysql_query("INSERT INTO `adjacencies` (`network_id`, `interface_id`) VALUES ('$network_id', '$interface_id')");
        #echo("Create Adjacency : $hostname, $interface_id, $network_id, $network, $ifIndex\n");
        echo("A");
      }

    } else { echo("!"); }

  }

  echo("\n");

?>
