<?php

  echo("Polling IP-MIB ipSystemStats ");

  $ipSystemStats = snmpwalk_cache_oid("ipSystemStats", $device, $ipSystemStats, "IP-MIB");
  $ipSystemStats = $ipSystemStats[$device[device_id]];

  foreach($ipSystemStats as $af => $stats) {

   echo("$af ");

    $oids = array('ipSystemStatsInReceives','ipSystemStatsInHdrErrors','ipSystemStatsInAddrErrors','ipSystemStatsInUnknownProtos','ipSystemStatsInForwDatagrams','ipSystemStatsReasmReqds',
                  'ipSystemStatsReasmOKs','ipSystemStatsReasmFails','ipSystemStatsInDiscards','ipSystemStatsInDelivers','ipSystemStatsOutRequests','ipSystemStatsOutNoRoutes','ipSystemStatsOutDiscards',
                  'ipSystemStatsOutFragFails','ipSystemStatsOutFragCreates','ipSystemStatsOutForwDatagrams');

    if(isset($stats['ipSystemStatsHCInReceives'])) { $stats['ipSystemStatsInReceives'] = $stats['ipSystemStatsHCInReceives']; }
    if(isset($stats['ipSystemStatsHCInForwDatagrams'])) { $stats['ipSystemStatsInForwDatagrams'] = $stats['ipSystemStatsHCInForwDatagrams']; }
    if(isset($stats['ipSystemStatsHCInDelivers'])) { $stats['ipSystemStatsInDelivers'] = $stats['ipSystemStatsHCInDelivers']; }
    if(isset($stats['ipSystemStatsHCOutRequests'])) { $stats['ipSystemStatsOutRequests'] = $stats['ipSystemStatsHCOutRequests']; }
    if(isset($stats['ipSystemStatsHCOutForwDatagrams'])) { $stats['ipSystemStatsOutForwDatagrams'] = $stats['ipSystemStatsHCOutForwDatagrams']; }

    unset($snmpstring, $rrdupdate, $snmpdata, $snmpdata_cmd, $rrd_create);

    $rrdfile = $config['rrd_dir'] . "/" . $device['hostname'] . "/ipSystemStats-".$af.".rrd";

    $rrd_create = $config['rrdtool'] . " create $rrdfile ";
    $rrd_create .= "RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797";
    
    $rrdupdate = "N";

    foreach($oids as $oid){
      $oid_ds = str_replace("ipSystemStats", "", $oid);
      $oid_ds = truncate($oid_ds, 19, '');
      $rrd_create .= " DS:$oid_ds:COUNTER:600:U:100000000000";
      $snmpstring .= " $oid.0";
      if(strstr($stats[$oid], "No") || strstr($stats[$oid], "d") || strstr($stats[$oid], "s")) { $stats[$oid] = "0"; }
      $rrdupdate  .= ":".$stats[$oid]; 
    }
    if(!file_exists($rrdfile)) { shell_exec($rrd_create); }

    rrdtool_update($rrdfile, $rrdupdate);

    unset($snmpstring, $rrdupdate, $snmpdata, $snmpdata_cmd, $rrd_create);
  }

echo("\n");


?>
