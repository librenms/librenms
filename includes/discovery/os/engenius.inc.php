<?php

if (!$os)
{
  if (strstr($sysObjectId, ".1.3.6.1.4.1.14125.100.1.3")) { $os = "engenius"; }
  else if (strstr($sysObjectId, ".1.3.6.1.4.1.14125.101.1.3")) { $os = "engenius"; }
  else if ($sysDescr == "Wireless Access Point")
  {
    if (!empty(snmp_get($device, "SNMPv2-SMI::enterprises.14125.2.1.1.6.0", "-Oqv", "")))
    {
      $os = "engenius";
    }
  }
}

?>
