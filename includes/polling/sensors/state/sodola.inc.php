<?php

/**
 * Sodola enterprise PoE state polling workarounds.
 *
 * - PoE mode: optional OS regex maps SNMP "capability" value 3 to disabled (1) for known wrong ports.
 * - PoE device: column 15 / .3 often stays 0 while real draw is only in .12284.5.2.1.1.5 (mW); .7.1.1.1.14 may be 0.
 */

use App\Facades\LibrenmsConfig;

require_once base_path('includes/discovery/sensors/sodola-poe-helper.inc.php');

$descr = (string) ($sensor['sensor_descr'] ?? '');

if (str_ends_with($descr, ' PoE mode') && $sensor_value !== '' && is_numeric($sensor_value) && (int) $sensor_value === 3) {
    $ifName = substr($descr, 0, -strlen(' PoE mode'));
    $patterns = LibrenmsConfig::getOsSetting($device['os'], 'poe_mode_force_disabled_ifname_regex');
    foreach (array_filter(
        is_array($patterns) ? $patterns : (($patterns !== null && $patterns !== '') ? [$patterns] : []),
        fn ($p) => is_string($p) && $p !== ''
    ) as $pattern) {
        if (@preg_match($pattern . 'i', $ifName)) {
            $sensor_value = '1';
            break;
        }
    }
}

$pdSnmpOff = ($sensor_value === '' || $sensor_value === '0'
    || (is_numeric($sensor_value) && (int) $sensor_value === 0));

if (str_ends_with($descr, ' PoE device') && $pdSnmpOff) {
    // Prefer companion `power` row (polled earlier on sodola via includes/polling/sensors.inc.php ordering). PD uses `poe-pd.<ifIndex>`, power uses `poe-power.<ifIndex>`.
    $pdIdx = (string) ($sensor['sensor_index'] ?? '');
    $powerIdx = $pdIdx;
    if (preg_match('/^poe-pd\.(.+)$/', $pdIdx, $m)) {
        $powerIdx = 'poe-power.' . $m[1];
    } elseif (preg_match('/^\d+$/', $pdIdx)) {
        $powerIdx = 'poe-power.' . $pdIdx;
    }
    $fromDb = false;
    if ($powerIdx !== '') {
        $watts = dbFetchCell(
            'SELECT `sensor_current` FROM `sensors` WHERE `device_id` = ? AND `sensor_class` = ? AND `sensor_type` = ? AND `sensor_index` = ? AND `sensor_deleted` = 0 LIMIT 1',
            [$device['device_id'], 'power', 'sodola', $powerIdx]
        );
        if (is_numeric($watts) && (float) $watts > 0) {
            $sensor_value = '1';
            $fromDb = true;
        }
    }

    if (! $fromDb) {
        $oid = (string) ($sensor['sensor_oid'] ?? '');

        $readMw = static function (string $oidMw) use ($device): int {
            if ($oidMw === '') {
                return 0;
            }
            $mwRaw = snmp_get($device, $oidMw, '-OUQntea');
            $mw = trim(str_replace('"', '', (string) ($mwRaw !== false ? $mwRaw : '')));

            return is_numeric($mw) ? (int) $mw : 0;
        };

        // Try legacy .14 (mW), stats .5 (mW), then explicit stats .5 by ifIndex — .14 often stays 0 while .5 matches the UI.
        $candidates = [];
        if (preg_match('/\.15\.(\d+)$/', $oid)) {
            $candidates[] = preg_replace('/\.15\.(\d+)$/', '.14.$1', $oid);
        } elseif (preg_match('/\.3\.(\d+)$/', $oid)) {
            $candidates[] = preg_replace('/\.3\.(\d+)$/', '.5.$1', $oid);
        }
        if (preg_match('/\.(\d+)$/', $oid, $m)) {
            $candidates[] = '.' . SODOLA_POE_PORT_STATS_BASE_TRIM . '.5.' . $m[1];
        }
        $candidates = array_values(array_unique(array_filter($candidates)));

        foreach ($candidates as $oidMw) {
            if ($readMw($oidMw) > 0) {
                $sensor_value = '1';
                break;
            }
        }
    }
}
