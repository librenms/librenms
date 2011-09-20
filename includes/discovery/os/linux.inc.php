<?php

if (!$os)
{
  if (preg_match("/^Linux/", $sysDescr)) { $os = "linux"; }


  ## Specific Linux-derivatives

  if ($os == "linux") {

    ## Check for QNAP Systems TurboNAS
    $entPhysicalMfgName = snmp_get($device, "ENTITY-MIB::entPhysicalMfgName.1", "-Osqnv");

    if (strpos($entPhysicalMfgName, "QNAP") !== FALSE) { $os = "qnap";}
    elseif(strstr($sysObjectId, ".1.3.6.1.4.1.5528.100.20.10.2014")) { $os = "netbotz"; }

  }

}

?>
