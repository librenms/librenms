<?php

$snmp_data = snmpwalk_cache_oid($device, 'nsExtendOutLine."custom"', [], 'NET-SNMP-EXTEND-MIB', null, '-OteQUsb');

if (! empty($snmp_data)) {
    $sensor_id = 0;
    $sensors = [];

    foreach ($snmp_data as $index => $entry) {
        if (Str::contains($entry['nsExtendOutLine'], ';')) {
            $sensor = [];
            foreach (explode(';', $entry['nsExtendOutLine']) as $splitted_data_index => $splitted_data) {
                $parts = explode(',', $splitted_data);

                if ($splitted_data_index == 0) {
                    if (isset($parts[0]) && isset($parts[1]) && isset($parts[2])) {
                        $sensor['name'] = $parts[0];
                        $sensor['type'] = $parts[1];
                        $sensor['descr'] = $parts[2];
                        $sensor['low_limit'] = $parts[3];
                        $sensor['low_warn_limit'] = $parts[4];
                        $sensor['warn_limit'] = $parts[5];
                        $sensor['high_limit'] = $parts[6];
                        $sensor['group'] = $parts[7];
                    }
                } else {
                    if (isset($parts[0]) && isset($parts[1]) && isset($parts[2])) {
                        if (! isset($sensor['state_data'])) {
                            $sensor['state_data'] = [];
                        }

                        $state['value'] = intval($parts[0]);
                        $state['generic'] = intval($parts[1]);
                        $state['graph'] = 1;
                        $state['descr'] = $parts[2];
                        array_push($sensor['state_data'], $state);
                    }
                }
                $sensors[$sensor_id] = $sensor;
            }
        } else {
            $sensors[$sensor_id]['value'] = intval($entry['nsExtendOutLine']);
            $sensors[$sensor_id]['oid'] = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.' . $index;
            $sensor_id++;
        }
    }

    foreach ($sensors as $id => $sensor) {
        if (isset($sensor['name']) && isset($sensor['type']) && isset($sensor['descr'])) {
            if (isset($sensor['state_data'])) {
                create_state_index($sensor['name'], $sensor['state_data']);
            }

            discover_sensor(null, $sensor['type'], $device, $sensor['oid'], $id, $sensor['name'], $sensor['descr'], 1, 1, $sensor['low_limit'], $sensor['low_warn_limit'], $sensor['warn_limit'], $sensor['high_limit'], $sensor['value'], 'snmp', null, null, null, $sensor['group']);

            if (isset($sensor['state_data'])) {
                create_sensor_to_state_index($device, $sensor['name'], $id);
            }
        } else {
            echo "[custom] An error occurred while reading a sensor! Please run your custom script on the target device to verify the configuration.\n";
        }
    }
}
