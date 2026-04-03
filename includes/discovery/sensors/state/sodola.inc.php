<?php

/**
 * Sodola / Letscom per-port PoE (enterprise table .12284.7.1.1.1).
 * Standard POWER-ETHERNET-MIB is not exposed on these switches.
 *
 * Port admin (web UI) is read from column poe_admin_enable_column (default 2): 0=disable, 1=enable.
 * poe_capability_column (default 6) reflects detection/capability (e.g. 2 = not available on SFP).
 *
 * PoE draw / PD: also see .12284.5.2.1.1 (power column 5, PD column 3)—discovered in power/ and here when present.
 *
 * @link       https://www.librenms.org
 * @copyright  2026 LibreNMS Contributors
 */

use App\Facades\LibrenmsConfig;

require_once base_path('includes/discovery/sensors/sodola-poe-helper.inc.php');

$base = '.1.3.6.1.4.1.12284.7.1.1.1';
$baseTrim = ltrim($base, '.');
$adminCol = (int) LibrenmsConfig::getOsSetting($device['os'], 'poe_admin_enable_column', 2);
$capCol = (int) LibrenmsConfig::getOsSetting($device['os'], 'poe_capability_column', 6);

$poe = [];
// numeric OID keys so sodola_poe_row_columns matches; power/ discovery walks the same table first and caches it.
$response = SnmpQuery::cache()
    ->numeric()
    ->options(['-OteQUsab', '-Pu', '-Ih'])
    ->walk($base);
if (! $response->isValid()) {
    return;
}
$response->groupByIndex(1, $poe);

$statsTrim = SODOLA_POE_PORT_STATS_BASE_TRIM;
$statsBase = '.' . $statsTrim;
$statsRows = sodola_poe_port_stats_table();

$useTpLinkAdmin = sodola_poe_has_tp_link_style_admin($poe, $baseTrim, $adminCol);

$state_admin = 'sodolaPoePortAdmin';
if ($useTpLinkAdmin) {
    create_state_index($state_admin, [
        ['value' => 0, 'generic' => 1, 'graph' => 0, 'descr' => 'Disabled'],
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Enabled'],
    ]);
}

$state_mode = 'sodolaPoePortMode';
create_state_index($state_mode, [
    ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Disabled'],
    ['value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'Not available'],
    ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'Enabled'],
]);

$state_pd = 'sodolaPoePdPresent';
create_state_index($state_pd, [
    ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'No PD'],
    ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'PD powered'],
]);

foreach ($poe as $ifIndex => $cols) {
    if (! preg_match('/^\d+(\.\d+)*$/', (string) $ifIndex)) {
        continue;
    }

    $byCol = sodola_poe_row_columns($baseTrim, $cols);
    $cap = isset($byCol[$capCol]) ? (int) $byCol[$capCol] : null;
    $statsCols = $statsRows[$ifIndex] ?? [];
    $st = sodola_poe_row_columns($statsTrim, $statsCols);
    $pdLegacy = isset($byCol[15]) ? (int) $byCol[15] : null;
    $pdOid = null;
    if (array_key_exists(3, $st)) {
        $pd = (int) $st[3];
        $pdOid = $statsBase . '.3.' . $ifIndex;
    } elseif ($pdLegacy !== null) {
        $pd = $pdLegacy;
        $pdOid = $base . '.15.' . $ifIndex;
    } else {
        $pd = null;
    }

    $ifName = SnmpQuery::cache()->get(['IF-MIB::ifName.' . $ifIndex])->value()
        ?: ('if' . $ifIndex);

    if (! sodola_poe_port_is_managed($device, (string) $ifName, $cap)) {
        continue;
    }

    $adminRaw = $byCol[$adminCol] ?? null;
    $hasAdminSnmp = $useTpLinkAdmin && $adminRaw !== null && $adminRaw !== ''
        && in_array((int) $adminRaw, [0, 1], true);

    if ($hasAdminSnmp) {
        $adminVal = (int) $adminRaw;
        discover_sensor(
            null,
            'state',
            $device,
            $base . '.' . $adminCol . '.' . $ifIndex,
            'poe-mode.' . $ifIndex,
            $state_admin,
            $ifName . ' PoE mode',
            1,
            1,
            null,
            null,
            null,
            null,
            $adminVal,
            'snmp',
            (string) $ifIndex,
            'ports',
            null,
            'PoE',
            'GAUGE'
        );
    } elseif ($cap !== null && $cap >= 1 && $cap <= 3) {
        discover_sensor(
            null,
            'state',
            $device,
            $base . '.' . $capCol . '.' . $ifIndex,
            'poe-mode.' . $ifIndex,
            $state_mode,
            $ifName . ' PoE mode',
            1,
            1,
            null,
            null,
            null,
            null,
            $cap,
            'snmp',
            (string) $ifIndex,
            'ports',
            null,
            'PoE',
            'GAUGE'
        );
    }

    if ($pd !== null && $pdOid !== null && ($pd === 0 || $pd === 1)) {
        discover_sensor(
            null,
            'state',
            $device,
            $pdOid,
            'poe-pd.' . $ifIndex,
            $state_pd,
            $ifName . ' PoE device',
            1,
            1,
            null,
            null,
            null,
            null,
            $pd,
            'snmp',
            (string) $ifIndex,
            'ports',
            null,
            'PoE',
            'GAUGE'
        );
    }
}
