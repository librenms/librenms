<?php

/**
 * Sodola transceiver optical power (receive / transmit) from enterprise DOM table.
 *
 * @link       https://www.librenms.org
 * @copyright  2026 LibreNMS Contributors
 */

require_once base_path('includes/discovery/sensors/sodola-dom-helper.inc.php');

$dom = [];
$response = SnmpQuery::cache()
    ->hideMib()
    ->options(['-OteQUsab', '-Pu', '-Ih'])
    ->walk('.1.3.6.1.4.1.12284.5.4.1.1');
if ($response->isValid()) {
    $response->groupByIndex(1, $dom);
}

foreach ($dom as $ifIndex => $cols) {
    if (! ctype_digit((string) $ifIndex)) {
        continue;
    }

    $descr = SnmpQuery::cache()->get(['IF-MIB::ifDescr.' . $ifIndex])->value()
        ?: ('port ' . $ifIndex);

    $rx16 = $tx17 = $rx31 = $tx32 = null;
    foreach ($cols as $oid => $rawRaw) {
        $oid = ltrim((string) $oid, '.');
        $parts = explode('.', $oid);
        if (count($parts) < 2) {
            continue;
        }

        $col = (int) $parts[count($parts) - 2];
        if ($col === 16) {
            $rx16 = sodola_parse_dom_numeric($rawRaw);
        } elseif ($col === 17) {
            $tx17 = sodola_parse_dom_numeric($rawRaw);
        } elseif ($col === 31) {
            $rx31 = sodola_parse_dom_numeric($rawRaw);
        } elseif ($col === 32) {
            $tx32 = sodola_parse_dom_numeric($rawRaw);
        }
    }

    [$rx, $rxOidSuffix] = sodola_pick_dom_dbm($rx16, $rx31, 16, 31);
    [$tx, $txOidSuffix] = sodola_pick_dom_dbm($tx17, $tx32, 17, 32);

    if ($rx !== null && $rx > -80 && $rx < 20) {
        discover_sensor(
            null,
            'dbm',
            $device,
            '.1.3.6.1.4.1.12284.5.4.1.1.' . $rxOidSuffix . '.' . $ifIndex,
            'sfp-rx.' . $ifIndex,
            'sodola',
            $descr . ' Rx power',
            1,
            1,
            null,
            null,
            null,
            null,
            $rx,
            'snmp',
            (string) $ifIndex,
            'ports',
            null,
            null,
            'GAUGE'
        );
    }

    if ($tx !== null && $tx > -80 && $tx < 20) {
        discover_sensor(
            null,
            'dbm',
            $device,
            '.1.3.6.1.4.1.12284.5.4.1.1.' . $txOidSuffix . '.' . $ifIndex,
            'sfp-tx.' . $ifIndex,
            'sodola',
            $descr . ' Tx power',
            1,
            1,
            null,
            null,
            null,
            null,
            $tx,
            'snmp',
            (string) $ifIndex,
            'ports',
            null,
            null,
            'GAUGE'
        );
    }
}
