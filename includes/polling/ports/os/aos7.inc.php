<?php

use App\Facades\LibrenmsConfig;

if (LibrenmsConfig::get('enable_ports_poe')) {
    $fetched_data_string .= 'alaPethPsePortTable ';
    $poe_maximum_oid = 'ALCATEL-IND1-INLINE-POWER-MIB::alaPethPsePortPowerMaximum';
    $poe_actual_oid = 'ALCATEL-IND1-INLINE-POWER-MIB::alaPethPsePortPowerActual';
    $poe_by_index = SnmpQuery::walk([$poe_maximum_oid, $poe_actual_oid])->valuesByIndex();

    foreach ($port_stats as $if_index => $stats) {
        if (! preg_match('/^(\d+)\/(\d+)\/(\d+)$/', $stats['ifName'] ?? '', $matches)) {
            continue;
        }

        // The PSE group encodes the VC member and slot, not the IF-MIB ifIndex.
        $group = ((int) $matches[1] - 1) * 1000 + (int) $matches[2];
        $poe_index = "$group.{$matches[3]}";
        if (isset($poe_by_index[$poe_index])) {
            $port_stats[$if_index] += [
                'alaPethPsePortPowerMaximum' => $poe_by_index[$poe_index][$poe_maximum_oid] ?? null,
                'alaPethPsePortPowerActual' => $poe_by_index[$poe_index][$poe_actual_oid] ?? null,
            ];
        }
    }
}
