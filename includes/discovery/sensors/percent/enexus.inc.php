<?php

if (is_array($pre_cache['enexus_battery_test_result_table'])) {
    $numeric_results = array_filter($pre_cache['enexus_battery_test_result_table'], function ($key) {
        return is_int($key);
    }, ARRAY_FILTER_USE_KEY);
    if (empty($numeric_results)) {
        return;
    }
    $last_index = max(array_keys($numeric_results));
    $batteryQualityResult = $numeric_results[$last_index]['batteryTestResultQuality'];
    discover_sensor(
        null,
        'percent',
        $device,
        '.1.3.6.1.4.1.12148.10.10.16.4.1.5',
        'batteryTestResultQuality',
        'batteryTestQuality',
        'Battery Test Quality',
        1,
        1,
        null,
        null,
        null,
        null,
        $batteryQualityResult,
        'snmp',
        null,
        null,
        null,
        null,
        'gauge'
    );

    unset($numeric_results, $last_index, $batteryQualityResult);
}
