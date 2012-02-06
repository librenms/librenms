<?php

unset ($mac_table);

echo("ARP Table : ");

$ipNetToMedia_data = snmp_walk($device, 'ipNetToMediaPhysAddress', '-Oq', 'IP-MIB');
$ipNetToMedia_data = str_replace("ipNetToMediaPhysAddress.", "", trim($ipNetToMedia_data));
$ipNetToMedia_data = str_replace("IP-MIB::", "", trim($ipNetToMedia_data));

foreach (explode("\n", $ipNetToMedia_data) as $data)
{
  list($oid, $mac) = explode(" ", $data);
  list($if, $first, $second, $third, $fourth) = explode(".", $oid);
  $ip = $first .".". $second .".". $third .".". $fourth;
  if ($ip != '...')
  {
    $interface = mysql_fetch_assoc(mysql_query("SELECT * FROM ports WHERE device_id = '".$device['device_id']."' AND ifIndex = '".$if."'"));

    list($m_a, $m_b, $m_c, $m_d, $m_e, $m_f) = explode(":", $mac);
    $m_a = zeropad($m_a);$m_b = zeropad($m_b);$m_c = zeropad($m_c);$m_d = zeropad($m_d);$m_e = zeropad($m_e);$m_f = zeropad($m_f);
    $md_a = hexdec($m_a);$md_b = hexdec($m_b);$md_c = hexdec($m_c);$md_d = hexdec($m_d);$md_e = hexdec($m_e);$md_f = hexdec($m_f);
    $mac = "$m_a:$m_b:$m_c:$m_d:$m_e:$m_f";
  
    $mac_table[$if][$mac]['ip'] =  $ip;
    $mac_table[$if][$mac]['ciscomac'] = "$m_a$m_b.$m_c$m_d.$m_e$m_f";
    $clean_mac = $m_a . $m_b . $m_c . $m_d . $m_e . $m_f;
    $mac_table[$if][$mac]['cleanmac'] = $clean_mac;
    $interface_id = $interface['interface_id'];
    $mac_table[$interface_id][$clean_mac] = 1;

    if (mysql_result(mysql_query("SELECT COUNT(*) from ipv4_mac WHERE interface_id = '".$interface['interface_id']."' AND ipv4_address = '$ip'"),0))
    {
      $sql = "UPDATE `ipv4_mac` SET `mac_address` = '$clean_mac' WHERE interface_id = '".$interface['interface_id']."' AND ipv4_address = '$ip'";
      $old_mac = mysql_fetch_row(mysql_query("SELECT mac_address from ipv4_mac WHERE ipv4_address='$ip' AND interface_id = '".$interface['interface_id']."'"));

      if ($clean_mac != $old_mac[0] && $clean_mac != '' && $old_mac[0] != '')
      {
        if ($debug) { echo("Changed mac address for $ip from $old_mac[0] to $clean_mac\n"); }
        log_event("MAC change: $ip : " . mac_clean_to_readable($old_mac[0]) . " -> " . mac_clean_to_readable($clean_mac), $device, "interface", $interface['interface_id']);
      }
      mysql_query($sql);
      echo(".");
    }
    else
    {
      echo("+");
    #echo("Add MAC $mac\n");
      mysql_query("INSERT INTO `ipv4_mac` (interface_id, mac_address, ipv4_address) VALUES ('".$interface['interface_id']."','$clean_mac','$ip')");
    }
  }
}

$sql = "SELECT * from ipv4_mac AS M, ports as I WHERE M.interface_id = I.interface_id and I.device_id = '".$device['device_id']."'";
$query = mysql_query($sql);
while ($entry = mysql_fetch_assoc($query))
{
  $entry_mac = $entry['mac_address'];
  $entry_if  = $entry['interface_id'];
  if (!$mac_table[$entry_if][$entry_mac])
  {
    mysql_query("DELETE FROM ipv4_mac WHERE interface_id = '".$entry_if."' AND mac_address = '".$entry_mac."'");
    if ($debug) { echo("Removing MAC $entry_mac from interface ".$interface['ifName']); }
    echo("-");
  }
}

echo("\n");
unset($mac);

?>