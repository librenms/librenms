<?php

/**
 * Sodola / Letscom Enterprise MIB: chassis + transceiver module temperature (DOM).
 *
 * @link       https://www.librenms.org
 * @copyright  2026 LibreNMS Contributors
 */

require_once base_path('includes/discovery/sensors/sodola-dom-helper.inc.php');

// Chassis temperature (integer, °C)
$chassis = SnmpQuery::get('.1.3.6.1.4.1.12284.6.3.3.0')->value();
if (is_numeric($chassis)) {
    $t = (float) $chassis;
    if ($t > -40 && $t < 125) {
        discover_sensor(
            null,
            'temperature',
            $device,
            '.1.3.6.1.4.1.12284.6.3.3.0',
            'chassis.0',
            'sodola',
            'Chassis',
            1,
            1,
            null,
            null,
            null,
            null,
            $t,
            'snmp',
            null,
            null,
            null,
            null,
            'GAUGE'
        );
    }
}

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

    foreach ($cols as $oid => $rawRaw) {
        $oid = ltrim((string) $oid, '.');
        $parts = explode('.', $oid);
        if (count($parts) < 2) {
            continue;
        }

        $col = (int) $parts[count($parts) - 2];
        if ($col !== 26) {
            continue;
        }

        $t = sodola_parse_dom_numeric($rawRaw);
        if ($t === null || $t < -40 || $t > 125) {
            continue;
        }

        $num_oid = '.1.3.6.1.4.1.12284.5.4.1.1.26.' . $ifIndex;
        discover_sensor(
            null,
            'temperature',
            $device,
            $num_oid,
            'sfp-temp.' . $ifIndex,
            'sodola',
            $descr . ' module',
            1,
            1,
            null,
            null,
            null,
            null,
            $t,
            'snmp',
            (string) $ifIndex,
            'ports',
            null,
            null,
            'GAUGE'
        );
    }
}
