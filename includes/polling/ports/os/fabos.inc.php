<?php

foreach (snmpwalk_group($device, 'swFCPortName', 'SW-MIB') as $index => $brocade_port) {
    $index_brocade = $index + 1073741823;
    $port_stats[$index_brocade]['ifAlias'] = $brocade_port['swFCPortName'];
}
