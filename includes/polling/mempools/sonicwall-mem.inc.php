<?php

if ($device['os'] == 'sonicwall') {
  echo 'SonicWALL-MEMORY-POOL: ';
  $perc = str_replace('"', "", snmp_get($device, 'SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicCurrentRAMUtil.0', '-OvQ'));
  if (is_numeric($perc)) {
    $mempool['perc'] = $perc;
    $mempool['used'] = $perc;
    $mempool['total'] = 100;
    $mempool['free'] = 100 - $perc;
  }
}
