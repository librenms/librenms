<?php

  ### Simple poller for UCD old style CPU. will always poll the same index.

  #$system = snmp_get($device, "ssCpuSystem.0", "-OvQ", "UCD-SNMP-MIB");
  #$user = snmp_get($device, "ssCpuUser.0", "-OvQ", "UCD-SNMP-MIB");
  $idle = snmp_get($device, "ssCpuIdle.0", "-OvQ", "UCD-SNMP-MIB");

  $proc = 100 - $idle;

?>
