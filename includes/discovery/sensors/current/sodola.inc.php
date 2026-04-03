<?php

/**
 * Sodola PoE port current (.12284.5.2.1.1 column 6), milliamps.
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

$poeResp = SnmpQuery::cache()
    ->numeric()
    ->options(['-OteQUsab', '-Pu', '-Ih'])
    ->walk($base);
if (! $poeResp->isValid()) {
    return;
}
$poe = [];
$poeResp->groupByIndex(1, $poe);
$statsRows = sodola_poe_port_stats_table();

foreach ($poe as $ifIndex => $cols) {
    if (! preg_match('/^\d+(\.\d+)*$/', (string) $ifIndex)) {
        continue;
    }

    $byCol = sodola_poe_row_columns($baseTrim, $cols);
    $mode = isset($byCol[$capCol]) ? (int) $byCol[$capCol] : null;
    if ($mode === null || $mode === 2 || ($mode !== 1 && $mode !== 3)) {
        continue;
    }

    $ifName = SnmpQuery::cache()->get(['IF-MIB::ifName.' . $ifIndex])->value()
        ?: ('if' . $ifIndex);
    if (! sodola_poe_port_is_managed($device, (string) $ifName, $mode)) {
        continue;
    }

    $statsCols = $statsRows[$ifIndex] ?? [];
    if ($statsCols === []) {
        continue;
    }
    $st = sodola_poe_row_columns($statsTrim, $statsCols);
    if (! array_key_exists(6, $st)) {
        continue;
    }
    $ma = max(0, (int) $st[6]);

    discover_sensor(
        null,
        'current',
        $device,
        $statsBase . '.6.' . $ifIndex,
        'poe-current.' . $ifIndex,
        'sodola',
        $ifName . ' PoE',
        1,
        1,
        null,
        null,
        null,
        null,
        $ma,
        'snmp',
        (string) $ifIndex,
        'ports',
        null,
        'PoE',
        'GAUGE'
    );
}
