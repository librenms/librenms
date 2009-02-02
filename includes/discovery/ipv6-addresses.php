<?php

echo("IPv6 Addresses : ");

$ipv6interfaces = shell_exec($config['snmpget']." -Ovnq -".$device['snmpver']." -c ".$device['community']." ".$device['hostname'].":".$device['port']." ipv6Interfaces.0");

if($ipv6interfaces){

  $oids = trim(trim(shell_exec($config['snmpwalk']." -".$device['snmpver']." -Ln -c ".$device['community']." ".$device['hostname'].":".$device['port']." ipAddressIfIndex.ipv6 -Osq | grep -v No")));
  $oids = str_replace("ipAddressIfIndex.ipv6.", "", $oids);  $oids = str_replace("\"", "", $oids);  $oids = trim($oids);
  foreach(explode("\n", $oids) as $data) {
   if($data) {
    $data = trim($data);
    list($ipv6addr,$ifIndex) = explode(" ", $data);
    $oid = "";
    $sep = ''; $adsep = '';
    unset($address);
    $do = '0';
    foreach(explode(":", $ipv6addr) as $part) {
      $n = hexdec($part);
      $oid = "$oid" . "$sep" . "$n";
      $sep = ".";
      $address = $address . "$adsep" . $part;
      $do++;
      if($do == 2) { $adsep = ":"; $do = '0'; } else { $adsep = "";}
    }

    $cidr = trim(shell_exec($config['snmpget']." -".$device['snmpver']." -c ".$device['community']." ".$device['hostname'].":".$device['port']." .1.3.6.1.2.1.4.34.1.5.2.16.$oid | sed 's/.*\.//'"));
    $origin = trim(shell_exec($config['snmpget']." -Ovq -".$device['snmpver']." -c ".$device['community']." ".$device['hostname'].":".$device['port']." .1.3.6.1.2.1.4.34.1.6.2.16.$oid"));

    $network = trim(shell_exec($config['sipcalc']." $address/$cidr | grep Subnet | cut -f 2 -d '-'"));
    $comp    = trim(shell_exec($config['sipcalc']." $address/$cidr | grep Compressed | cut -f 2 -d '-'"));

    if (mysql_result(mysql_query("SELECT count(*) FROM `interfaces` WHERE device_id = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'"), 0) != '0' && $cidr > '0' && $cidr < '129' && $comp != '::1') {
      $i_query = "SELECT interface_id FROM `interfaces` WHERE device_id = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'";
      $interface_id = mysql_result(mysql_query($i_query), 0);
      if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ip6addr` WHERE `addr` = '$address' AND `cidr` = '$cidr' AND `interface_id` = '$interface_id'"), 0) == '0') {
       mysql_query("INSERT INTO `ip6addr` (`addr`, `comp_addr`, `cidr`, `origin`, `network`, `interface_id`) VALUES ('$address', '$comp', '$cidr', '$origin', '$network', '$interface_id')");
       echo("+");
      }
      if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ip6networks` WHERE `cidr` = '$network'"), 0) < '1') {
        mysql_query("INSERT INTO `ip6networks` (`id`, `cidr`) VALUES ('', '$network')");
        echo("N");
      }
      $network_id = @mysql_result(mysql_query("SELECT id from `ip6networks` WHERE `cidr` = '$network'"), 0);
      if (match_network($nets, $address) && mysql_result(mysql_query("SELECT COUNT(*) FROM `ip6adjacencies` WHERE `network_id` = '$network_id' AND `interface_id` = '$interface_id'"), 0) < '1') {
        mysql_query("INSERT INTO `ip6adjacencies` (`network_id`, `interface_id`) VALUES ('$network_id', '$interface_id')");
        echo("A");
      }
    } else { echo("."); }
   }
  }
} else { echo("None configured"); }

?>

