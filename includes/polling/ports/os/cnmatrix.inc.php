<?php

$int_desc = snmpwalk_group($device, 'ifMainDesc', 'ARICENT-CFA-MIB');
foreach ($port_stats as $index => $port) {
    $port_stats[$index]['ifAlias'] = $int_desc[$index]['ifMainDesc'];
}
