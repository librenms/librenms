<?php

$portData = \SnmpQuery::walk([
    'ALTEON-CHEETAH-SWITCH-MIB::agPortCurCfgPortName',
    'ALTEON-CHEETAH-SWITCH-MIB::agPortCurCfgPortAlias',
])->valuesByIndex();

if (empty($portData)) {
    return;
}

foreach ($portData as $portNumber => $entry) {
    $ifIndex = 256 + (int) $portNumber;

    $name = $entry['ALTEON-CHEETAH-SWITCH-MIB::agPortCurCfgPortName'] ?? '';
    if ($name !== '') {
        $port_stats[$ifIndex]['ifName'] = $name;
    }

    $alias = $entry['ALTEON-CHEETAH-SWITCH-MIB::agPortCurCfgPortAlias'] ?? '';
    if ($alias !== '') {
        $port_stats[$ifIndex]['ifAlias'] = $alias;
    }
}
