<?php

if (!$os)
{
  if (strstr($sysDescr, "APC Web/SNMP Management Card")) { $os = "apc"; }
  elseif (strstr($sysDescr, "APC Switched Rack PDU")) { $os = "apc"; }
  elseif (strstr($sysDescr, "APC MasterSwitch PDU")) { $os = "apc"; }
  elseif (strstr($sysDescr, "APC Metered Rack PDU")) { $os = "apc"; }
}

?>