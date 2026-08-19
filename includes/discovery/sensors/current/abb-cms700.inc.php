<?php

// The MIB declares each branch as its own uniquely-named scalar object
// (TRMSsensN / BranchNamesensN / PhasesensN, N = 1..256) rather than a
// real SNMP table with a shared INDEX, so YamlDiscovery can't walk it
// (it collapses everything into one row and warns "Array to string
// conversion"). Walk the numeric OID subtrees directly instead.
$abb_currents = SnmpQuery::numericIndex()->options(['-OQn'])->walk('.1.3.6.1.4.1.51055.1.1')->values();
$abb_branch_names = SnmpQuery::numericIndex()->options(['-OQn'])->walk('.1.3.6.1.4.1.51055.1.24')->values();
$abb_phases = SnmpQuery::numericIndex()->options(['-OQn'])->walk('.1.3.6.1.4.1.51055.1.26')->values();

foreach ($abb_currents as $abb_oid => $abb_raw) {
    $abb_index = (int) substr((string) $abb_oid, strrpos((string) $abb_oid, '.') + 1);

    // empty BranchNamesens means the branch isn't physically wired up
    $abb_branch_name = trim((string) ($abb_branch_names['.1.3.6.1.4.1.51055.1.24.' . $abb_index] ?? ''));
    if ($abb_branch_name === '') {
        continue;
    }

    $abb_descr = "TRMSsens_{$abb_index} {$abb_branch_name}";

    $abb_phase = trim((string) ($abb_phases['.1.3.6.1.4.1.51055.1.26.' . $abb_index] ?? ''));
    if ($abb_phase !== '') {
        $abb_descr .= " (L{$abb_phase})";
    }

    // centiamps
    $abb_current = ((float) $abb_raw) / 100;

    discover_sensor(null, 'current', $device, $abb_oid, 'TRMSsens.' . $abb_index, 'abb-cms700', $abb_descr, 100, 1, null, null, null, null, $abb_current);
}

unset($abb_currents, $abb_branch_names, $abb_phases, $abb_oid, $abb_raw, $abb_index, $abb_branch_name, $abb_descr, $abb_phase, $abb_current);
