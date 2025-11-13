<?php

$port_names = snmpwalk_cache_oid($device, 'agPortCurCfgPortName', [], 'ALTEON-CHEETAH-SWITCH-MIB', 'alteonos');
if (empty($port_names)) {
    return;
}

$port_aliases = snmpwalk_cache_oid($device, 'agPortCurCfgPortAlias', $port_names, 'ALTEON-CHEETAH-SWITCH-MIB', 'alteonos');

foreach ($port_aliases as $portNumber => $entry) {
    $ifIndex = 256 + (int) $portNumber;
    if ($ifIndex <= 0) {
        continue;
    }

    $name = trim((string) ($entry['agPortCurCfgPortName'] ?? ''));
    $alias = trim((string) ($entry['agPortCurCfgPortAlias'] ?? ''));

    if ($alias !== '') {
        $trimmedAlias = preg_replace('/^Port\\s+/i', '', $alias);
        $port_stats[$ifIndex]['ifName'] = 'Port ' . trim($trimmedAlias);
    }

    if ($name !== '') {
        $port_stats[$ifIndex]['ifDescr'] = $name;
        $port_stats[$ifIndex]['ifAlias'] = $name;
    } elseif (! empty($port_stats[$ifIndex]['ifDescr'])) {
        $port_stats[$ifIndex]['ifAlias'] = $port_stats[$ifIndex]['ifDescr'];
    }
}
