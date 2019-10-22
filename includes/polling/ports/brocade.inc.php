<?php
  
$brocade_stats = snmpwalk_group($device, 'swFCPortName', 'SW-MIB', 1, $brocade_stats);

$brocade_ports = [];
foreach ($brocade_stats as $index => $port) {
   $index_brocade=$index + 1073741823;
   $brocade_ports[$index_brocade]['ifAlias'] = $brocade_stats[$index]['swFCPortName'];
}

$port_stats = array_replace_recursive($brocade_ports, $port_stats);
