<?php

if($device['os_group'] == "ios") {

  $array = snmp_cache_portIfIndex ($device, $array);

  $cpe_oids = array("cpeExtPsePortEnable", "cpeExtPsePortDiscoverMode", "cpeExtPsePortDeviceDetected", "cpeExtPsePortIeeePd", 
  "cpeExtPsePortAdditionalStatus", "cpeExtPsePortPwrMax", "cpeExtPsePortPwrAllocated", "cpeExtPsePortPwrAvailable", "cpeExtPsePortPwrConsumption", 
  "cpeExtPsePortMaxPwrDrawn", "cpeExtPsePortEntPhyIndex", "cpeExtPsePortEntPhyIndex", "cpeExtPsePortPolicingCapable", "cpeExtPsePortPolicingEnable", 
  "cpeExtPsePortPolicingAction", "cpeExtPsePortPwrManAlloc");

  $peth_oids = array("pethPsePortAdminEnable", "pethPsePortPowerPairsControlAbility", "pethPsePortPowerPairs", "pethPsePortDetectionStatus", 
  "pethPsePortPowerPriority", "pethPsePortMPSAbsentCounter", "pethPsePortType", "pethPsePortPowerClassifications", "pethPsePortInvalidSignatureCounter", 
  "pethPsePortPowerDeniedCounter", "pethPsePortOverLoadCounter", "pethPsePortShortCounter");

  $sub_start = utime();
  echo("Caching Oids: ");
  foreach ($cpe_oids as $oid)      { echo("$oid "); $array = snmp_cache_slotport_oid($oid, $device, $array, "CISCO-POWER-ETHERNET-EXT-MIB"); }
  foreach ($peth_oids as $oid)     { echo("$oid "); $array = snmp_cache_slotport_oid($oid, $device, $array, "POWER-ETHERNET-MIB"); }
  $end = utime(); $run = $end - $sub_start; $proctime = substr($run, 0, 5);
  echo("\n$proctime secs\n");

  $polled = time();

  $port_query = mysql_query("SELECT * FROM `ports` WHERE `device_id` = '".$device['device_id']."'");
  while ($port = mysql_fetch_array($port_query)) {

    if($array[$device[device_id]][$port[ifIndex]]) { // Check to make sure Port data is cached.

      echo(" --> " . $port['ifDescr'] . " POE");

      /// Update RRDs
      $rrdfile = $host_rrd . "/" . safename($port['ifIndex'] . ".rrd");
      if(!is_file($rrdfile)) {
        $woo = shell_exec($config['rrdtool'] . " create $rrdfile -s 300 \
        DS:PortPwrAllocated:GAUGE:600:0:12500000000 \
        DS:PortPwrAvailable:GAUGE:600:0:12500000000 \
        DS:PortConsumption:DERIVE:600:0:12500000000 \
        DS:PortMaxPwrDrawn:GAUGE:600:0:12500000000 \
        RRA:AVERAGE:0.5:1:600 \
        RRA:AVERAGE:0.5:6:700 \
        RRA:AVERAGE:0.5:24:775 \
        RRA:AVERAGE:0.5:288:797 \
        RRA:MAX:0.5:1:600 \
        RRA:MAX:0.5:6:700 \
        RRA:MAX:0.5:24:775 \
        RRA:MAX:0.5:288:797");
      }

      $woo = "$polled:".$port['cpeExtPsePortPwrAllocated'].":".$port['cpeExtPsePortPwrAvailable'].":".$port['cpeExtPsePortPwrConsumption'].":".$port['cpeExtPsePortMaxPwrDrawn'];
      $ret = rrdtool_update("$rrdfile", $woo);

      /// End Update POE-RRD

    }

  }


}


?>
