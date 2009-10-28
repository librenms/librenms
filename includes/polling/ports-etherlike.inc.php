<?php

  unset($ports);
  $ports = snmp_cache_ifIndex($device); // Cache Port List

  // Build SNMP Cache Array
  $etherlike_oids = array('dot3StatsAlignmentErrors', 'dot3StatsFCSErrors', 'dot3StatsSingleCollisionFrames', 'dot3StatsMultipleCollisionFrames', 
                          'dot3StatsSQETestErrors', 'dot3StatsDeferredTransmissions', 'dot3StatsLateCollisions', 'dot3StatsExcessiveCollisions', 
                          'dot3StatsInternalMacTransmitErrors', 'dot3StatsCarrierSenseErrors', 'dot3StatsFrameTooLongs', 'dot3StatsInternalMacReceiveErrors',
                          'dot3StatsSymbolErrors');

  if(count($etherlike_oids) > (count($ports)*2.5)) { /// If there are 2.5x more interfaces than OIDs, do per-OID
    $sub_start = utime();
    echo("Caching Ports: ");
    foreach($ports as $port) { echo("$port "); $array = snmp_cache_port_oids($etherlike_oids, $port, $device, $array, "EtherLike-MIB"); }
    $end = utime(); $run = $end - $sub_start; $proctime = substr($run, 0, 5);
    echo("\n$proctime secs\n");
  } else {
    $sub_start = utime();
    echo("Caching Oids: ");
    foreach ($etherlike_oids as $oid)      { echo("$oid "); $array = snmp_cache_oid($oid, $device, $array, "EtherLike-MIB"); }
    $end = utime(); $run = $end - $sub_start; $proctime = substr($run, 0, 5);
    echo("\n$proctime secs\n");
  }

  $polled = time();

  /// Loop interfaces in the DB and update where necessary
  $port_query = mysql_query("SELECT * FROM `interfaces` WHERE `device_id` = '".$device['device_id']."'");
  while ($port = mysql_fetch_array($port_query)) {
    
    echo(" --> " . $port['ifDescr'] . " ");   
    if($array[$device[device_id]][$port[ifIndex]]) { // Check to make sure Port data is cached.

      $this_port = &$array[$device[device_id]][$port[ifIndex]];

      $rrdfile = $config['rrd_dir'] . "/" . $device['hostname'] . "/etherlike-".$port['ifIndex'].".rrd";

      $rrd_create = $config['rrdtool'] . " create $rrdfile ";
      $rrd_create .= "RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 \
                      RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797";

      if(!file_exists($rrdfile)) {
        foreach($etherlike_oids as $oid){
          $oid = truncate(str_replace("dot3Stats", "", $oid), 19, '');
          $rrd_create .= " DS:$oid:COUNTER:600:U:100000000000";
        }
        shell_exec($rrd_create);
      }

      $rrdupdate = "N";
      foreach($etherlike_oids as $oid) {
        $data = $this_port[$oid] + 0;
        $rrdupdate .= ":$data";
      }
      rrdtool_update($rrdfile, $rrdupdate);

      #AlignmentErrors|FCSErrors|SingleCollisionFram|MultipleCollisionFr|SQETestErrors|DeferredTransmissio|LateCollisions|ExcessiveCollisions
      #InternalMacTransmit|CarrierSenseErrors|FrameTooLongs|InternalMacReceiveE|SymbolErrors

    } else {
      echo("Port Deleted?"); // Port missing from SNMP cache?
    } 
    echo("\n");
  }

?>
