<?php

echo("IPv4 Addresses : ");

$oids = trim(snmp_walk($device,"ipAdEntIfIndex","-Osq","IP-MIB"));
$oids = trim(str_replace("ipAdEntIfIndex", "", $oids),'.');
foreach (explode("\n", $oids) as $data)
{
  $data = trim($data);
  list($oid,$ifIndex) = explode(" ", $data);
  $mask = trim(snmp_get($device,"ipAdEntNetMask.$oid","-Oqv","IP-MIB"));
  $addr = Net_IPv4::parseAddress("$oid/$mask");
  $network = $addr->network . "/" . $addr->bitmask;
  $cidr = $addr->bitmask;

  if (mysql_result(mysql_query("SELECT count(*) FROM `ports` WHERE device_id = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'"), 0) != '0' && $oid != "0.0.0.0")
  {
    $i_query = "SELECT interface_id FROM `ports` WHERE device_id = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'";
    $interface_id = mysql_result(mysql_query($i_query), 0);

    if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ipv4_networks` WHERE `ipv4_network` = '$network'"), 0) < '1')
    {
      mysql_query("INSERT INTO `ipv4_networks` (`ipv4_network`) VALUES ('$network')");
      #echo("Create Subnet $network\n");
      echo("S");
    }

    $ipv4_network_id = @mysql_result(mysql_query("SELECT `ipv4_network_id` from `ipv4_networks` WHERE `ipv4_network` = '$network'"), 0);

    if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ipv4_addresses` WHERE `ipv4_address` = '$oid' AND `ipv4_prefixlen` = '$cidr' AND `interface_id` = '$interface_id'"), 0) == '0')
    {
      mysql_query("INSERT INTO `ipv4_addresses` (`ipv4_address`, `ipv4_prefixlen`, `ipv4_network_id`, `interface_id`) VALUES ('$oid', '$cidr', '$ipv4_network_id', '$interface_id')");
      #echo("Added $oid/$cidr to $interface_id ( $hostname $ifIndex )\n $i_query\n");
      echo("+");
    } else { echo("."); }

    $full_address = "$oid/$cidr|$ifIndex";
    $valid_v4[$full_address] = 1;
  } else { echo("!"); }
}

$sql   = "SELECT * FROM ipv4_addresses AS A, ports AS I WHERE I.device_id = '".$device['device_id']."' AND  A.interface_id = I.interface_id";
$data = mysql_query($sql);

while ($row = mysql_fetch_assoc($data))
{
  $full_address = $row['ipv4_address'] . "/" . $row['ipv4_prefixlen'] . "|" . $row['ifIndex'];

  if (!$valid_v4[$full_address])
  {
    echo("-");
    $query = @mysql_query("DELETE FROM `ipv4_addresses` WHERE `ipv4_address_id` = '".$row['ipv4_address_id']."'");
    if (!mysql_result(mysql_query("SELECT count(*) FROM ipv4_addresses WHERE ipv4_network_id = '".$row['ipv4_network_id']."'"),0))
    {
      $query = @mysql_query("DELETE FROM `ipv4_networks` WHERE `ipv4_network_id` = '".$row['ipv4_network_id']."'");
    }
  }
}

echo("\n");

unset($valid_v4);

?>