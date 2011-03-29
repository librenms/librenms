<?php

if (!$os)
{
  if (preg_match("/^Linux/", $sysDescr)) { $os = "linux"; }


  ## Specific Linux-derivatives

  if($os == "linux") {

    ## Check for QNAP Systems TurboNAS
    $entPhysicalMfgName = snmp_get($device, "ENTITY-MIB::entPhysicalMfgName.1", "-Osqnv");

    if(strpos($entPhysicalMfgName, "QNAP") !== FALSE) { $os = "qnap";}

  }

}

?>
