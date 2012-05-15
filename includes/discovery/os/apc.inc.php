<?php

if (!$os)
{
  if (strstr(snmp_get($device, "SNMPv2-SMI::enterprises.318.1.1.1.1.1.1.0", "-Oqv", ""), "UPS")) { $os = "apcups"; }
  elseif (strstr($sysDescr, "APC Web/SNMP Management Card")) { $os = "apc"; }
  elseif (strstr($sysDescr, "APC Switched Rack PDU")) { $os = "apc"; }
  elseif (strstr($sysDescr, "APC MasterSwitch PDU")) { $os = "apc"; }
  elseif (strstr($sysDescr, "APC Metered Rack PDU")) { $os = "apc"; }
}

?>
