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

$baseOid = '.1.3.6.1.4.1.10072.2.20.1.1.3.1.1.34';
$onuLevels = SnmpQuery::numeric()->walk($baseOid)->values();

foreach ($onuLevels as $fullOid => $hex) {
    // index is deviceIndex.boardIndex.ponIndex; only chassis-level rows
    // (deviceIndex == 1) hold the per-port alarm-level OCTET STRING.
    $parts = explode('.', substr((string) $fullOid, strlen($baseOid) + 1));
    if (count($parts) !== 3 || $parts[0] !== '1') {
        continue;
    }
    [, $boardIdx, $ponIdx] = $parts;

    // The OctetString arrives as "05 05 05 05 07 00 ..." with spaces
    // between bytes and line breaks every 16 bytes; strip whitespace
    // before splitting into individual byte values.
    $cleanHex = preg_replace('/\s+/', '', $hex);
    if (strlen((string) $cleanHex) < 2) {
        continue;
    }

    $active = $inactive = $total = 0;
    foreach (str_split(strtolower((string) $cleanHex), 2) as $b) {
        if ($b === '00') {
            continue;
        }
        $total++;
        if ($b === '05') {
            $active++;
        } elseif ($b === '07') {
            $inactive++;
        }
    }

    if ($total === 0) {
        continue;
    }

    $oid = $fullOid;
    $portLabel = "PON $boardIdx/$ponIdx";
    $indexBase = "ponPortAlmLevel.1.$boardIdx.$ponIdx";
    $group = 'EPON ONU counts';

    discover_sensor(null, 'count', $device, $oid, "$indexBase.active", 'gwd-active',
        "$portLabel Active ONU", 1, 1, null, null, null, null, $active,
        'snmp', null, null, null, $group);

    discover_sensor(null, 'count', $device, $oid, "$indexBase.inactive", 'gwd-inactive',
        "$portLabel Inactive ONU", 1, 1, null, null, null, null, $inactive,
        'snmp', null, null, null, $group);

    discover_sensor(null, 'count', $device, $oid, "$indexBase.total", 'gwd-total',
        "$portLabel Total ONU", 1, 1, null, null, null, null, $total,
        'snmp', null, null, null, $group);
}

unset($baseOid, $onuLevels, $fullOid, $parts, $boardIdx, $ponIdx, $hex,
    $cleanHex, $b, $active, $inactive, $total,
    $oid, $portLabel, $indexBase, $group);
