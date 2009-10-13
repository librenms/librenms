#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");

if($argv[1]) { $where = "AND `device_id` = '$argv[1]'"; }

function snmp_array($oid, $device, $array, $mib = 0) {
  global $config;
  $cmd  = $config['snmpbulkwalk'] . " -O Qs -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
  if($mib) { $cmd .= "-m $mib "; }
  $cmd .= $oid;
  $data = trim(shell_exec($cmd));
  $device_id = $device['device_id'];
  echo("Caching: $oid\n");
  foreach(explode("\n", $data) as $entry) {
    list ($this_oid, $this_value) = split("=", $entry);
    list ($this_oid, $this_index) = explode(".", $this_oid);
    $this_index = trim($this_index);
    $this_oid = trim($this_oid);
    $this_value = trim($this_value);
    if(!strstr($this_value, "No Such Instance currently exists at this OID") && $this_index) {
      $array[$device_id][$this_index][$this_oid] = $this_value;
    }
  }
  return $array;
}

$i = 0;
$device_query = mysql_query("SELECT * FROM `devices` WHERE `ignore` = '0' AND `disabled` = '0' AND `status` = '1' $where ORDER BY device_id DESC");
while ($device = mysql_fetch_array($device_query)) {
  echo("-> " . $device['hostname'] . "\n"); 
  $i++;

  // Build SNMP Cache Array
  $data_oids = array('ifName','ifDescr','ifAlias', 'ifAdminStatus', 'ifOperStatus', 'ifMtu', 'ifSpeed', 'ifHighSpeed', 'ifType', 'ifPhysAddress');
  $stat_oids = array('ifHCInOctets', 'ifHCOutOctets', 'ifInErrors', 'ifOutErrors', 'ifInUcastPkts', 'ifOutUcastPkts', 'ifInNUcastPkts', 'ifOutNUcastPkts');
  $etherlike_oids = array('dot3StatsAlignmentErrors', 'dot3StatsFCSErrors', 'dot3StatsSingleCollisionFrames', 'dot3StatsMultipleCollisionFrames', 'dot3StatsSQETestErrors', 'dot3StatsDeferredTransmissions', 'dot3StatsLateCollisions', 'dot3StatsExcessiveCollisions', 'dot3StatsInternalMacTransmitErrors', 'dot3StatsCarrierSenseErrors', 'dot3StatsFrameTooLongs', 'dot3StatsInternalMacReceiveErrors', 'dot3StatsSymbolErrors', 'dot3StatsDuplexStatus');
  $cisco_oids = array('locIfHardType', 'vmVlan', 'vlanTrunkPortEncapsulationOperType', 'vlanTrunkPortNativeVlan', 'locIfInRunts', 'locIfInGiants', 'locIfInCRC', 'locIfInFrame', 'locIfInOverrun', 'locIfInIgnored', 'locIfInAbort', 'locIfCollisions', 'locIfInputQueueDrops', 'locIfOutputQueueDrops');
  $pagp_oids = array('pagpOperationMode', 'pagpPortState', 'pagpPartnerDeviceId', 'pagpPartnerLearnMethod', 'pagpPartnerIfIndex', 'pagpPartnerGroupIfIndex', 'pagpPartnerDeviceName', 'pagpEthcOperationMode', 'pagpDeviceId', 'pagpGroupIfIndex');
  foreach ($data_oids as $oid) {
#    $array = snmp_array($oid, $device, $array);
  }
  foreach ($stat_oids as $oid) {
#    $array = snmp_array($oid, $device, $array);
  }
  foreach ($etherlike_oids as $oid) {
#    $array = snmp_array($oid, $device, $array, "EtherLike-MIB");
  }
  foreach ($cisco_oids as $oid) {
#    $array = snmp_array($oid, $device, $array, "CISCO-SMI:CISCO-VLAN-MEMBERSHIP-MIB:CISCO-VTP-MIB:OLD-CISCO-INTERFACES-MIB");
  }
  foreach ($pagp_oids as $oid) {
    $array = snmp_array($oid, $device, $array, "CISCO-PAGP-MIB");
  }
  // End Building SNMP Cache Array

  // New interface detection
  ///// TO DO
  // End New interface detection

  // Loop interfaces in the DB and update where necessary

  $port_query = mysql_query("SELECT * FROM `interfaces` WHERE `device_id` = '".$device['device_id']."'");
  while ($port = mysql_fetch_array($port_query)) {
    echo(" --> " . $port['ifDescr'] . " ");   
    if($array[$device[device_id]][$port[ifIndex]]) { // Check to make sure Port data is cached.
      $this_port = $array[$device[device_id]][$port[ifIndex]];
      if($this_port['pagpOperationMode']) { // Check if this port has PAgP enabled.
        echo($this_port['pagpOperationMode'] ." -> " . $port['pagpOperationMode'] . " ");
        unset($separator); unset($update);
        foreach ($pagp_oids as $oid) { // Loop the OIDs
          if ( $this_port[$oid] != $port[$oid] ) { // If data has changed, build a query
            $update .= $separator . "`$oid` = '".$this_port[$oid]."'";
            $separator = ", "; 
          }
        }
        if ($update) {
          $update_query  = "UPDATE `interfaces` SET ";
          $update_query .= $update;
          $update_query .= " WHERE `interface_id` = '" . $port['interface_id'] . "'";
          @mysql_query($update_query);
          echo("PAgP ");
          unset($separator); unset($update_query); unset($update);
        }
      }
    } else {
      echo("Port Deleted?"); // Port missing from SNMP cache?
    } 
    echo("\n");
  }

  unset($array);
  echo("\n");
}

echo("$i devices polled");

?>

