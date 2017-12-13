<?php

use LibreNMS\RRD\RrdDefinition;

$ipmi_rows = dbFetchRows("SELECT * FROM sensors WHERE device_id = ? AND poller_type='ipmi'", array($device['device_id']));

if (is_array($ipmi_rows)) {
    d_echo($ipmi_rows);

    if ($ipmi['host'] = $attribs['ipmi_hostname']) {
        $ipmi['user'] = $attribs['ipmi_username'];
        $ipmi['password'] = $attribs['ipmi_password'];
        $ipmi['type'] = $attribs['ipmi_type'];

        echo 'Fetching IPMI sensor data...';

        if ($config['own_hostname'] != $device['hostname'] || $ipmi['host'] != 'localhost') {
            $remote = " -H " . $ipmi['host'] . " -U '" . $ipmi['user'] . "' -P '" . $ipmi['password'] . "' -L USER";
        }

        // Check to see if we know which IPMI interface to use
        // so we dont use wrong arguments for ipmitool
        if ($ipmi['type'] != '') {
            $results = external_exec($config['ipmitool'] . ' -I ' . $ipmi['type'] . ' -c ' . $remote . ' sdr 2>/dev/null');
            d_echo($results);
            echo " done.\n";
        } else {
            echo " type not yet discovered.\n";
        }

        foreach (explode("\n", $results) as $row) {
            list($desc, $value, $type, $status) = explode(',', $row);
            $desc = trim($desc, ' ');
            $ipmi_sensor[$desc][$config['ipmi_unit'][$type]]['value'] = $value;
            $ipmi_sensor[$desc][$config['ipmi_unit'][$type]]['unit'] = $type;
        }

        foreach ($ipmi_rows as $ipmisensors) {
            echo 'Updating IPMI sensor ' . $ipmisensors['sensor_descr'] . '... ';

            $sensor_value = $ipmi_sensor[$ipmisensors['sensor_descr']][$ipmisensors['sensor_class']]['value'];
            $unit = $ipmi_sensor[$ipmisensors['sensor_descr']][$ipmisensors['sensor_class']]['unit'];

            echo "$sensor_value $unit\n";

            $rrd_name = get_sensor_rrd_name($device, $ipmisensors);
            $rrd_def = RrdDefinition::make()->addDataset('sensor', 'GAUGE', -20000, 20000);

            $fields = array(
                'sensor' => $sensor_value,
            );

            $tags = array(
                'sensor_class' => $ipmisensors['sensor_class'],
                'sensor_type' => $ipmisensors['sensor_type'],
                'sensor_descr' => $ipmisensors['sensor_descr'],
                'sensor_index' => $ipmisensors['sensor_index'],
                'rrd_name' => $rrd_name,
                'rrd_def' => $rrd_def
            );
            data_update($device, 'ipmi', $tags, $fields);

            // FIXME warnings in event & mail not done here yet!
            dbUpdate(
                array('sensor_current' => $sensor_value,
                    'lastupdate' => array('NOW()')),
                'sensors',
                'poller_type = ? AND sensor_class = ? AND sensor_id = ?',
                array('ipmi', $ipmisensors['sensor_class'], $ipmisensors['sensor_id'])
            );
        }

        unset($ipmi_sensor);
    }
}
