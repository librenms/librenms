<?php

require_once 'includes/discovery/functions.inc.php';

$agent_data['hddtemp'] = '|/dev/sda|WDC WD30EFRX-68EUZN0|31|C||/dev/sdb|WDC WD30EFRX-68EUZN0|31|C||/dev/sdc|ST3160815AS|35|C||/dev/sdd|WDC WD30EFRX-68EUZN0|32|C||/dev/sde|WDC WD30EFRX-68EUZN0|32|C||/dev/sdf|WDC WD30EFRX-68EUZN0|37|C||/dev/sdg|WDC WD30EFRX-68EUZN0|32|C||/dev/sdh|WDC WD30EFRX-68EUZN0|34|C|';

if ($agent_data['hddtemp'] != '|') {
    $disks = explode('||', trim($agent_data['hddtemp'], '|'));

    if (count($disks)) {
        echo 'hddtemp: ';
        foreach ($disks as $disk) {
            list($blockdevice,$descr,$temperature,$unit) = explode('|', $disk, 4);
            $diskcount++;
            discover_sensor($valid['sensor'], 'temperature', $device, '', $diskcount, 'hddtemp', "$blockdevice: $descr", '1', '1', null, null, null, null, $temperature, 'agent');
            dbUpdate(array('sensor_current' => $temperature), 'sensors', '`sensor_index` = ?, `sensor_class` = ?, `poller_type` = ?, `device_id` = ?', array($diskcount, 'temperature', 'agent', $device['device_id']));
            $tmp_agent_sensors = dbFetchRow("SELECT * FROM `sensors` WHERE `sensor_index` = ? AND `device_id` = ? AND `sensor_class` = 'temperature' AND `poller_type` = 'agent' AND `sensor_deleted` = 0 LIMIT 1", array($diskcount, $device['device_id']));
            $tmp_agent_sensors['new_value'] = $temperature;
            $agent_sensors[] = $tmp_agent_sensors;
            unset($tmp_agent_sensors);
        }

        echo "\n";
    }
}//end if
