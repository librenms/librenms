<?php

/*
 * LibreNMS GW Delight (EasyPath EPON) count sensors
 *
 * Per-PON ONU statistics derived from GW-EPON-MIB::ponPortAllOnuAlmLevel,
 * an OnuAlarmLevelList (OCTET STRING, 64 bytes) where each byte specifies
 * the alarm level of one ONU slot on the port. The MIB textual convention
 * defines the per-octet values as:
 *
 *   0  null         (slot empty)
 *   1  vital
 *   2  major
 *   3  minor
 *   4  warning
 *   5  clear        (ONU online, no alarm)
 *   6  information
 *   7  off-line     (ONU registered but currently off-line)
 *
 * From this single bulkwalk the sensor produces three counters per active
 * PON port: Active (level 5), Inactive (level 7), Total (any non-null
 * slot), comparable to the BDCOM "Active/Inactive Onu Num" presentation
 * that GW Delight does not expose as separate OIDs.
 */

$onuLevels = SnmpQuery::walk('GW-EPON-MIB::ponPortAllOnuAlmLevel')->table(3);

// only chassis-level rows (deviceIndex 1) hold the per-port alarm-level list;
// the MIB's empty DISPLAY-HINT makes net-snmp print one digit per ONU slot
foreach ($onuLevels[1] ?? [] as $boardIdx => $ports) {
    foreach ($ports as $ponIdx => $row) {
        $levels = str_split((string) ($row['GW-EPON-MIB::ponPortAllOnuAlmLevel'] ?? ''));
        $total = count(array_filter($levels, fn ($level) => $level !== '0' && $level !== ''));
        if ($total === 0) {
            continue;
        }

        $oid = ".1.3.6.1.4.1.10072.2.20.1.1.3.1.1.34.1.$boardIdx.$ponIdx";
        $index = "ponPortAlmLevel.1.$boardIdx.$ponIdx";
        $counts = [
            'active' => count(array_keys($levels, '5', true)),
            'inactive' => count(array_keys($levels, '7', true)),
            'total' => $total,
        ];

        foreach ($counts as $type => $value) {
            discover_sensor(null, 'count', $device, $oid, "$index.$type", "gwd-$type",
                "PON $boardIdx/$ponIdx " . ucfirst($type) . ' ONU', 1, 1, null, null, null, null, $value,
                'snmp', null, null, null, 'EPON ONU counts');
        }
    }
}

unset($onuLevels, $boardIdx, $ports, $ponIdx, $row, $levels, $total, $oid, $index, $counts, $type, $value);
