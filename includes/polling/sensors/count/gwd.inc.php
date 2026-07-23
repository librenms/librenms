<?php

/*
 * LibreNMS GW Delight (EasyPath EPON) count sensor poller.
 *
 * For sensors of type gwd-active / gwd-inactive / gwd-total we re-fetch
 * GW-EPON-MIB::ponPortAllOnuAlmLevel (an OCTET STRING describing the
 * per-ONU alarm level for one PON port) and recount the byte map. The
 * raw $sensor_value provided by the bulk poller arrives numeric-cast to 0
 * for OctetString OIDs, so we read the value through SnmpQuery here.
 *
 * Per the GW-EPON-MIB OnuAlarmLevelList textual convention each byte is
 * an alarm-level: 0 null (empty), 5 clear (active), 7 off-line (inactive).
 */

if (in_array($sensor['sensor_type'] ?? '', ['gwd-active', 'gwd-inactive', 'gwd-total'], true)) {
    $rawHex = (string) SnmpQuery::numeric()
        ->get($sensor['sensor_oid'])
        ->value();

    // The OctetString arrives as "05 05 05 05 07 00 ..." with spaces
    // between bytes and line breaks every 16 bytes; strip whitespace
    // before splitting into individual byte values.
    $cleanHex = preg_replace('/\s+/', '', $rawHex);

    $active = $inactive = $total = 0;
    if (strlen((string) $cleanHex) >= 2) {
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
    }

    $sensor_value = match ($sensor['sensor_type']) {
        'gwd-active' => $active,
        'gwd-inactive' => $inactive,
        'gwd-total' => $total,
    };

    unset($rawHex, $cleanHex, $active, $inactive, $total, $b);
}
