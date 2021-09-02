<?php

require_once 'includes/discovery/functions.inc.php';

if (isset($agent_data['hddtemp']) && $agent_data['hddtemp'] != '|') {
    $disks = explode('||', trim($agent_data['hddtemp'], '|'));
    echo 'hddtemp: ';

    $diskcount = 0;
    foreach ($disks as $disk) {
        [$blockdevice,$descr,$temperature,$unit] = explode('|', $disk, 4);
        $diskcount++;
        $temperature = trim(str_replace('C', '', $temperature));
        discover_sensor($valid['sensor'], 'temperature', $device, '', $diskcount, 'hddtemp', "$blockdevice: $descr", '1', '1', null, null, null, null, $temperature, 'agent');
        dbUpdate(['sensor_current' => $temperature], 'sensors', '`sensor_index` = ? AND `sensor_class` = ? AND `poller_type` = ? AND `device_id` = ?', [$diskcount, 'temperature', 'agent', $device['device_id']]);
        $tmp_agent_sensors = dbFetchRow("SELECT * FROM `sensors` WHERE `sensor_index` = ? AND `device_id` = ? AND `sensor_class` = 'temperature' AND `poller_type` = 'agent' AND `sensor_deleted` = 0 LIMIT 1", [$diskcount, $device['device_id']]);
        $tmp_agent_sensors['new_value'] = $temperature;
        $agent_sensors[] = $tmp_agent_sensors;
        unset($tmp_agent_sensors);
    }

    echo "\n";
}//end if
