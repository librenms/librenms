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

  if($config['enable_ports_etherlike']) { echo("dot3Stats "); $array = snmp_cache_oid("dot3StatsEntry", $device, $array, "EtherLike-MIB"); }

  echo("\n");

  #foreach ($etherlike_oids as $oid) { $array = snmp_cache_oid($oid, $device, $array, "EtherLike-MIB"); }
  #foreach ($cisco_oids as $oid)     { $array = snmp_cache_oid($oid, $device, $array, "OLD-CISCO-INTERFACES-MIB"); }
  #foreach ($pagp_oids as $oid)      { $array = snmp_cache_oid($oid, $device, $array, "CISCO-PAGP-MIB"); }

  if($device['os_group'] == "ios") {
    $array = snmp_cache_portIfIndex ($device, $array);
    $array = snmp_cache_portName ($device, $array);
    $data_oids[] = "portName";
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

  /// Loop ports in the DB and update where necessary
  $port_query = mysql_query("SELECT * FROM `ports` WHERE `device_id` = '".$device['device_id']."'");
  while ($port = mysql_fetch_array($port_query)) {
    
    echo(" --> " . $port['ifDescr'] . " ");   
    if($array[$device[device_id]][$port[ifIndex]]) { // Check to make sure Port data is cached.

      $this_port = &$array[$device[device_id]][$port[ifIndex]];

      $polled_period = $polled - $port['poll_time'];

      $update .= "`poll_time` = '".$polled."'";
      $update .= ", `poll_prev` = '".$port['poll_time']."'";
      $update .= ", `poll_period` = '".$polled_period."'";

      /// Copy ifHC[In|Out]Octets values to non-HC if they exist
      if($this_port['ifHCInOctets'] > 0 && is_numeric($this_port['ifHCInOctets']) && $this_port['ifHCOutOctets'] > 0 && is_numeric($this_port['ifHCOutOctets'])) {
        echo("HC ");
        $this_port['ifInOctets'] = $this_port['ifHCInOctets'];
	$this_port['ifOutOctets'] = $this_port['ifHCOutOctets'];
      }

      /// Update IF-MIB data
      foreach ($data_oids as $oid)      { 
        if ( $port[$oid] != $this_port[$oid] && !isset($this_port[$oid])) {
          $update .= ", `$oid` = NULL";
          eventlog($oid . ": ".$port[$oid]." -> NULL", $device['device_id'], $port['interface_id']);
          if($debug) { echo($oid . ": ".$port[$oid]." -> NULL "); } else { echo($oid . " "); }
        } elseif ( $port[$oid] != $this_port[$oid] ) {
          $update .= ", `$oid` = '".mres($this_port[$oid])."'";
  	  eventlog($oid . ": ".$port[$oid]." -> " . $this_port[$oid], $device['device_id'], $port['interface_id']);
          if($debug) { echo($oid . ": ".$port[$oid]." -> " . $this_port[$oid]." "); } else { echo($oid . " "); }
        }
      }

      /// Parse description (usually ifAlias) if config option set

      if(isset($config['port_descr_parser']))
      {
        $port_attribs = array('type','descr','circuit','speed','notes');
        include($config['port_descr_parser']);

        foreach ($port_attribs as $attrib) {
          $attrib_key = "port_descr_".$attrib;
          if($port_ifAlias[$attrib]) 
          {
            if($port_ifAlias[$attrib] != $port[$attrib_key]) 
            {
              $update .= ", `".$attrib_key."` = '".$port_ifAlias[$attrib]."'";
              eventlog($attrib . ": ".$port[$attrib_key]." -> " . $port_ifAlias[$attrib], $device['device_id'], $port['interface_id']);
            }
          }
        }        
      }

      /// Ende parse ifAlias

      /// Update IF-MIB metrics
      foreach ($stat_oids_db as $oid) {
	$update .= ", `$oid` = '".$this_port[$oid]."'";
        $update .= ", `".$oid."_prev` = '".$port[$oid]."'";       
        $oid_prev = $oid . "_prev";
        if($port[$oid]) {
          $oid_diff = $this_port[$oid] - $port[$oid];
          $oid_rate  = $oid_diff / $polled_period;
          if($oid_rate < 0) { $oid_rate = "0"; }
          $update .= ", `".$oid."_rate` = '".$oid_rate."'";
          $update .= ", `".$oid."_delta` = '".$oid_diff."'";
          if($debug) {echo("\n $oid ($oid_diff B) $oid_rate Bps $polled_period secs\n");}
        }
      }

      /// Update RRDs
      $rrdfile = $host_rrd . "/" . safename($port['ifIndex'] . ".rrd");
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
        if(!is_numeric($$oid)) { $$oid = "0"; }
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
	    eventlog("$oid -> ".$this_port[$oid], $device['device_id'], $port['interface_id']);
          }
        }
      } 
      // End Update PAgP

      /// Do EtherLike-MIB
      if($config['enable_ports_etherlike']) { include("port-etherlike.inc.php"); }      

     // Update MySQL
     if ($update) { 
        $update_query  = "UPDATE `ports` SET ".$update." WHERE `interface_id` = '" . $port['interface_id'] . "'";
        @mysql_query($update_query); $mysql++;
        if($debug) {echo("\nMYSQL : [ $update_query ]");}
      }
      // End Update MySQL

      unset($update_query); unset($update);

      // Send alerts for interface flaps.
      if ($config['warn']['ifdown'] && ($port['ifOperStatus'] != $this_port['ifOperStatus'])) {
          if ($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
          if ($this_port['ifAlias']) { $falias = preg_replace('/^"/', '', $this_port['ifAlias']); $falias = preg_replace('/"$/', '', $falias); $full = $this_port['ifDescr'] . " (" . $falias . ")"; } else { $full = $this_port['ifDescr']; }
          switch ($this_port['ifOperStatus']) {
              case "up":
                  mail($email, "Interface UP - " . $device['hostname'] . " - " . $full, "Device:    " . $device['hostname'] . "\nInterface: " . $full . "\nTimestamp: " . date($config['timestamp_format']), $config['email_headers']);
              break;
              case "down":
                  mail($email, "Interface DOWN - " . $device['hostname'] . " - " . $full, "Device:    " . $device['hostname'] . "\nInterface: " . $full . "\nTimestamp: " . date($config['timestamp_format']), $config['email_headers']);
              break;
          }
      }
    } else {
      echo("Port Deleted?"); // Port missing from SNMP cache?
    } 
    echo("\n");
  }

?>
