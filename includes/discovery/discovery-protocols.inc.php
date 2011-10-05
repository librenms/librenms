<?php

echo("Discovery protocols:");

global $link_exists;

$community = $device['community'];

if ($device['os'] == "ironware")
{
  echo(" Brocade FDP: ");
  $fdp_array = snmpwalk_cache_twopart_oid($device, "snFdpCacheEntry", array(), "FOUNDRY-SN-SWITCH-GROUP-MIB");
  if ($fdp_array)
  {
    unset($fdp_links);
    foreach (array_keys($fdp_array) as $key)
    {
      $interface = mysql_fetch_assoc(mysql_query("SELECT * FROM `ports` WHERE device_id = '".$device['device_id']."' AND `ifIndex` = '".$key."'"));
      $fdp_if_array = $fdp_array[$key];
      foreach (array_keys($fdp_if_array) as $entry_key)
      {
        $fdp = $fdp_if_array[$entry_key];
        $remote_device_id = @mysql_result(mysql_query("SELECT `device_id` FROM `devices` WHERE `sysName` = '".$fdp['snFdpCacheDeviceId']."' OR `hostname`='".$fdp['snFdpCacheDeviceId']."'"), 0);

        if (!$remote_device_id)
        {
          $remote_device_id = discover_new_device($fdp['snFdpCacheDeviceId']);
          if ($remote_device_id)
          {
            $int = ifNameDescr($interface);
            log_event("Device autodiscovered through FDP on " . $device['hostname'] . " (port " . $int['label'] . ")", $remote_device_id, 'interface', $int['interface_id']);
          }
        }

        if ($remote_device_id)
        {
          $if = $fdp['snFdpCacheDevicePort'];
          $remote_interface_id = @mysql_result(mysql_query("SELECT interface_id FROM `ports` WHERE (`ifDescr` = '$if' OR `ifName`='$if') AND `device_id` = '".$remote_device_id."'"),0);
        } else { $remote_interface_id = "0"; }

        discover_link($interface['interface_id'], $fdp['snFdpCacheVendorId'], $remote_interface_id, $fdp['snFdpCacheDeviceId'], $fdp['snFdpCacheDevicePort'], $fdp['snFdpCachePlatform'], $fdp['snFdpCacheVersion']);
      }
    }
  }
}

echo(" CISCO-CDP-MIB: ");
unset($cdp_array);
$cdp_array = snmpwalk_cache_twopart_oid($device, "cdpCache", array(), "CISCO-CDP-MIB");
if ($cdp_array)
{
  unset($cdp_links);
  foreach (array_keys($cdp_array) as $key)
  {
    $interface = mysql_fetch_assoc(mysql_query("SELECT * FROM `ports` WHERE device_id = '".$device['device_id']."' AND `ifIndex` = '".$key."'"));
    $cdp_if_array = $cdp_array[$key];
    foreach (array_keys($cdp_if_array) as $entry_key)
    {
      $cdp = $cdp_if_array[$entry_key];
      if (ctype_alnum($cdp['cdpCacheDeviceId']))
      {
        $remote_device_id = @mysql_result(mysql_query("SELECT `device_id` FROM `devices` WHERE `sysName` = '".$cdp['cdpCacheDeviceId']."' OR `hostname`='".$cdp['cdpCacheDeviceId']."'"), 0);

        if (!$remote_device_id)
        {
          $remote_device_id = discover_new_device($cdp['cdpCacheDeviceId']);
          if ($remote_device_id)
          {
            $int = ifNameDescr($interface);
            log_event("Device autodiscovered through CDP on " . $device['hostname'] . " (port " . $int['label'] . ")", $remote_device_id, 'interface', $int['interface_id']);
          }
        }

        if ($remote_device_id)
        {
          $if = $cdp['cdpCacheDevicePort'];
          $remote_interface_id = @mysql_result(mysql_query("SELECT interface_id FROM `ports` WHERE (`ifDescr` = '$if' OR `ifName`='$if') AND `device_id` = '".$remote_device_id."'"),0);
        } else { $remote_interface_id = "0"; }

        if ($interface['interface_id'] && $cdp['cdpCacheDeviceId'] && $cdp['cdpCacheDevicePort'])
        {
          discover_link($interface['interface_id'], 'cdp', $remote_interface_id, $cdp['cdpCacheDeviceId'], $cdp['cdpCacheDevicePort'], $cdp['cdpCachePlatform'], $cdp['cdpCacheVersion']);
        }
      }
    }
  }
}

echo(" LLDP-MIB: ");

unset($lldp_array);
$lldp_array = snmpwalk_cache_threepart_oid($device, "lldpRemoteSystemsData", array(), "LLDP-MIB");
$dot1d_array = snmpwalk_cache_oid($device, "dot1dBasePortIfIndex", array(), "BRIDGE-MIB");

if ($lldp_array)
{
  $lldp_links = "";
  foreach (array_keys($lldp_array) as $key)
  {
    $lldp_if_array = $lldp_array[$key];
    foreach (array_keys($lldp_if_array) as $entry_key)
    {
      if (is_numeric($dot1d_array[$entry_key]['dot1dBasePortIfIndex']))
      {
        $ifIndex = $dot1d_array[$entry_key]['dot1dBasePortIfIndex'];
      } else {
        $ifIndex = $entry_key;
      }
      $interface = mysql_fetch_assoc(mysql_query("SELECT * FROM `ports` WHERE device_id = '".$device['device_id']."' AND `ifIndex` = '".$ifIndex."'"));
      $lldp_instance = $lldp_if_array[$entry_key];
      foreach (array_keys($lldp_instance) as $entry_instance)
      {
        $lldp = $lldp_instance[$entry_instance];
        $remote_device_id = @mysql_result(mysql_query("SELECT `device_id` FROM `devices` WHERE `sysName` = '".$lldp['lldpRemSysName']."' OR `hostname`='".$lldp['lldpRemSysName']."'"), 0);

        if (!$remote_device_id)
        {
          $remote_device_id = discover_new_device($lldp['lldpRemSysName']);
          if ($remote_device_id)
          {
            $int = ifNameDescr($interface);
            log_event("Device autodiscovered through LLDP on " . $device['hostname'] . " (port " . $int['label'] . ")", $remote_device_id, 'interface', $int['interface_id']);
          }
        }

        if ($remote_device_id)
        {
          $if = $lldp['lldpRemPortDesc']; $id = $lldp['lldpRemPortId'];
          $remote_interface_id = @mysql_result(mysql_query("SELECT interface_id FROM `ports` WHERE (`ifDescr` = '$if' OR `ifName`='$if' OR `ifDescr`= '$id' OR `ifName`='$id') AND `device_id` = '".$remote_device_id."'"),0);
        } else {
          $remote_interface_id = "0";
        }

        if (is_numeric($interface['interface_id']) && isset($lldp['lldpRemSysName']) && isset($lldp['lldpRemPortId']))
        {
          discover_link($interface['interface_id'], 'lldp', $remote_interface_id, $lldp['lldpRemSysName'], $lldp['lldpRemPortId'], NULL, $lldp['lldpRemSysDesc']);
        }
      }
    }
  }
}

if ($debug) { print_r($link_exists); }

$sql = "SELECT * FROM `links` AS L, `ports` AS I WHERE L.local_interface_id = I.interface_id AND I.device_id = '".$device['device_id']."'";
if ($query = mysql_query($sql))
{
  while ($test = mysql_fetch_assoc($query))
  {
    $local_interface_id = $test['local_interface_id'];
    $remote_hostname = $test['remote_hostname'];
    $remote_port = $test['remote_port'];
    if ($debug) { echo("$local_interface_id -> $remote_hostname -> $remote_port \n"); }
    if (!$link_exists[$local_interface_id][$remote_hostname][$remote_port])
    {
      echo("-");
      mysql_query("DELETE FROM `links` WHERE id = '" . $test['id'] . "'");
      if ($debug) { echo(mysql_affected_rows()." deleted "); }
    }
  }
}

unset($link_exists);
echo("\n");

?>
