<?php

global $agent_sensors;

require_once 'includes/discovery/functions.inc.php';

if ($agent_data['haddtemp'] != '|') {
    $disks = explode('||', trim($agent_data['hddtemp'], '|'));

    if (count($disks)) {
        echo 'hddtemp: ';
        foreach ($disks as $disk) {
            list($blockdevice,$descr,$temperature,$unit) = explode('|', $disk, 4);
            $diskcount++;
            discover_sensor($valid['sensor'], 'temperature', $device, '', $diskcount, 'hddtemp', "$blockdevice: $descr", '1', '1', null, null, null, null, $temperature, 'agent');
        }

        echo "\n";
        $agent_sensors = dbFetchRows("SELECT * FROM `sensors` WHERE `device_id` = ? AND `sensor_class` = 'temperature' AND `poller_type` = 'agent' AND `sensor_deleted` = 0", array($device['device_id']));
    }
}//end if
