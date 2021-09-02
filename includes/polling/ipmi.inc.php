<?php

use LibreNMS\Config;
use LibreNMS\RRD\RrdDefinition;

$ipmi_rows = dbFetchRows("SELECT * FROM sensors WHERE device_id = ? AND poller_type='ipmi'", [$device['device_id']]);

if (is_array($ipmi_rows)) {
    d_echo($ipmi_rows);

    if ($ipmi['host'] = $attribs['ipmi_hostname']) {
        $ipmi['port'] = filter_var($attribs['ipmi_port'], FILTER_VALIDATE_INT) ? $attribs['ipmi_port'] : '623';
        $ipmi['user'] = $attribs['ipmi_username'];
        $ipmi['password'] = $attribs['ipmi_password'];
        $ipmi['type'] = $attribs['ipmi_type'];

        echo 'Fetching IPMI sensor data...';

        $cmd = [Config::get('ipmitool', 'ipmitool')];
        if (Config::get('own_hostname') != $device['hostname'] || $ipmi['host'] != 'localhost') {
            array_push($cmd, '-H', $ipmi['host'], '-U', $ipmi['user'], '-P', $ipmi['password'], '-L', 'USER', '-p', $ipmi['port']);
        }

        // Check to see if we know which IPMI interface to use
        // so we dont use wrong arguments for ipmitool
        if ($ipmi['type'] != '') {
            array_push($cmd, '-I', $ipmi['type'], '-c', 'sdr');
            $results = external_exec($cmd);
            d_echo($results);
            echo " done.\n";
        } else {
            echo " type not yet discovered.\n";
        }

        foreach (explode("\n", $results) as $row) {
            [$desc, $value, $type, $status] = explode(',', $row);
            $desc = trim($desc, ' ');
            $ipmi_unit_type = Config::get("ipmi_unit.$type");

            // SDR records can include hexadecimal values, identified by an h
            // suffix (like "93h" for 0x93). Convert them to decimal.
            if (preg_match('/^([0-9A-Fa-f]+)h$/', $value, $matches)) {
                $value = hexdec($matches[1]);
            }

            $ipmi_sensor[$desc][$ipmi_unit_type]['value'] = $value;
            $ipmi_sensor[$desc][$ipmi_unit_type]['unit'] = $type;
        }

        foreach ($ipmi_rows as $ipmisensors) {
            echo 'Updating IPMI sensor ' . $ipmisensors['sensor_descr'] . '... ';

            $sensor_value = $ipmi_sensor[$ipmisensors['sensor_descr']][$ipmisensors['sensor_class']]['value'];
            $unit = $ipmi_sensor[$ipmisensors['sensor_descr']][$ipmisensors['sensor_class']]['unit'];

            echo "$sensor_value $unit\n";

            $rrd_name = get_sensor_rrd_name($device, $ipmisensors);
            $rrd_def = RrdDefinition::make()->addDataset('sensor', 'GAUGE', -20000, 20000);

            $fields = [
                'sensor' => $sensor_value,
            ];

            $tags = [
                'sensor_class' => $ipmisensors['sensor_class'],
                'sensor_type' => $ipmisensors['sensor_type'],
                'sensor_descr' => $ipmisensors['sensor_descr'],
                'sensor_index' => $ipmisensors['sensor_index'],
                'rrd_name' => $rrd_name,
                'rrd_def' => $rrd_def,
            ];
            data_update($device, 'ipmi', $tags, $fields);

            // FIXME warnings in event & mail not done here yet!
            dbUpdate(
                ['sensor_current' => $sensor_value,
                    'lastupdate' => ['NOW()'], ],
                'sensors',
                'poller_type = ? AND sensor_class = ? AND sensor_id = ?',
                ['ipmi', $ipmisensors['sensor_class'], $ipmisensors['sensor_id']]
            );
        }

        unset($ipmi_sensor);
    }
}
