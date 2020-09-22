<?php

$brocade_stats = snmpwalk_group($device, 'swFCPortName', 'SW-MIB', 1, $brocade_stats);

foreach ($brocade_stats as $index => $port) {
    $index_brocade = $index + 1073741823;
    $port_stats[$index_brocade]['ifAlias'] = $brocade_stats[$index]['swFCPortName'];
}
