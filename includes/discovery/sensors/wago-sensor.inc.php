<?php

use LibreNms\Config;

echo " WAGO-SENSOR:\n";
$allowedTypes = ['temperature', 'humidity', 'state', 'concentration'];

$entriesConfig = null;
$thresholdsConfig = null;
$statesConfig = null;

d_echo("  Searching PLC configuration items:\n");
$configPlc = Config::get('os.wago.plc');
if ($configPlc && is_array($configPlc)) {
    foreach ($configPlc as $key => $entry) {
        if (!array_key_exists('entries', $entry) || !is_array($entry['entries'])) {
            d_echo("   Skipping PLC entry '$key': missing 'entries' array!\n");
            continue;
        }

        if (array_key_exists('regexp', $entry) &&
            !preg_match($entry['regexp'], $device['hostname'])
        ) {
            // The given regexp does not match the device hostname
            continue;
        }

        d_echo("   PLC entry '$key' found!\n");
        $entriesConfig = $entry['entries'];

        if (array_key_exists('states', $entry) && is_array($entry['states'])) {
            $statesConfig = $entry['states'];
        }

        if (array_key_exists('thresholds', $entry) && is_array($entry['thresholds'])) {
            $thresholdsConfig = $entry['thresholds'];
        }
        break;
    }
    unset($key, $entry);
}
unset($configPlc);

if ($entriesConfig !== null) {
    // We have valid configuration, now walk the device and process matching items
    $oids = snmpwalk_cache_oid($device, 'wioPlcDataWriteArea', [], 'WAGO-MIB');

    foreach ($entriesConfig as $key => $entry) {
        if (!array_key_exists($key, $oids) ||        // Value not found in SNMP device result
            !array_key_exists('type', $entry) ||     // Type is a mandatory field
            !in_array($entry['type'], $allowedTypes) // Unallowed type
        ) {
            // Skip to the next value
            continue;
        }

        $type = $entry['type'];
        $oid = '.1.3.6.1.4.1.13576.10.1.100.1.1.3.' . $key;
        $descr = array_key_exists('descr', $entry) ? $entry['descr'] : ($type . ' (' . $key . ')');

        if ($type === 'state') {
            if (!$statesConfig || !array_key_exists('states', $entry) || !array_key_exists($entry['states'], $statesConfig)) {
                d_echo("   No matching state for table entry '$key'!\n");
                continue;
            }

            $stateRef = $entry['states'];
            $stateName = $stateRef . 'State';
            $states = $statesConfig[$stateRef];

            $stateRecords = [];
            foreach ($states as $stateVal => $stateEntry) {
                if (!is_numeric($stateVal) || !array_key_exists('descr', $stateEntry) || !array_key_exists('generic', $stateEntry)) {
                    d_echo("   Invalid state '$stateVal' in $stateRef entry!\n");
                    continue;
                }

                $record = [];
                $record['value'] = $stateVal;
                $record['descr'] = $stateEntry['descr'];
                $record['generic'] = $stateEntry['generic'];
                $record['graph'] = array_key_exists('graph', $stateEntry) ? $stateEntry['graph'] : 0;

                $stateRecords[] = $record;
                unset($record);
            }

            create_state_index($stateName, $stateRecords);
            discover_sensor($valid['sensor'], $type, $device, $oid, $key, $stateName, $descr, 1, 1, null, null, null, null, $current = $oids[$key]['wioPlcDataWriteArea'], 'snmp', $key);
            create_sensor_to_state_index($device, $stateName, $key);

            unset($stateRef, $stateName, $states, $stateRecords);
        } else {
            // Set default values
            $divisor = 1;
            $multiplier = 1;

            $limit_low = null;
            $warn_limit_low = null;
            $warn_limit = null;
            $limit = null;

            if (array_key_exists('thresholds', $entry) && $thresholdsConfig && array_key_exists($entry['thresholds'], $thresholdsConfig)) {
                // We have a matching entry in the thresholds table: let's define user-defined thresholds and divisors/multipliers
                $thresholds = $thresholdsConfig[$entry['thresholds']];

                $divisor = array_key_exists('divisor', $thresholds) ? $thresholds['divisor'] : 1;
                $multiplier = array_key_exists('multiplier', $thresholds) ? $thresholds['multiplier'] : 1;

                $limit_low = array_key_exists('low', $thresholds) ? $thresholds['low'] : null;
                $warn_limit_low = array_key_exists('low_warn', $thresholds) ? $thresholds['low_warn'] : null;
                $warn_limit = array_key_exists('high_warn', $thresholds) ? $thresholds['high_warn'] : null;
                $limit = array_key_exists('high', $thresholds) ? $thresholds['high'] : null;
            }

            $current = $oids[$key]['wioPlcDataWriteArea'] * $multiplier / $divisor;

            discover_sensor($valid['sensor'], $type, $device, $oid, $key, 'wago-sensor', ucwords($descr), $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', null, null, null);

            unset($divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current);
        }
        unset($type, $oid, $descr);
    }
    unset($oids, $key, $entry);
} else {
    d_echo("No PLC configuration array found!\n");
}

unset($entriesConfig, $thresholdsConfig, $statesConfig, $allowedTypes);
