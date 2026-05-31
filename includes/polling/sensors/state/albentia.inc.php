<?php
//
// Albentia polling-side hook for the state sensors created by
// includes/discovery/sensors/state/albentia.inc.php
//
// For the unbounded GPS params (Receiver, TDOP, Coordinates, Altitude) and
// the per-zone BSID, the actual SNMP value is a string that may change
// between polls (coords drift, altitude jitter, etc.) but we still want the
// UI to show the *current* value. We achieve that by:
//   1. Letting the upstream poller read the SNMP string into $sensor_value.
//   2. Rewriting the state_translations.state_descr row to that current value
//      so the UI shows fresh data each cycle.
//   3. Returning sensor_value = 1 so the state sensor stays "in state 1"
//      and the framework looks up the freshly-updated descr.
//
// Bounded GPS sensors (Antenna/Antenna mode/Anti-jamming) are NOT touched
// here; the YAML discoverer handles them with fixed state translations and
// the upstream reverse-lookup already maps their strings correctly.

use Illuminate\Support\Facades\DB;

$type = (string) ($sensor['sensor_type'] ?? '');
$is_unbounded_gps = str_starts_with($type, 'albGpsParam') && preg_match('/^albGpsParam[1234]_dev\d+$/', $type);
$is_bsid          = str_starts_with($type, 'albBsid_');
$is_dev_scalar    = (bool) preg_match('/^alb(SectorFW|RadioBw|RadioFD|RadioCPSize)_dev\d+$/', $type);

if ($is_unbounded_gps || $is_bsid || $is_dev_scalar) {
    $raw = trim((string) $sensor_value);
    if ($raw !== '') {
        DB::table('state_translations')
            ->join('sensors_to_state_indexes', 'state_translations.state_index_id', '=', 'sensors_to_state_indexes.state_index_id')
            ->where('sensors_to_state_indexes.sensor_id', $sensor['sensor_id'])
            ->where('state_translations.state_value', 1)
            ->update(['state_translations.state_descr' => $raw]);
    }
    $sensor_value = 1;
}

unset($type, $is_unbounded_gps, $is_bsid, $is_dev_scalar, $raw);
