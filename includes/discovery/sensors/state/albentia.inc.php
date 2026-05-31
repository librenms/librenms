<?php
//
// Albentia state sensors for values that the YAML discoverer can't handle:
//   - GPS unbounded strings (Receiver fix, TDOP, Coordinates, Altitude)
//   - Zone BSID (variable per BS)
//
// Bounded GPS params (Antenna / Antenna mode / Anti-jamming) are handled by
// the YAML state sensors via the polling-time reverse-lookup (state_descr LIKE).
//
// For unbounded strings, the LibreNMS state machinery still needs a numeric
// state value. So we register a state translation with descr = current SNMP
// string and value = 1 at discovery time. The polling reverse-lookup matches
// the string back to value = 1 every cycle, and the UI shows the current
// state_descr next to the sensor name. When the upstream value changes the
// next discovery cycle refreshes the descr.

// --- GPS unbounded parameters ---
$gps_unbounded = [
    1 => 'GPS Receiver',
    2 => 'GPS TDOP',
    3 => 'GPS Coordinates',
    4 => 'GPS Altitude',
];

$gps = snmpwalk_cache_oid($device, 'gpsTable', [], 'ALBENTIA-COMMON-MIB', 'albentia', '-OteQUsb');

foreach ($gps as $idx => $row) {
    $name_idx = isset($row['gpsParamName']) ? (int) $row['gpsParamName'] : -1;
    if (!isset($gps_unbounded[$name_idx])) {
        continue;
    }
    $value = (string) ($row['gpsParamValue'] ?? '');
    if ($value === '') {
        continue;
    }

    // state_name unique per device so each BS keeps its own state_translations
    // (lat/lon/altitude/etc. differ per device and would otherwise overwrite).
    $state_name = 'albGpsParam' . $name_idx . '_dev' . $device['device_id'];
    $states = [
        ['value' => 1, 'generic' => 0, 'descr' => $value],
    ];
    create_state_index($state_name, $states);

    discover_sensor(
        null, 'state', $device,
        '.1.3.6.1.4.1.28087.12.3.1.1.2.' . $idx,
        (string) $name_idx,
        $state_name,
        $gps_unbounded[$name_idx],
        1, 1, null, null, null, null, 1
    );
}

// --- Per-device config strings that are uniform across sectors ---
// (Komarac firmware reports the same sectorFW / radioInfoBw / FD / CPSize on
// every sector of a given BS, so publish a single sensor per device using the
// first row found. Polling/sensors/state/albentia.inc.php refreshes the
// state_descr each cycle.)
$device_scalar_sources = [
    'sectorFW' => [
        'walk_oid'   => 'sectorsTable',
        'walk_mib'   => 'ALBENTIA-AS-MIB',
        'walk_col'   => 'sectorFW',
        'oid_prefix' => '.1.3.6.1.4.1.28087.12.10.10.1.1.6.',
        'descr'      => 'Sector firmware',
    ],
    'radioBw' => [
        'walk_oid'   => 'radioInfoTable',
        'walk_mib'   => 'ALBENTIA-AS-MIB',
        'walk_col'   => 'radioInfoBw',
        'oid_prefix' => '.1.3.6.1.4.1.28087.12.10.10.5.1.3.',
        'descr'      => 'Bandwidth',
    ],
    'radioFD' => [
        'walk_oid'   => 'radioInfoTable',
        'walk_mib'   => 'ALBENTIA-AS-MIB',
        'walk_col'   => 'radioInfoFD',
        'oid_prefix' => '.1.3.6.1.4.1.28087.12.10.10.5.1.4.',
        'descr'      => 'Frame duration',
    ],
    'radioCPSize' => [
        'walk_oid'   => 'radioInfoTable',
        'walk_mib'   => 'ALBENTIA-AS-MIB',
        'walk_col'   => 'radioInfoCPSize',
        'oid_prefix' => '.1.3.6.1.4.1.28087.12.10.10.5.1.5.',
        'descr'      => 'Cyclic prefix',
    ],
];

foreach ($device_scalar_sources as $tag => $cfg) {
    $rows = (array) snmpwalk_cache_oid(
        $device, $cfg['walk_oid'], [], $cfg['walk_mib'], 'albentia', '-OteQUsb'
    );
    foreach ($rows as $idx_enc => $row) {
        $val = (string) ($row[$cfg['walk_col']] ?? '');
        if ($val === '') {
            continue;
        }
        $state_name = 'alb' . ucfirst($tag) . '_dev' . $device['device_id'];
        $states = [['value' => 1, 'generic' => 0, 'descr' => $val]];
        create_state_index($state_name, $states);
        discover_sensor(
            null, 'state', $device,
            $cfg['oid_prefix'] . $idx_enc,
            $tag,
            $state_name,
            $cfg['descr'],
            1, 1, null, null, null, null, 1
        );
        break;
    }
}
unset($device_scalar_sources, $tag, $cfg, $rows, $idx_enc, $row, $val, $state_name, $states);

// --- Zone BSID per zone ---
//
// snmpwalk_cache_oid keys the result by the SNMP row index. For zonesTable
// (INDEX { zoneId }) the index is a string but arrives encoded as
// <len>.<asc1>.<asc2>... Decode it back to read the zone name (e.g. "unified")
// — the human-readable form is also available in the row's zoneId column.
$zones = snmpwalk_cache_oid($device, 'zonesTable', [], 'ALBENTIA-AS-MIB', 'albentia', '-OteQUsb');

foreach ($zones as $idx_enc_in => $row) {
    $bsid = (string) ($row['zoneBSID'] ?? '');
    if ($bsid === '') {
        continue;
    }
    $zone_name = (string) ($row['zoneId'] ?? $idx_enc_in);

    $state_name = 'albBsid_' . preg_replace('/[^A-Za-z0-9]/', '_', $zone_name) . '_dev' . $device['device_id'];
    $states = [
        ['value' => 1, 'generic' => 0, 'descr' => $bsid],
    ];
    create_state_index($state_name, $states);

    discover_sensor(
        null, 'state', $device,
        '.1.3.6.1.4.1.28087.12.10.10.2.1.4.' . $idx_enc_in,
        $zone_name,
        $state_name,
        'BSID',
        1, 1, null, null, null, null, 1
    );
}

unset($gps_unbounded, $gps, $zones, $idx, $row, $name_idx, $value, $state_name, $states, $bsid, $idx_enc, $ch, $zone_id);
