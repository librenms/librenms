<?php

$portData = \SnmpQuery::walk([
    'ALTEON-CHEETAH-SWITCH-MIB::agPortCurCfgPortName',
    'ALTEON-CHEETAH-SWITCH-MIB::agPortCurCfgPortAlias',
])->valuesByIndex();

foreach ($portData as $portNumber => $entry) {
    $ifIndex = 256 + (int) $portNumber;

    $name = trim((string) ($entry['ALTEON-CHEETAH-SWITCH-MIB::agPortCurCfgPortName'] ?? ''));
    $aliasRaw = trim((string) ($entry['ALTEON-CHEETAH-SWITCH-MIB::agPortCurCfgPortAlias'] ?? ''));
    $alias = $aliasRaw !== '' ? (ctype_digit($aliasRaw) ? 'Port ' . $aliasRaw : $aliasRaw) : '';

    if ($name === '' && $alias !== '') {
        $name = $alias;
    }

    if ($name !== '') {
        $port_stats[$ifIndex]['ifName'] = $name;
    }

    if ($alias !== '') {
        $port_stats[$ifIndex]['ifAlias'] = $alias;
    }
}
