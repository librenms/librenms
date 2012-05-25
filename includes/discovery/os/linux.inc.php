<?php

if (!$os)
{
  if (preg_match("/^Linux/", $sysDescr)) { $os = "linux"; }

  // Specific Linux-derivatives

  if ($os == "linux")
  {
    // Check for QNAP Systems TurboNAS
    $entPhysicalMfgName = snmp_get($device, "ENTITY-MIB::entPhysicalMfgName.1", "-Osqnv");

    if (strstr($sysObjectId, ".1.3.6.1.4.1.5528.100.20.10.2014")) { $os = "netbotz"; }
    elseif (strstr($sysDescr, "endian")) { $os = "endian"; }
    elseif (preg_match("/Cisco Small Business/", $sysDescr)) { $os = "ciscosmblinux"; }
    elseif (strpos($entPhysicalMfgName, "QNAP") !== FALSE) { $os = "qnap"; }
    else
    {
      // Check for Synology DSM
      $hrSystemInitialLoadParameters = trim(snmp_get($device, "hrSystemInitialLoadParameters.0", "-Osqnv"));

      if (strpos($hrSystemInitialLoadParameters, "syno_hw_version") !== FALSE) { $os = "dsm"; }
      else
      {
        // Check for Carel PCOweb
        $roomTemp = trim(snmp_get($device,"roomTemp.0", "-OqvU", "CAREL-ug40cdz-MIB"));

        if (is_numeric($roomTemp))
        {
          $os = "pcoweb";
        }
      }
    }
  }
}

?>
