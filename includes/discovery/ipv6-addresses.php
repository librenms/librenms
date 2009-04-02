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
    unset($ip6_addr);
    $do = '0';
    foreach(explode(":", $ipv6addr) as $part) {
      $n = hexdec($part);
      $oid = "$oid" . "$sep" . "$n";
      $sep = ".";
      $ip6_addr = $ip6_addr . "$adsep" . $part;
      $do++;
      if($do == 2) { $adsep = ":"; $do = '0'; } else { $adsep = "";}
    }
    $ip6_prefixlen = trim(shell_exec($config['snmpget']." -".$device['snmpver']." -c ".$device['community']." ".$device['hostname'].":".$device['port']." .1.3.6.1.2.1.4.34.1.5.2.16.$oid | sed 's/.*\.//'"));
    $ip6_origin    = trim(shell_exec($config['snmpget']." -Ovq -".$device['snmpver']." -c ".$device['community']." ".$device['hostname'].":".$device['port']." .1.3.6.1.2.1.4.34.1.6.2.16.$oid"));

    $ip6_network   = trim(shell_exec($config['sipcalc']." $ip6_addr/$ip6_prefixlen | grep Subnet | cut -f 2 -d '-'"));
    $ip6_comp_addr = trim(shell_exec($config['sipcalc']." $ip6_addr/$ip6_prefixlen | grep Compressed | cut -f 2 -d '-'"));

    if (mysql_result(mysql_query("SELECT count(*) FROM `interfaces` WHERE device_id = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'"), 0) != '0' && $ip6_prefixlen > '0' && $ip6_prefixlen < '129' && $ip6_comp_addr != '::1') {
      $i_query = "SELECT interface_id FROM `interfaces` WHERE device_id = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'";
      $interface_id = mysql_result(mysql_query($i_query), 0);
       if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ip6networks` WHERE `ip6_network` = '$ip6_network'"), 0) < '1') {
        mysql_query("INSERT INTO `ip6networks` (`ip6_network`) VALUES ('$ip6_network')");
        echo("N");
      }

      if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ip6networks` WHERE `ip6_network` = '$ip6_network'"), 0) < '1') {
        mysql_query("INSERT INTO `ip6networks` (`ip6_network`) VALUES ('$ip6_network')");
        echo("N");
      }
      $ip6_network_id = @mysql_result(mysql_query("SELECT `ip6_network_id` from `ip6networks` WHERE `ip6_network` = '$ip6_network'"), 0);

      if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ip6addr` WHERE `ip6_addr` = '$ip6_addr' AND `ip6_prefixlen` = '$ip6_prefixlen' AND `interface_id` = '$interface_id'"), 0) == '0') {
       mysql_query("INSERT INTO `ip6addr` (`ip6_addr`, `ip6_comp_addr`, `ip6_prefixlen`, `ip6_origin`, `ip6_network`, `ip6_network_id`, `interface_id`) VALUES ('$ip6_addr', '$ip6_comp_addr', '$ip6_prefixlen', '$ip6_origin', '$ip6_network', '$ip6_network_id', '$interface_id')");
       echo("+");
      } else { echo("."); }
#      if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ip6adjacencies` WHERE `ip6_network_id` = '$ip6_network_id' AND `interface_id` = '$interface_id'"), 0) < '1') {
#        mysql_query("INSERT INTO `ip6adjacencies` (`network_id`, `interface_id`) VALUES ('$ip6_network_id', '$interface_id')");
#        echo("A");
#      }
      $full_address = "$ip6_addr/$ip6_prefixlen";
      $valid_v6[$full_address] = 1;
    }
   }
  }
} else { echo("None configured"); }

$sql   = "SELECT * FROM ip6addr AS A, interfaces AS I WHERE I.device_id = '".$device['device_id']."' AND  A.interface_id = I.interface_id";
$data = mysql_query($sql);
while($row = mysql_fetch_array($data)) {
  $full_address = $row['ip6_addr'] . "/" . $row['ip6_prefixlen'];
  if(!$valid_v6[$full_address]) {
    echo("-");
    $query = @mysql_query("DELETE FROM `ip6addr` WHERE `ip6_addr_id` = '".$row['ip6_addr_id']."'");
    if(!mysql_result(mysql_query("SELECT count(*) FROM ip6addr WHERE ip6_network_id = '".$row['ip6_network_id']."'"),0)) {
      $query = @mysql_query("DELETE FROM `ip6networks` WHERE `ip6_network_id` = '".$row['ip6_network_id']."'");
    }
  }
}
unset($valid_v6);

?>

