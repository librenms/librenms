<?php

use App\Facades\LibrenmsConfig;

if (LibrenmsConfig::get('enable_ports_poe')) {
    $fetched_data_string .= 'alaPethPsePortTable ';
    $poe_by_index = SnmpQuery::walk([
        'ALCATEL-IND1-INLINE-POWER-MIB::alaPethPsePortPowerMaximum',
        'ALCATEL-IND1-INLINE-POWER-MIB::alaPethPsePortPowerActual',
    ])->valuesByIndex();

    foreach ($port_stats as $if_index => $stats) {
        if (! preg_match('/^(\d+)\/(\d+)\/(\d+)$/', $stats['ifName'] ?? '', $matches)) {
            continue;
        }

        // The PSE group encodes the VC member and slot, not the IF-MIB ifIndex.
        [, $chassis, $slot, $port] = $matches;
        $group = ($chassis - 1) * 1000 + $slot;
        $port_stats[$if_index] += $poe_by_index["$group.$port"] ?? [];
    }
}
