<?php

echo("IPv6 Addresses : ");

  $cmd = $config['snmpwalk']." -m IP-MIB -".$device['snmpver']." -Ln -c ".$device['community']." ".$device['hostname'].":".$device['port'];
  $cmd .= " ipAddressIfIndex.ipv6 -Osq | grep -v No";
  $oids = trim(trim(shell_exec($cmd)));
  $oids = str_replace("ipAddressIfIndex.ipv6.", "", $oids);  $oids = str_replace("\"", "", $oids);  $oids = trim($oids);
  foreach(explode("\n", $oids) as $data) {
   if($data) {
    $data = trim($data);
    list($ipv6addr,$ifIndex) = explode(" ", $data);
    $oid = "";
    $sep = ''; $adsep = '';
    unset($ipv6_address);
    $do = '0';
    foreach(explode(":", $ipv6addr) as $part) {
      $n = hexdec($part);
      $oid = "$oid" . "$sep" . "$n";
      $sep = ".";
      $ipv6_address = $ipv6_address . "$adsep" . $part;
      $do++;
      if($do == 2) { $adsep = ":"; $do = '0'; } else { $adsep = "";}
    }
    $ipv6_prefixlen = trim(shell_exec($config['snmpget']." -m IP-MIB -".$device['snmpver']." -c ".$device['community']." ".$device['hostname'].":".$device['port']." .1.3.6.1.2.1.4.34.1.5.2.16.$oid | sed 's/.*\.//'"));
    $ipv6_origin    = trim(shell_exec($config['snmpget']." -m IP-MIB -Ovq -".$device['snmpver']." -c ".$device['community']." ".$device['hostname'].":".$device['port']." .1.3.6.1.2.1.4.34.1.6.2.16.$oid"));

    discover_process_ipv6($ifIndex,$ipv6_address,$ipv6_prefixlen,$ipv6_origin);
   } # if $data
  } # foreach

if (!$oids)
{
  $cmd = $config['snmpwalk']." -m IPV6-MIB -".$device['snmpver']." -Ln -c ".$device['community']." ".$device['hostname'].":".$device['port'];
  $cmd .= " ipv6AddrPfxLength -Osq -OnU| grep -v No";
  $oids = trim(trim(shell_exec($cmd)));
  $oids = str_replace(".1.3.6.1.2.1.55.1.8.1.2.", "", $oids);  $oids = str_replace("\"", "", $oids);  $oids = trim($oids);
  foreach(explode("\n", $oids) as $data) {
   if($data) {
    $data = trim($data);
    list($if_ipv6addr,$ipv6_prefixlen) = explode(" ", $data);
    list($ifIndex,$ipv6addr) = split("\\.",$if_ipv6addr,2);
    $ipv6_address = snmp2ipv6($ipv6addr);

    $ipv6_origin    = trim(shell_exec($config['snmpget']." -m IPV6-MIB -Ovq -".$device['snmpver']." -c ".$device['community']." ".$device['hostname'].":".$device['port']." IPV6-MIB::ipv6AddrType.$if_ipv6addr"));
    
    discover_process_ipv6($ifIndex,$ipv6_address,$ipv6_prefixlen,$ipv6_origin);
   } # if $data
  } # foreach
} # if $oids

$sql   = "SELECT * FROM ipv6_addresses AS A, interfaces AS I WHERE I.device_id = '".$device['device_id']."' AND  A.interface_id = I.interface_id";
$data = mysql_query($sql);
while($row = mysql_fetch_array($data)) {
  $full_address = $row['ipv6_address'] . "/" . $row['ipv6_prefixlen'];
  $interface_id = $row['interface_id'];
  $valid = $full_address  . "-" . $interface_id;
  if(!$valid_v6[$valid]) {
    echo("-");
    $query = @mysql_query("DELETE FROM `ipv6_addresses` WHERE `ipv6_address_id` = '".$row['ipv6_address_id']."'");
    if(!mysql_result(mysql_query("SELECT count(*) FROM ipv6_addresses WHERE ipv6_network_id = '".$row['ipv6_network_id']."'"),0)) {
      $query = @mysql_query("DELETE FROM `ipv6_networks` WHERE `ipv6_network_id` = '".$row['ipv6_network_id']."'");
    }
  }
}
unset($valid_v6);

?>

