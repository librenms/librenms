<?php

  unset($ports);
  $ports = snmp_cache_ifIndex($device); // Cache Port List

  // Build SNMP Cache Array
  $data_oids = array('ifName','ifDescr','ifAlias', 'ifAdminStatus', 'ifOperStatus', 'ifMtu', 'ifSpeed', 'ifHighSpeed', 'ifType', 'ifPhysAddress',
                     'ifPromiscuousMode','ifConnectorPresent');
  $stat_oids = array('ifInErrors', 'ifOutErrors', 'ifInUcastPkts', 'ifOutUcastPkts', 'ifInNUcastPkts', 'ifOutNUcastPkts', 
                     'ifHCInMulticastPkts', 'ifHCInBroadcastPkts', 'ifHCOutMulticastPkts', 'ifHCOutBroadcastPkts',
                     'ifInOctets', 'ifOutOctets', 'ifHCInOctets', 'ifHCOutOctets');

  $stat_oids_db = array('ifInOctets', 'ifOutOctets', 'ifInErrors', 'ifOutErrors', 'ifInUcastPkts', 'ifOutUcastPkts'); // From above for DB

 $etherlike_oids = array('dot3StatsAlignmentErrors', 'dot3StatsFCSErrors', 'dot3StatsSingleCollisionFrames', 'dot3StatsMultipleCollisionFrames',
                          'dot3StatsSQETestErrors', 'dot3StatsDeferredTransmissions', 'dot3StatsLateCollisions', 'dot3StatsExcessiveCollisions',
                          'dot3StatsInternalMacTransmitErrors', 'dot3StatsCarrierSenseErrors', 'dot3StatsFrameTooLongs', 'dot3StatsInternalMacReceiveErrors',
                          'dot3StatsSymbolErrors');

  $cisco_oids = array('locIfHardType', 'locIfInRunts', 'locIfInGiants', 'locIfInCRC', 'locIfInFrame', 'locIfInOverrun', 'locIfInIgnored', 'locIfInAbort',  
                      'locIfCollisions', 'locIfInputQueueDrops', 'locIfOutputQueueDrops');

  $pagp_oids = array('pagpOperationMode', 'pagpPortState', 'pagpPartnerDeviceId', 'pagpPartnerLearnMethod', 'pagpPartnerIfIndex', 'pagpPartnerGroupIfIndex', 
                     'pagpPartnerDeviceName', 'pagpEthcOperationMode', 'pagpDeviceId', 'pagpGroupIfIndex');

  $ifmib_oids = array_merge($data_oids, $stat_oids);

  $ifmib_oids = array('ifEntry', 'ifXEntry');

  echo("Caching Oids: ");
  foreach ($ifmib_oids as $oid)      { echo("$oid "); $array = snmp_cache_oid($oid, $device, $array, "IF-MIB");}

  if($config['enable_etherlike']) { echo("dot3Stats "); $array = snmp_cache_oid("dot3StatsEntry", $device, $array, "EtherLike-MIB"); }

  echo("\n");

  #foreach ($etherlike_oids as $oid) { $array = snmp_cache_oid($oid, $device, $array, "EtherLike-MIB"); }
  #foreach ($cisco_oids as $oid)     { $array = snmp_cache_oid($oid, $device, $array, "OLD-CISCO-INTERFACES-MIB"); }
  #foreach ($pagp_oids as $oid)      { $array = snmp_cache_oid($oid, $device, $array, "CISCO-PAGP-MIB"); }

  if($device['os_group'] == "ios") {
    #$array = snmp_cache_portIfIndex ($device, $array);
    #$array = snmp_cache_portName ($device, $array);
    #$array = snmp_cache_oid("vmVlan", $device, $array, "CISCO-VLAN-MEMBERSHIP-MIB");
    #$array = snmp_cache_oid("vlanTrunkPortEncapsulationOperType", $device, $array, "CISCO-VTP-MIB");
    #$array = snmp_cache_oid("vlanTrunkPortNativeVlan", $device, $array, "CISCO-VTP-MIB");
  }

  $polled = time();

  /// End Building SNMP Cache Array

  if($debug) { print_r($array); }

  /// New interface detection
  ///// TO DO
  /// End New interface detection

  /// Loop interfaces in the DB and update where necessary
  $port_query = mysql_query("SELECT * FROM `interfaces` WHERE `device_id` = '".$device['device_id']."'");
  while ($port = mysql_fetch_array($port_query)) {
    
    echo(" --> " . $port['ifDescr'] . " ");   
    if($array[$device[device_id]][$port[ifIndex]]) { // Check to make sure Port data is cached.

      $this_port = &$array[$device[device_id]][$port[ifIndex]];

      $polled_period = $polled - $port['poll_time'];

      $update .= "`poll_time` = '".$polled."'";
      $update .= ", `poll_prev` = '".$port['poll_time']."'";
      $update .= ", `poll_period` = '".$polled_period."'";

      /// Copy ifHC[In|Out]Octets values to non-HC if they exist
      if(is_numeric($this_port['ifHCInOctets']) && is_numeric($this_port['ifHCOutOctets'])) {
        echo("HC ");
        $this_port['ifInOctets'] = $this_port['ifHCInOctets'];
	$this_port['ifOutOctets'] = $this_port['ifHCOutOctets'];
      }

      /// Update IF-MIB data
      foreach ($data_oids as $oid)      { 
        if ( $port[$oid] != $this_port[$oid]) {
          $update .= ", `$oid` = '".mysql_real_escape_string($this_port[$oid])."'";
          #mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $port['device_id'] . "', '" . $port['interface_id'] . "', NOW(), '".$oid . ": ".$port[$oid]." -> " . $this_port[$oid]."')");
          #eventlog($device['device_id'], 'interface', $port['interface_id'], $oid . ": ".$port[$oid]." -> " . $this_port[$oid]);
          echo($oid . " ");
        }
      }

      /// Update IF-MIB metrics
      foreach ($stat_oids_db as $oid) {
	$update .= ", `$oid` = '".$this_port[$oid]."'";
        $update .= ", `".$oid."_prev` = '".$port[$oid]."'";       
        $oid_prev = $oid . "_prev";
        if($port[$oid]) {
          $oid_diff = $this_port[$oid] - $port[$oid];
          $oid_rate  = $oid_diff / $polled_period;
          $update .= ", `".$oid."_rate` = '".$oid_rate."'";
          $update .= ", `".$oid."_delta` = '".$oid_diff."'";
          #echo("\n $oid ($oid_diff B) $oid_rate Bps $polled_period secs\n");
        }
      }

      /// Update RRDs
      $rrdfile = $host_rrd . "/" . $port['ifIndex'] . ".rrd";
      if(!is_file($rrdfile)) {
        $woo = shell_exec($config['rrdtool'] . " create $rrdfile -s 300 \
        DS:INOCTETS:DERIVE:600:0:12500000000 \
        DS:OUTOCTETS:DERIVE:600:0:12500000000 \
        DS:INERRORS:DERIVE:600:0:12500000000 \
        DS:OUTERRORS:DERIVE:600:0:12500000000 \
        DS:INUCASTPKTS:DERIVE:600:0:12500000000 \
        DS:OUTUCASTPKTS:DERIVE:600:0:12500000000 \
        DS:INNUCASTPKTS:DERIVE:600:0:12500000000 \
        DS:OUTNUCASTPKTS:DERIVE:600:0:12500000000 \
        RRA:AVERAGE:0.5:1:600 \
        RRA:AVERAGE:0.5:6:700 \
        RRA:AVERAGE:0.5:24:775 \
        RRA:AVERAGE:0.5:288:797 \
        RRA:MAX:0.5:1:600 \
        RRA:MAX:0.5:6:700 \
        RRA:MAX:0.5:24:775 \
        RRA:MAX:0.5:288:797");
      }

      foreach ($stat_oids as $oid) {  /// Copy values from array to global variables and force numeric.
        $$oid = $this_port[$oid];      
        $$oid = $$oid+0;
      }

      $woo = "$polled:$ifInOctets:$ifOutOctets:$ifInErrors:$ifOutErrors:$ifInUcastPkts:$ifOutUcastPkts:$ifInNUcastPkts:$ifOutNUcastPkts";
      $ret = rrdtool_update("$rrdfile", $woo);

      /// End Update IF-MIB

      /// Update PAgP
      if($this_port['pagpOperationMode']) { 
        foreach ($pagp_oids as $oid) { // Loop the OIDs
          if ( $this_port[$oid] != $port[$oid] ) { // If data has changed, build a query
            $update .= ", `$oid` = '".mres($this_port[$oid])."'";
            echo("PAgP ");
          }
        }
      } 
      // End Update PAgP

      /// Do EtherLike-MIB
      if($config['enable_etherlike']) { include("port-etherlike.inc.php"); }      

     if ($update) { /// Do Updates
        $update_query  = "UPDATE `interfaces` SET ".$update." WHERE `interface_id` = '" . $port['interface_id'] . "'";
        @mysql_query($update_query); $mysql++;
        if($debug) {echo("\nMYSQL : [ $update_query ]");}
      } /// End Updates

      unset($update_query); unset($update);
    } else {
      echo("Port Deleted?"); // Port missing from SNMP cache?
    } 
    echo("\n");
  }

?>
