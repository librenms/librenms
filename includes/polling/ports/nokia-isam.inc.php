<?php

// Use proprietary asamIfExtCustomerId as ifAlias for Nokia ISAM Plattform. The default IF-MIB fields are here quite meaningless
$isam_port_stats = snmpwalk_cache_oid($device, 'asamIfExtCustomerId', [], 'ITF-MIB-EXT', 'nokia-isam');

foreach ($isam_port_stats as $index => $value) {
    $port_stats[$index]['ifAlias'] = $isam_port_stats[$index]['asamIfExtCustomerId'];
}
unset($isam_ports_stats);
