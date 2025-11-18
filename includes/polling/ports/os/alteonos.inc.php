<?php

require_once base_path('includes/common/alteon-snmp.inc.php');

$portData = alteon_snmp($device)
    ->walk([
        'ALTEON-CHEETAH-SWITCH-MIB::agPortCurCfgPortName',
        'ALTEON-CHEETAH-SWITCH-MIB::agPortCurCfgPortAlias',
    ])->valuesByIndex();

if (empty($portData)) {
    return;
}

foreach ($portData as $portNumber => $entry) {
    $ifIndex = 256 + (int) $portNumber;
    if ($ifIndex <= 0) {
        continue;
    }

    $name = trim((string) ($entry['ALTEON-CHEETAH-SWITCH-MIB::agPortCurCfgPortName'] ?? ''));
    $alias = trim((string) ($entry['ALTEON-CHEETAH-SWITCH-MIB::agPortCurCfgPortAlias'] ?? ''));

    if ($alias !== '') {
        $trimmedAlias = preg_replace('/^Port\s+/i', '', $alias);
        $port_stats[$ifIndex]['ifName'] = 'Port ' . trim((string) $trimmedAlias);
    }

    if ($name !== '') {
        $port_stats[$ifIndex]['ifDescr'] = $name;
        $port_stats[$ifIndex]['ifAlias'] = $name;
    } elseif (! empty($port_stats[$ifIndex]['ifDescr'])) {
        $port_stats[$ifIndex]['ifAlias'] = $port_stats[$ifIndex]['ifDescr'];
    }
}
