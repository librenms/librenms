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
    $interface = dbFetchRow("SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?",array($device['device_id'],$if));

    list($m_a, $m_b, $m_c, $m_d, $m_e, $m_f) = explode(":", $mac);
    $m_a = zeropad($m_a);$m_b = zeropad($m_b);$m_c = zeropad($m_c);$m_d = zeropad($m_d);$m_e = zeropad($m_e);$m_f = zeropad($m_f);
    $md_a = hexdec($m_a);$md_b = hexdec($m_b);$md_c = hexdec($m_c);$md_d = hexdec($m_d);$md_e = hexdec($m_e);$md_f = hexdec($m_f);
    $mac = "$m_a:$m_b:$m_c:$m_d:$m_e:$m_f";

    $mac_table[$if][$mac]['ip'] =  $ip;
    $mac_table[$if][$mac]['ciscomac'] = "$m_a$m_b.$m_c$m_d.$m_e$m_f";
    $clean_mac = $m_a . $m_b . $m_c . $m_d . $m_e . $m_f;
    $mac_table[$if][$mac]['cleanmac'] = $clean_mac;
    $port_id = $interface['port_id'];
    $mac_table[$port_id][$clean_mac] = 1;

    if (dbFetchCell("SELECT COUNT(*) from ipv4_mac WHERE port_id = ? AND ipv4_address = ?",array($interface['port_id'], $ip)))
    {
      // Commented below, no longer needed but leaving for reference.
      //$sql = "UPDATE `ipv4_mac` SET `mac_address` = '$clean_mac' WHERE port_id = '".$interface['port_id']."' AND ipv4_address = '$ip'";
      $old_mac = dbFetchCell("SELECT mac_address from ipv4_mac WHERE ipv4_address=? AND port_id=?",array($ip,$interface['port_id']));

      if ($clean_mac != $old_mac && $clean_mac != '' && $old_mac != '')
      {
        if ($debug) { echo("Changed mac address for $ip from $old_mac to $clean_mac\n"); }
        log_event("MAC change: $ip : " . mac_clean_to_readable($old_mac) . " -> " . mac_clean_to_readable($clean_mac), $device, "interface", $interface['port_id']);
      }
      dbUpdate(array('mac_address' => $clean_mac), 'ipv4_mac', 'port_id=? AND ipv4_address=?',array($interface['port_id'],$ip));
      echo(".");
    }
    elseif (isset($interface['port_id'])) 
    {
      echo("+");
    #echo("Add MAC $mac\n");
      $insert_data = array(
        'port_id' => $interface['port_id'],
        'mac_address' => $clean_mac,
        'ipv4_address' => $ip
      );

      dbInsert($insert_data, 'ipv4_mac');
    }
  }
}

$sql = "SELECT * from ipv4_mac AS M, ports as I WHERE M.port_id = I.port_id and I.device_id = '".$device['device_id']."'";
foreach (dbFetchRows($sql) as $entry)
{
  $entry_mac = $entry['mac_address'];
  $entry_if  = $entry['port_id'];
  if (!$mac_table[$entry_if][$entry_mac])
  {
    dbDelete('ipv4_mac', '`port_id` = ? AND `mac_address` = ?', array($entry_if,$entry_mac));
    if ($debug) { echo("Removing MAC $entry_mac from interface ".$interface['ifName']); }
    echo("-");
  }
}

echo("\n");
unset($mac);

?>
