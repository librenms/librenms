<?php

echo("VLANs:\n");

// Pre-cache the existing state of VLANs for this device from the database
$vlans_db_raw = dbFetchRows("SELECT * FROM `vlans` WHERE `device_id` = ?", array($device['device_id']));
foreach ($vlans_db_raw as $vlan_db)
{
  $vlans_db[$vlan_db['vlan_domain']][$vlan_db['vlan_vlan']] = $vlan_db;
}

// Create an empty array to record what VLANs we discover this session.
$device['vlans'] = array();

include("includes/discovery/vlans/q-bridge-mib.inc.php");
include("includes/discovery/vlans/cisco-vtp.inc.php");

// Fetch switchport <> VLAN relationships. This is DIRTY.
foreach ($device['vlans'] as $domain_id => $vlans)
{
  foreach ($vlans as $vlan_id => $vlan)
  {
  // Pull Tables for this VLAN

    #/usr/bin/snmpbulkwalk -v2c -c kglk5g3l454@988  -OQUs  -m BRIDGE-MIB -M /opt/observium/mibs/ udp:sw2.ahf:161 dot1dStpPortEntry
    #/usr/bin/snmpbulkwalk -v2c -c kglk5g3l454@988  -OQUs  -m BRIDGE-MIB -M /opt/observium/mibs/ udp:sw2.ahf:161 dot1dBasePortEntry

    // FIXME - do this only when vlan type == ethernet?
    if (is_numeric($vlan_id) && ($vlan_id <1002 || $vlan_id > 1105)) // Ignore reserved VLAN IDs
    {
      if ($device['os_group'] == "cisco" || $device['os'] == "ios")  // This shit only seems to work on IOS
      {
        # Probably does not work with snmpv3. I have no real idea about what this code is really doing
        $vlan_device = array_merge($device, array('community' => $device['community']."@".$vlan_id));
        $vlan_data = snmpwalk_cache_oid($vlan_device, "dot1dStpPortEntry", array(), "BRIDGE-MIB:Q-BRIDGE-MIB");
        $vlan_data = snmpwalk_cache_oid($vlan_device, "dot1dBasePortEntry", $vlan_data, "BRIDGE-MIB:Q-BRIDGE-MIB");
      }

      echo("VLAN $vlan_id \n");

      if ($vlan_data)
      {
        echo(str_pad("dot1d id", 10).str_pad("ifIndex", 10).str_pad("Port Name", 25).
             str_pad("Priority", 10).str_pad("State", 15).str_pad("Cost", 10)."\n");
      }

      foreach ($vlan_data as $vlan_port_id => $vlan_port)
      {
        $port = get_port_by_index_cache($device, $vlan_port['dot1dBasePortIfIndex']);
        echo(str_pad($vlan_port_id, 10).str_pad($vlan_port['dot1dBasePortIfIndex'], 10).
        str_pad($port['ifDescr'],25).str_pad($vlan_port['dot1dStpPortPriority'], 10).
        str_pad($vlan_port['dot1dStpPortState'], 15).str_pad($vlan_port['dot1dStpPortPathCost'], 10));

        $db_w = array('device_id' => $device['device_id'],
                    'port_id' => $port['port_id'],
                    'vlan' => $vlan_id);

        $db_a['baseport'] = $vlan_port_id;
        $db_a['priority'] = isset($vlan_port['dot1dStpPortPriority']) ? $vlan_port['dot1dStpPortPriority'] : 0;
        $db_a['state']    = isset($vlan_port['dot1dStpPortState']) ? $vlan_port['dot1dStpPortState'] : "unknown";
        $db_a['cost']     = isset($vlan_port['dot1dStpPortPathCost']) ? $vlan_port['dot1dStpPortPathCost'] : 0;

        $from_db = dbFetchRow("SELECT * FROM `ports_vlans` WHERE device_id = ? AND port_id = ? AND `vlan` = ?", array($device['device_id'], $port['port_id'], $vlan_id));
	
        if ($from_db['port_vlan_id'])
        {
          dbUpdate($db_a, 'ports_vlans', "`port_vlan_id` = ?", array($from_db['port_vlan_id']));
          echo("Updated");
        } else {
          dbInsert(array_merge($db_w, $db_a), 'ports_vlans');
          echo("Inserted");
        }
        echo("\n");
      }
    }
  }
}

foreach ($vlans_db as $domain_id => $vlans)
{
  foreach ($vlans as $vlan_id => $vlan)
  {
    if (empty($device['vlans'][$domain_id][$vlan_id]))
    {
      dbDelete('vlans', "`device_id` = ? AND vlan_domain = ? AND vlan_vlan = ?", array($device['device_id'], $domain_id, $vlan_id));
    }
  }
}

unset($device['vlans']);

?>
