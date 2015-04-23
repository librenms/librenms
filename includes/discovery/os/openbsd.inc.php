<?php

if (!$os)
{
  if (strstr($sysObjectId, ".1.3.6.1.4.1.30155.23.1")) { $os = "openbsd"; } # snmpd
  if (preg_match("/OpenBSD/", $sysDescr)) { $os = "openbsd"; } # Net-SNMP
}

?>
