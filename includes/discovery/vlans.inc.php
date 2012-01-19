<?php

echo("VLANs:\n");

$this_vlans = array();

include("includes/discovery/q-bridge-mib.inc.php");
include("includes/discovery/cisco-vlans.inc.php");

foreach($this_vlans as $vlan)
{
  /// Pull Tables for this VLAN

  #/usr/bin/snmpbulkwalk -v2c -c kglk5g3l454@988  -OQUs  -m BRIDGE-MIB -M /opt/observium/mibs/ udp:sw2.ahf:161 dot1dStpPortEntry
  #/usr/bin/snmpbulkwalk -v2c -c kglk5g3l454@988  -OQUs  -m BRIDGE-MIB -M /opt/observium/mibs/ udp:sw2.ahf:161 dot1dBasePortEntry

  if(is_numeric($vlan) && ($vlan <1002 || $vlan > 1105)) /// Ignore reserved VLAN IDs
  {

    if($device['os_group'] == "cisco" || $device['os'] == "ios")  /// This shit only seems to work on IOS
    {
      $vlan_device = array_merge($device, array('community' => $device['community']."@".$vlan));
      $vlan_data = snmpwalk_cache_oid($vlan_device, "dot1dStpPortEntry", array(), "BRIDGE-MIB:Q-BRIDGE-MIB");
      $vlan_data = snmpwalk_cache_oid($vlan_device, "dot1dBasePortEntry", $vlan_data, "BRIDGE-MIB:Q-BRIDGE-MIB");
    }

    echo("VLAN $vlan \n");

    if ($vlan_data)
    {
      echo(str_pad("dot1d id", 10).str_pad("ifIndex", 10).str_pad("Port Name", 25).
           str_pad("Priority", 10).str_pad("State", 15).str_pad("Cost", 10)."\n");
    }

    foreach($vlan_data as $vlan_port_id => $vlan_port)
    {
      $port = get_port_by_index_cache($device, $vlan_port['dot1dBasePortIfIndex']);
      echo(str_pad($vlan_port_id, 10).str_pad($vlan_port['dot1dBasePortIfIndex'], 10).
      str_pad($port['ifDescr'],25).str_pad($vlan_port['dot1dStpPortPriority'], 10).
      str_pad($vlan_port['dot1dStpPortState'], 15).str_pad($vlan_port['dot1dStpPortPathCost'], 10));

      $db_w = array('device_id' => $device['device_id'],
                  'interface_id' => $port['interface_id'],
                  'vlan' => $vlan);

      $db_a = array('baseport' => $vlan_port_id,
                  'priority' => $vlan_port['dot1dStpPortPriority'],
                  'state' => $vlan_port['dot1dStpPortState'],
                  'cost' => $vlan_port['dot1dStpPortPathCost']);

      $from_db = dbFetchRow("SELECT * FROM `ports_vlans` WHERE device_id = ? AND interface_id = ? AND `vlan` = ?", array($device['device_id'], $port['interface_id'], $vlan));

      if($from_db['port_vlan_id'])
      {
        dbUpdate($db_a, 'ports_vlans', "`port_vlan_id` = ?", $from_db['port_vlan_id']);
        echo("Updated");
      } else {
        dbInsert(array_merge($db_w, $db_a), 'ports_vlans');
        echo("Inserted");
      }

      echo("\n");

    }
  }

  unset($vlan_data);

}


$device_vlans = mysql_query("SELECT * FROM `vlans` WHERE `device_id` = '" . $device['device_id'] . "'");
while ($dev_vlan = mysql_fetch_assoc($device_vlans))
{
  unset($vlan_exists);
  foreach ($this_vlans as $test_vlan)
  {
    if ($test_vlan == $dev_vlan['vlan_vlan']) { $vlan_exists = 1; }
  }
  if (!$vlan_exists)
  {
    mysql_query("DELETE FROM `vlans` WHERE `vlan_id` = '" . $dev_vlan['vlan_id'] . "'");
    echo("-");
    if ($debug) { echo("Deleted VLAN ". $dev_vlan['vlan_vlan'] ."\n"); }
  }
}

unset($this_vlans);


?>
