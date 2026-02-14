<?php
/**
 * OpenWrt Multi-Zone Temperature Sensor Discovery
 * 
 * Discovers thermal sensors from OpenWrt devices that expose them via nsExtend.
 * Uses nsExtendOutLine to get ALL lines from multi-line output.
 * 
 * Place in: /opt/librenms/includes/discovery/sensors/temperature/openwrt.inc.php
 */

if ($device['os'] == 'openwrt') {
    echo "OpenWrt Thermal Sensor Discovery\n";
    
    // Get all nsExtendOutLine data (multi-line output)
    $thermal_temps = snmpwalk_cache_oid($device, 'nsExtendOutLine."thermal-temp"', [], 'NET-SNMP-EXTEND-MIB');
    $thermal_names = snmpwalk_cache_oid($device, 'nsExtendOutLine."thermal-name"', [], 'NET-SNMP-EXTEND-MIB');
    $thermal_indices = snmpwalk_cache_oid($device, 'nsExtendOutLine."thermal-index"', [], 'NET-SNMP-EXTEND-MIB');
    
    if (empty($thermal_temps)) {
        echo "  No thermal sensors found via nsExtendOutLine\n";
        return;
    }
    
    // nsExtendOutLine uses format: nsExtendOutLine."extend-name".line_number
    // We need to iterate through line numbers
    foreach ($thermal_temps as $oid_key => $data) {
        // Extract line number from OID
        // Format: .1.3.6.1.4.1.8072.1.3.2.4.1.2.12.116.104.101.114.109.97.108.45.116.101.109.112.LINE_NUM
        preg_match('/\.(\d+)$/', $oid_key, $matches);
        $line_num = $matches[1] ?? '1';
        
        // Get values from the nested array structure
        // The data comes back with the full column name as key
        $temp_value = null;
        $sensor_name = null;
        $zone_index = null;
        
        // Find the temperature value in the data array
        foreach ($data as $key => $value) {
            if (strpos($key, 'thermal-temp') !== false) {
                $temp_value = $value;
                break;
            }
        }
        
        // Get corresponding name and index
        if (isset($thermal_names[$oid_key])) {
            foreach ($thermal_names[$oid_key] as $key => $value) {
                if (strpos($key, 'thermal-name') !== false) {
                    $sensor_name = $value;
                    break;
                }
            }
        }
        
        if (isset($thermal_indices[$oid_key])) {
            foreach ($thermal_indices[$oid_key] as $key => $value) {
                if (strpos($key, 'thermal-index') !== false) {
                    $zone_index = $value;
                    break;
                }
            }
        }
        
        // Fallback values
        $sensor_name = $sensor_name ?? "Thermal $line_num";
        $zone_index = $zone_index ?? $line_num;
        
        if (is_numeric($temp_value) && $temp_value > 0) {
            // Build OID for this specific line
            // Base: .1.3.6.1.4.1.8072.1.3.2.4.1.2 (nsExtendOutLine)
            // + length of "thermal-temp" (12)
            // + ASCII encoding of "thermal-temp"
            // + line number
            
            $extend_name = "thermal-temp";
            $ascii_encoded = implode('.', array_map('ord', str_split($extend_name)));
            $temp_oid = ".1.3.6.1.4.1.8072.1.3.2.4.1.2." . strlen($extend_name) . ".$ascii_encoded.$line_num";
            
            $descr = trim($sensor_name, '"') ?: "Thermal Zone $zone_index";
            
            echo "  Found: $descr = " . ($temp_value / 1000) . "°C (line $line_num, zone $zone_index)\n";
            
            discover_sensor(
                $valid['sensor'],
                'temperature',
                $device,
                $temp_oid,
                "thermal-line-$line_num",
                'openwrt-nsextend',
                $descr,
                1000,      // divisor - millidegrees to degrees
                1,         // multiplier
                null,      // limit_low
                null,      // limit_low_warn  
                null,      // limit_warn
                100,       // limit (100°C)
                $temp_value / 1000,
                'snmp',
                null,
                null,
                "OpenWrt thermal sensor via nsExtend (zone $zone_index)"
            );
        }
    }
    
    $thermal_count = count($thermal_temps);
    echo "  Discovered $thermal_count thermal sensor(s)\n";
}
