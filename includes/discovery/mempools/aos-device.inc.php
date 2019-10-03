<?php
if ($device['os'] == 'aos') {
  echo 'Alcatel-Lucent OS: ';
  if (strpos($device['sysObjectID'],'1.3.6.1.4.1.6486.801')) {
    // New AOS 7
    $total   = snmp_get($device, '.1.3.6.1.4.1.6486.801.1.1.1.2.1.1.3.4.0', '-OvQ', 'ALCATEL-IND1-SYSTEM-MIB', 'aos7'); // systemHardwareMemorySize
    $percent = snmp_get($device, '.1.3.6.1.4.1.6486.801.1.2.1.16.1.1.1.1.1.8.0', '-OvQ', $mib, 'aos7');                 // healthModuleMemory1MinAvg
    $used    = $total / 100 * $percent;
    $free    = ($total - $used);

    if (is_numeric($total) && is_numeric($percent)) {
      $total *= 1024; // AOS7 reports total memory in MB
      // Use HC bit for new aos
      discover_mempool($valid_mempool, $device, 0, 'aos-device', 'Device Memory', 1, null, null);
    }
  }
  elseif (strpos($device['sysObjectID'],'1.3.6.1.4.1.6486.800.1.1.2.2.4')) {
    ;
  }
  else {
    //NOTE. Because Alcatel changed their MIBs content (same oid names have different indexes), here used only numeric OIDs.

    $total   = snmp_get($device, '.1.3.6.1.4.1.6486.800.1.1.1.2.1.1.3.4.0', '-OvQ', 'ALCATEL-IND1-SYSTEM-MIB', 'aos'); // systemHardwareMemorySize
    $percent = snmp_get($device, '.1.3.6.1.4.1.6486.800.1.2.1.16.1.1.1.10.0', '-OvQ', $mib, 'aos');                    // healthModuleMemory1MinAvg
    $used    = $total / 100 * $percent;
    $free    = ($total - $used);

    if (is_numeric($total) && is_numeric($percent)) {
      discover_mempool($valid_mempool, $device, 0, 'aos-device', 'Device Memory', '1', null, null);
    }
  }
}

