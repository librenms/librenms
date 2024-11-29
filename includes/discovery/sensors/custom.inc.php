<?php

$gpio_mon_data = snmpwalk_cache_oid($device, 'nsExtendOutLine."custom"', [], 'NET-SNMP-EXTEND-MIB', null, '-OteQUsb');

if (! empty($gpio_mon_data)) {
    $sensor_index = 0;
    $sensors = [];

    foreach ($gpio_mon_data as $index => $entry) {
        if (Str::contains($entry['nsExtendOutLine'], ';')) {
            $splitted_data_array = explode(';', $entry['nsExtendOutLine']);
            $sensor_data = [];
            foreach ($splitted_data_array as $splitted_data_index => $splitted_data) {
                $sensor_data_parts = explode(',', $splitted_data);

                if ($splitted_data_index == 0) {
                    if (isset($sensor_data_parts[0]) && isset($sensor_data_parts[1]) && isset($sensor_data_parts[2])) {
                        $sensor_data['name'] = $sensor_data_parts[0];
                        $sensor_data['type'] = $sensor_data_parts[1];
                        $sensor_data['descr'] = $sensor_data_parts[2];
                        $sensor_data['low_limit'] = $sensor_data_parts[3];
                        $sensor_data['low_warn_limit'] = $sensor_data_parts[4];
                        $sensor_data['warn_limit'] = $sensor_data_parts[5];
                        $sensor_data['high_limit'] = $sensor_data_parts[6];
                        $sensor_data['group'] = $sensor_data_parts[7];
                    }
                } else {
                    if (isset($sensor_data_parts[0]) && isset($sensor_data_parts[1]) && isset($sensor_data_parts[2])) {
                        if (! isset($sensor_data['state_data'])) {
                            $sensor_data['state_data'] = [];
                        }

                        $state_data['value'] = intval($sensor_data_parts[0]);
                        $state_data['generic'] = intval($sensor_data_parts[1]);
                        $state_data['graph'] = 1;
                        $state_data['descr'] = $sensor_data_parts[2];
                        array_push($sensor_data['state_data'], $state_data);
                    }
                }
                $sensors[$sensor_index] = $sensor_data;
            }
        } else {
            $sensors[$sensor_index]['value'] = intval($entry['nsExtendOutLine']);
            $sensors[$sensor_index]['oid'] = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.' . $index;
            $sensor_index++;
        }
    }

    foreach ($sensors as $sensor_id => $sensor_data) {
        if (isset($sensor_data['name']) && isset($sensor_data['type']) && isset($sensor_data['descr'])) {
            if (isset($sensor_data['state_data'])) {
                create_state_index($sensor_data['name'], $sensor_data['state_data']);
            }

            discover_sensor(null, $sensor_data['type'], $device, $sensor_data['oid'], $sensor_id, $sensor_data['name'], $sensor_data['descr'], 1, 1, $sensor_data['low_limit'], $sensor_data['low_warn_limit'], $sensor_data['warn_limit'], $sensor_data['high_limit'], $sensor_data['value'], 'snmp', null, null, null, $sensor_data['group']);

            if (isset($sensor_data['state_data'])) {
                create_sensor_to_state_index($device, $sensor_data['name'], $sensor_id);
            }
        } else {
            echo "[custom] An error occurred while reading a sensor! Please run your custom script on the target device to verify the configuration.\n";
        }
    }
}
