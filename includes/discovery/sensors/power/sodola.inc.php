<?php

/**
 * Sodola PoE draw per port: prefer enterprise .12284.5.2.1.1 column 5 (milliwatts).
 * Table .12284.7.1.1.1 column 14 is often zero on firmware where the web UI still shows load.
 *
 * @link       https://www.librenms.org
 * @copyright  2026 LibreNMS Contributors
 */

use App\Facades\LibrenmsConfig;

require_once base_path('includes/discovery/sensors/sodola-poe-helper.inc.php');

$base = '.1.3.6.1.4.1.12284.7.1.1.1';
$baseTrim = ltrim($base, '.');
$statsTrim = SODOLA_POE_PORT_STATS_BASE_TRIM;
$statsBase = '.' . $statsTrim;
$capCol = (int) LibrenmsConfig::getOsSetting($device['os'], 'poe_capability_column', 6);

$poe = [];
$response = SnmpQuery::cache()
    ->numeric()
    ->options(['-OteQUsab', '-Pu', '-Ih'])
    ->walk($base);
if (! $response->isValid()) {
    return;
}
$response->groupByIndex(1, $poe);

$statsRows = sodola_poe_port_stats_table();

foreach ($poe as $ifIndex => $cols) {
    if (! preg_match('/^\d+(\.\d+)*$/', (string) $ifIndex)) {
        continue;
    }

    $byCol = sodola_poe_row_columns($baseTrim, $cols);
    $mode = isset($byCol[$capCol]) ? (int) $byCol[$capCol] : null;

    if ($mode === null || $mode === 2) {
        continue;
    }

    if ($mode !== 1 && $mode !== 3) {
        continue;
    }

    $ifName = SnmpQuery::cache()->get(['IF-MIB::ifName.' . $ifIndex])->value()
        ?: ('if' . $ifIndex);

    if (! sodola_poe_port_is_managed($device, (string) $ifName, $mode)) {
        continue;
    }

    $statsCols = $statsRows[$ifIndex] ?? [];
    $st = sodola_poe_row_columns($statsTrim, $statsCols);
    $mwLegacy = isset($byCol[14]) ? (int) $byCol[14] : 0;
    if ($statsCols !== []) {
        $powerOid = $statsBase . '.5.' . $ifIndex;
        $mw = max(0, (int) ($st[5] ?? 0));
    } else {
        $powerOid = $base . '.14.' . $ifIndex;
        $mw = max(0, $mwLegacy);
    }

    discover_sensor(
        null,
        'power',
        $device,
        $powerOid,
        'poe-power.' . $ifIndex,
        'sodola',
        $ifName . ' PoE',
        1000,
        1,
        null,
        null,
        null,
        null,
        $mw,
        'snmp',
        (string) $ifIndex,
        'ports',
        null,
        'PoE',
        'GAUGE'
    );
}
