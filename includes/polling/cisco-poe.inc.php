<?php

if($device['os'] == "IOS") {

  $array = snmp_cache_portIfIndex ($device, $array);

  $cpe_oids = array("cpeExtPsePortEnable", "cpeExtPsePortDiscoverMode", "cpeExtPsePortDeviceDetected", "cpeExtPsePortIeeePd", "cpeExtPsePortAdditionalStatus", "cpeExtPsePortPwrMax", "cpeExtPsePortPwrAllocated", "cpeExtPsePortPwrAvailable", "cpeExtPsePortPwrConsumption", "cpeExtPsePortMaxPwrDrawn", "cpeExtPsePortEntPhyIndex", "cpeExtPsePortEntPhyIndex", "cpeExtPsePortPolicingCapable", "cpeExtPsePortPolicingEnable", "cpeExtPsePortPolicingAction", "cpeExtPsePortPwrManAlloc");
  $peth_oids = array("pethPsePortAdminEnable", "pethPsePortPowerPairsControlAbility", "pethPsePortPowerPairs", "pethPsePortDetectionStatus", "pethPsePortPowerPriority", "pethPsePortMPSAbsentCounter", "pethPsePortType", "pethPsePortPowerClassifications", "pethPsePortInvalidSignatureCounter", "pethPsePortPowerDeniedCounter", "pethPsePortOverLoadCounter", "pethPsePortShortCounter");

  $sub_start = utime();
  echo("Caching Oids: ");
  foreach ($cpe_oids as $oid)      { echo("$oid "); $array = snmp_cache_slotport_oid($oid, $device, $array, "CISCO-POWER-ETHERNET-EXT-MIB"); }
  foreach ($peth_oids as $oid)      { echo("$oid "); $array = snmp_cache_slotport_oid($oid, $device, $array, "POWER-ETHERNET-MIB"); }
  $end = utime(); $run = $end - $sub_start; $proctime = substr($run, 0, 5);
  echo("\n$proctime secs\n");

}


?>
