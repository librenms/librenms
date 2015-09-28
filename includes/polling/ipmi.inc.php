<?php

$ipmi_rows = dbFetchRows("SELECT * FROM sensors WHERE device_id = ? AND poller_type='ipmi'", array($device['device_id']));

if ($ipmi['host'] = get_dev_attrib($device, 'ipmi_hostname')) {
    $ipmi['user']     = get_dev_attrib($device, 'ipmi_username');
    $ipmi['password'] = get_dev_attrib($device, 'ipmi_password');
    $ipmi['type']     = get_dev_attrib($device, 'ipmi_type');
    echo 'Fetching IPMI sensor data...';

    if ($config['own_hostname'] != $device['hostname'] || $ipmi['host'] != 'localhost') {
        $remote = ' -H '.$ipmi['host'].' -U '.$ipmi['user'].' -P '.$ipmi['password'];
    }

    $results = external_exec($config['ipmitool'].' -I '.$ipmi['type'].' -c '.$remote.' sdr 2>/dev/null');
    echo " done.\n";

    foreach (explode("\n", $results) as $row) {
        list($desc,$value,$type,$status) = explode(',', $row);
        $ipmi_sensor[$desc][$config['ipmi_unit'][$type]]['value'] = $value;
        $ipmi_sensor[$desc][$config['ipmi_unit'][$type]]['unit']  = $type;
    }

    foreach ($ipmi_rows as $ipmisensors) {
        echo 'Updating IPMI sensor '.$ipmisensors['sensor_descr'].'... ';

        $sensor = $ipmi_sensor[$ipmisensors['sensor_descr']][$ipmisensors['sensor_class']]['value'];
        $unit   = $ipmi_sensor[$ipmisensors['sensor_descr']][$ipmisensors['sensor_class']]['unit'];

        $rrd_file = get_sensor_rrd($device, $ipmisensors);

        if (is_file($old_rrd_file)) {
            rename($old_rrd_file, $rrd_file);
        }

        if (!is_file($rrd_file)) {
            rrdtool_create(
                $rrd_file,
                '--step 300 
                DS:sensor:GAUGE:600:-20000:20000 '.$config['rrd_rra']
            );
        }

        echo $sensor." $unit\n";

        $fields = array(
            'sensor' => $sensor,
        );

        rrdtool_update($rrd_file, $fields);

        // FIXME warnings in event & mail not done here yet!
        dbUpdate(array('sensor_current' => $sensor), 'sensors', 'poller_type = ? AND sensor_class = ? AND sensor_id = ?', array('ipmi', $ipmisensors['sensor_class'], $ipmisensors['sensor_id']));
    }

    unset($ipmi_sensor);
}
