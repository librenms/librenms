#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");

if($argv[1]) { $where = "AND `device_id` = '$argv[1]'"; }

function snmp_cache($oid, $device, $array, $mib = 0) {
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
    $array[$device_id][$oid] = '1';
  }
  return $array;
}

function snmp_cache_portIfIndex ($device, $array) {
  global $config;
  $cmd = $config['snmpwalk'] . " -CI -m CISCO-STACK-MIB -O q -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " portIfIndex";
  $output = trim(shell_exec($cmd));
  echo("Caching: portIfIndex\n");
  foreach(explode("\n", $output) as $entry){
    $entry = str_replace("CISCO-STACK-MIB::portIfIndex.", "", $entry);
    list($slotport, $ifIndex) = explode(" ", $entry);
    $array[$device_id][$ifIndex]['portIfIndex'] = $slotport;
    $array[$device_id][$slotport]['ifIndex'] = $ifIndex;
  }
  return $array;
}

function snmp_cache_portName ($device, $array) {
  global $config;
  $cmd = $config['snmpwalk'] . " -CI -m CISCO-STACK-MIB -O Qs -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " portName";
  $output = trim(shell_exec($cmd));
  echo("Caching: portName\n");
  foreach(explode("\n", $output) as $entry){
    $entry = str_replace("portName.", "", $entry);
    list($slotport, $portName) = explode("=", $entry);
    $slotport = trim($slotport); $portName = trim($portName);
    if ($array[$device_id][$slotport]['ifIndex']) {
      $ifIndex = $array[$device_id][$slotport]['ifIndex'];
      $array[$device_id][$slotport]['portName'] = $portName;
      $array[$device_id][$ifIndex]['portName'] = $portName;
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
  $data_oids = array('ifName','ifDescr','ifAlias', 'ifAdminStatus', 'ifOperStatus', 'ifMtu', 'ifSpeed', 'ifHighSpeed', 'ifType', 'ifPhysAddress',
                     'ifPromiscuousMode','ifConnectorPresent');
  $stat_oids = array('ifHCInOctets', 'ifHCOutOctets', 'ifInErrors', 'ifOutErrors', 'ifInUcastPkts', 'ifOutUcastPkts', 'ifInNUcastPkts', 'ifOutNUcastPkts',
                     'ifHCInMulticastPkts', 'ifHCInBroadcastPkts', 'ifHCOutMulticastPkts', 'ifHCOutBroadcastPkts');
  $etherlike_oids = array('dot3StatsAlignmentErrors', 'dot3StatsFCSErrors', 'dot3StatsSingleCollisionFrames', 'dot3StatsMultipleCollisionFrames', 
                          'dot3StatsSQETestErrors', 'dot3StatsDeferredTransmissions', 'dot3StatsLateCollisions', 'dot3StatsExcessiveCollisions', 
                          'dot3StatsInternalMacTransmitErrors', 'dot3StatsCarrierSenseErrors', 'dot3StatsFrameTooLongs', 'dot3StatsInternalMacReceiveErrors', 
                          'dot3StatsSymbolErrors', 'dot3StatsDuplexStatus');
  $cisco_oids = array('locIfHardType', 'locIfInRunts', 'locIfInGiants', 'locIfInCRC', 'locIfInFrame', 'locIfInOverrun', 'locIfInIgnored', 'locIfInAbort',  
                      'locIfCollisions', 'locIfInputQueueDrops', 'locIfOutputQueueDrops');
  $pagp_oids = array('pagpOperationMode', 'pagpPortState', 'pagpPartnerDeviceId', 'pagpPartnerLearnMethod', 'pagpPartnerIfIndex', 'pagpPartnerGroupIfIndex', 
                     'pagpPartnerDeviceName', 'pagpEthcOperationMode', 'pagpDeviceId', 'pagpGroupIfIndex');

  $cip_oids = array('cipMacHCSwitchedBytes', 'cipMacHCSwitchedBytes', 'cipMacHCSwitchedPkts', 'cipMacHCSwitchedPkts');

  $array = snmp_cache_portIfIndex ($device, $array);
  $array = snmp_cache_portName ($device, $array);
  foreach ($data_oids as $oid)      { $array = snmp_cache($oid, $device, $array, "IF-MIB"); }
  #foreach ($cip_oids as $oid)       { $array = snmp_cache($oid, $device, $array, "CISCO-IP-STAT-MIB"); }
  foreach ($stat_oids as $oid)      { $array = snmp_cache($oid, $device, $array, "IF-MIB"); }
  foreach ($etherlike_oids as $oid) { $array = snmp_cache($oid, $device, $array, "EtherLike-MIB"); }
  foreach ($cisco_oids as $oid)     { $array = snmp_cache($oid, $device, $array, "OLD-CISCO-INTERFACES-MIB"); }
  foreach ($pagp_oids as $oid)      { $array = snmp_cache($oid, $device, $array, "CISCO-PAGP-MIB"); }

  snmp_cache("vmVlan", $device, $array, "CISCO-VLAN-MEMBERSHIP-MIB");
  snmp_cache("vlanTrunkPortEncapsulationOperType", $device, $array, "CISCO-VTP-MIB");
  snmp_cache("vlanTrunkPortNativeVlan", $device, $array, "CISCO-VTP-MIB");

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

      /// Update IF-MIB
      foreach ($data_oids as $oid)      { 
        if ( $port[$oid] != $this_port[$oid]) {
          $update .= $separator . "`$oid` = '".$this_port[$oid]."'";
          $separator = ", ";
          mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $port['device_id'] . "', '" . $port['interface_id'] . "', NOW(), '".$oid . ": ".$port[$oid]." -> " . $this_port[$oid]."')");
          #eventlog($device['device_id'], 'interface', $port['interface_id'], $oid . ": ".$port[$oid]." -> " . $this_port[$oid]);
          echo($oid . " ");
        }
      }
      /// End Update IF-MIB

      /// Update PAgP
      if($this_port['pagpOperationMode']) { 
        unset($separator); unset($update);
        foreach ($pagp_oids as $oid) { // Loop the OIDs
          if ( $this_port[$oid] != $port[$oid] ) { // If data has changed, build a query
            $update .= $separator . "`$oid` = '".$this_port[$oid]."'";
            $separator = ", "; 
            echo("PAgP ");
          }
        }
      } 
      // End Update PAgP


      if ($update) { /// Do Updates
        $update_query  = "UPDATE `interfaces` SET ";
        $update_query .= $update;
        $update_query .= " WHERE `interface_id` = '" . $port['interface_id'] . "'";
        #@mysql_query($update_query);
        #echo("$update_query");
        if(mysql_affected_rows > '0') { echo("Updated "); }
      } /// End Updates

      unset($separator); unset($update_query); unset($update);
    } else {
      echo("Port Deleted?"); // Port missing from SNMP cache?
    } 
    echo("\n");
  }

  #unset($array);
  echo("\n");
}

echo("$i devices polled");

?>
