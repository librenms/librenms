<?php

/**
 * Sodola enterprise PoE state polling workarounds.
 *
 * - PoE mode: optional OS regex maps SNMP "capability" value 3 to disabled (1) for known wrong ports.
 * - PoE device: column 15 / .3 often stays 0 while real draw is only in .12284.5.2.1.1.5 (mW); .7.1.1.1.14 may be 0.
 */

use App\Facades\LibrenmsConfig;
use App\Models\Sensor;

require_once base_path('includes/discovery/sensors/sodola-poe-helper.inc.php');

$descr = (string) ($sensor['sensor_descr'] ?? '');

if (str_ends_with($descr, ' PoE mode') && $sensor_value !== '' && is_numeric($sensor_value) && (int) $sensor_value === 3) {
    $ifName = substr($descr, 0, -strlen(' PoE mode'));
    $patterns = LibrenmsConfig::getOsSetting($device['os'], 'poe_mode_force_disabled_ifname_regex');
    foreach (array_filter(
        is_array($patterns) ? $patterns : (($patterns !== null && $patterns !== '') ? [$patterns] : []),
        fn ($p) => is_string($p) && $p !== ''
    ) as $pattern) {
        if (sodola_poe_ifname_matches_regex($pattern, $ifName)) {
            $sensor_value = '1';
            break;
        }
    }
}

$pdSnmpOff = ($sensor_value === '' || $sensor_value === '0'
    || (is_numeric($sensor_value) && (int) $sensor_value === 0));

if (str_ends_with($descr, ' PoE device') && $pdSnmpOff) {
    // Prefer companion `power` row when it was already updated this poll (`poe-pd.<ifIndex>` vs `poe-power.<ifIndex>`).
    $pdIdx = (string) ($sensor['sensor_index'] ?? '');
    $powerIdx = $pdIdx;
    if (preg_match('/^poe-pd\.(.+)$/', $pdIdx, $m)) {
        $powerIdx = 'poe-power.' . $m[1];
    } elseif (preg_match('/^\d+$/', $pdIdx)) {
        $powerIdx = 'poe-power.' . $pdIdx;
    }
    $fromDb = false;
    if ($powerIdx !== '') {
        $watts = Sensor::query()
            ->where('device_id', $device['device_id'])
            ->where('sensor_class', 'power')
            ->where('sensor_type', 'sodola')
            ->where('sensor_index', $powerIdx)
            ->where('sensor_deleted', 0)
            ->value('sensor_current');
        if (is_numeric($watts) && (float) $watts > 0) {
            $sensor_value = '1';
            $fromDb = true;
        }
    }

    if (! $fromDb) {
        $oid = (string) ($sensor['sensor_oid'] ?? '');

        $readMw = static function (string $oidMw): int {
            if ($oidMw === '') {
                return 0;
            }
            $resp = SnmpQuery::numeric()->options(['-OUQntea', '-Pu'])->get($oidMw);
            if (! $resp->isValid()) {
                return 0;
            }
            $mw = trim(str_replace('"', '', $resp->value()));

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
