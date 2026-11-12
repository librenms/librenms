<?php

use LibreNMS\Enum\SensorType;

$sfp_table = snmpwalk_cache_multi_oid($device, 'informationTable', [], 'G6-SFP-MIB');

if (is_array($sfp_table)) {
    foreach ($sfp_table as $index => $entry) {
        if (!empty($entry['informationTemperature'])) {
            $raw_temp = $entry['informationTemperature'];
            
            // Extract the Celsius float value from string like "63.5C / 146.7F"
            if (preg_match('/([0-9.-]+)\s*C/i', $raw_temp, $matches)) {
                $current = (float)$matches[1];
                
                $current_oid = '.1.3.6.1.4.1.3181.10.6.1.34.100.1.20.' . $index;
                $part = $entry['informationPartNumber'] ?? 'Unknown';
                $serial = $entry['informationSerialNumber'] ?? 'Unknown';
                $descr = 'SFP-' . $index . ' ' . $part . ' ' . $serial . ' Temperature';
                
                discover_sensor(
                    null,
                    SensorType::Temperature,
                    $device,
                    $current_oid,
                    'informationTemperature.' . $index,
                    'microsens-g6',
                    $descr,
                    1, // divisor
                    1, // multiplier
                    null,
                    null,
                    null,
                    null,
                    $current
                );
            }
        }
    }
}
unset($sfp_table);
