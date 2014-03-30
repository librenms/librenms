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
      $interface = dbFetchRow("SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?",array($device['device_id'],$key));
      $fdp_if_array = $fdp_array[$key];
      foreach (array_keys($fdp_if_array) as $entry_key)
      {
        $fdp = $fdp_if_array[$entry_key];
        $remote_device_id = dbFetchCell("SELECT `device_id` FROM `devices` WHERE `sysName` = ? OR `hostname` = ?",array($fdp['snFdpCacheDeviceId'], $fdp['snFdpCacheDeviceId']));

        if (!$remote_device_id)
        {
          $remote_device_id = discover_new_device($fdp['snFdpCacheDeviceId']);
          if ($remote_device_id)
          {
            $int = ifNameDescr($interface);
            log_event("Device autodiscovered through FDP on " . $device['hostname'] . " (port " . $int['label'] . ")", $remote_device_id, 'interface', $int['port_id']);
          }
        }

        if ($remote_device_id)
        {
          $if = $fdp['snFdpCacheDevicePort'];
          $remote_port_id = dbFetchCell("SELECT port_id FROM `ports` WHERE (`ifDescr` = ? OR `ifName = ?) AND `device_id` = ?",array($if,$if,$remote_device_id));
        } else { $remote_port_id = "0"; }

        discover_link($interface['port_id'], $fdp['snFdpCacheVendorId'], $remote_port_id, $fdp['snFdpCacheDeviceId'], $fdp['snFdpCacheDevicePort'], $fdp['snFdpCachePlatform'], $fdp['snFdpCacheVersion']);
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
    $interface = dbFetchRow("SELECT * FROM `ports` WHERE device_id = ? AND `ifIndex` = ?", array($device['device_id'], $key));
    $cdp_if_array = $cdp_array[$key];
    foreach (array_keys($cdp_if_array) as $entry_key)
    {
      $cdp = $cdp_if_array[$entry_key];
      if (is_valid_hostname($cdp['cdpCacheDeviceId']))
      {
        $remote_device_id = dbFetchCell("SELECT `device_id` FROM `devices` WHERE `sysName` = ? OR `hostname` = ?", array($cdp['cdpCacheDeviceId'], $cdp['cdpCacheDeviceId']));

        if (!$remote_device_id)
        {
          $remote_device_id = discover_new_device($cdp['cdpCacheDeviceId']);
          if ($remote_device_id)
          {
            $int = ifNameDescr($interface);
            log_event("Device autodiscovered through CDP on " . $device['hostname'] . " (port " . $int['label'] . ")", $remote_device_id, 'interface', $int['port_id']);
          }
        }

        if ($remote_device_id)
        {
          $if = $cdp['cdpCacheDevicePort'];
          $remote_port_id = dbFetchCell("SELECT port_id FROM `ports` WHERE (`ifDescr` = ? OR `ifName` = ?) AND `device_id` = ?", array($if, $if, $remote_device_id));
        } else { $remote_port_id = "0"; }

        if ($interface['port_id'] && $cdp['cdpCacheDeviceId'] && $cdp['cdpCacheDevicePort'])
        {
          discover_link($interface['port_id'], 'cdp', $remote_port_id, $cdp['cdpCacheDeviceId'], $cdp['cdpCacheDevicePort'], $cdp['cdpCachePlatform'], $cdp['cdpCacheVersion']);
        }
      }
      else
      {
        echo("X");
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
      $interface = dbFetchRow("SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?",array($device['device_id'],$ifIndex));
      $lldp_instance = $lldp_if_array[$entry_key];
      foreach (array_keys($lldp_instance) as $entry_instance)
      {
        $lldp = $lldp_instance[$entry_instance];
        $remote_device_id = dbFetchCell("SELECT `device_id` FROM `devices` WHERE `sysName` = ? OR `hostname` = ?",array($lldp['lldpRemSysName'], $lldp['lldpRemSysName']));

        if (!$remote_device_id && is_valid_hostname($lldp['lldpRemSysName']))
        {
          $remote_device_id = discover_new_device($lldp['lldpRemSysName']);
          if ($remote_device_id)
          {
            $int = ifNameDescr($interface);
            log_event("Device autodiscovered through LLDP on " . $device['hostname'] . " (port " . $int['label'] . ")", $remote_device_id, 'interface', $int['port_id']);
          }
        }

        if ($remote_device_id)
        {
          $if = $lldp['lldpRemPortDesc']; $id = $lldp['lldpRemPortId'];
          $remote_port_id = dbFetchCell("SELECT `port_id` FROM `ports` WHERE (`ifDescr` = ? OR `ifName` = ? OR `ifDescr` = ? OR `ifName` = ?) AND `device_id` = ?",array($if,$if,$id,$id,$remote_device_id));
        } else {
          $remote_port_id = "0";
        }

        if (is_numeric($interface['port_id']) && isset($lldp['lldpRemSysName']) && isset($lldp['lldpRemPortId']))
        {
          discover_link($interface['port_id'], 'lldp', $remote_port_id, $lldp['lldpRemSysName'], $lldp['lldpRemPortId'], NULL, $lldp['lldpRemSysDesc']);
        }
      }
    }
  }
}

if ($debug) { print_r($link_exists); }

$sql = "SELECT * FROM `links` AS L, `ports` AS I WHERE L.local_port_id = I.port_id AND I.device_id = '".$device['device_id']."'";
  foreach (dbFetchRows($sql) as $test)
  {
    $local_port_id = $test['local_port_id'];
    $remote_hostname = $test['remote_hostname'];
    $remote_port = $test['remote_port'];
    if ($debug) { echo("$local_port_id -> $remote_hostname -> $remote_port \n"); }
    if (!$link_exists[$local_port_id][$remote_hostname][$remote_port])
    {
      echo("-");
      $rows = dbDelete('links', '`id` = ?', array($test['id']));
      if ($debug) { echo("$rows deleted "); }
    }
  }

unset($link_exists);
echo("\n");

?>
