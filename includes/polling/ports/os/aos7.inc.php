<?php

use App\Facades\LibrenmsConfig;

if (LibrenmsConfig::get('enable_ports_poe')) {
    $fetched_data_string .= 'alaPethPsePortTable ';
    // The available AOS6 MIB uses enterprise 6486.800; AOS7 uses 6486.801.
    $poe_maximum_oid = '.1.3.6.1.4.1.6486.801.1.2.1.27.1.1.1.1.1';
    $poe_actual_oid = '.1.3.6.1.4.1.6486.801.1.2.1.27.1.1.1.1.2';
    $port_stats_poe = SnmpQuery::numeric()->walk([$poe_maximum_oid, $poe_actual_oid])->groupByIndex(2);

    foreach ($port_stats as $if_index => $stats) {
        if (! preg_match('/^(\d+)\/(\d+)\/(\d+)$/', $stats['ifName'] ?? '', $matches)) {
            continue;
        }

        // The PSE group encodes the VC member and slot, not the IF-MIB ifIndex.
        $group = ((int) $matches[1] - 1) * 1000 + (int) $matches[2];
        $poe_index = $group . '.' . $matches[3];
        if (isset($port_stats_poe[$poe_index])) {
            $port_stats[$if_index] += [
                'alaPethPsePortPowerMaximum' => $port_stats_poe[$poe_index]["$poe_maximum_oid.$poe_index"] ?? null,
                'alaPethPsePortPowerActual' => $port_stats_poe[$poe_index]["$poe_actual_oid.$poe_index"] ?? null,
            ];
        }
    }
}
