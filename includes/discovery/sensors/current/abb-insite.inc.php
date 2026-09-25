<?php

// Same OID tree/approach as abb-cms700.inc.php (see that file for why
// YamlDiscovery can't be used here) — InSite pro M just numbers the
// branchNamesens/phasesens subtrees differently in its own MIB.
$abb_currents = SnmpQuery::numericIndex()->options(['-OQn'])->walk('.1.3.6.1.4.1.51055.1.1')->values();
$abb_branch_names = SnmpQuery::numericIndex()->options(['-OQn'])->walk('.1.3.6.1.4.1.51055.1.19')->values();
$abb_phases = SnmpQuery::numericIndex()->options(['-OQn'])->walk('.1.3.6.1.4.1.51055.1.21')->values();

foreach ($abb_currents as $abb_oid => $abb_raw) {
    $abb_index = (int) substr((string) $abb_oid, strrpos((string) $abb_oid, '.') + 1);

    $abb_branch_name = trim((string) ($abb_branch_names['.1.3.6.1.4.1.51055.1.19.' . $abb_index] ?? ''));
    if ($abb_branch_name === '') {
        continue;
    }

    $abb_descr = "tRMSsens_{$abb_index} {$abb_branch_name}";

    $abb_phase = trim((string) ($abb_phases['.1.3.6.1.4.1.51055.1.21.' . $abb_index] ?? ''));
    if ($abb_phase !== '') {
        $abb_descr .= " (L{$abb_phase})";
    }

    // centiamps
    $abb_current = ((float) $abb_raw) / 100;

    discover_sensor(null, 'current', $device, $abb_oid, 'tRMSsens.' . $abb_index, 'abb-insite', $abb_descr, 100, 1, null, null, null, null, $abb_current);
}

unset($abb_currents, $abb_branch_names, $abb_phases, $abb_oid, $abb_raw, $abb_index, $abb_branch_name, $abb_descr, $abb_phase, $abb_current);
