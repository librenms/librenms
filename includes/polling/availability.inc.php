<?php

use LibreNMS\Config;
use LibreNMS\RRD\RrdDefinition;

if (isset($device['uptime']) && ($device['uptime'] > 0 )) {
    $os->enableGraph('availability');

    $col = dbFetchColumn('SELECT duration FROM availability WHERE device_id = ?', array($device['device_id']));
    foreach (Config::get('graphing.availability') as $duration) {
        if (!in_array($duration, $col)) {
            $data = ['device_id' => $device['device_id'],
                     'duration' => $duration];
            dbInsert($data, 'availability');
        }
    }

    echo 'Availability: ' . PHP_EOL;

    foreach (dbFetchRows('SELECT * FROM availability WHERE device_id = ?', array($device['device_id'])) as $row) {
        //delete not more interested availabilities
        if (!in_array($row['duration'], Config::get('graphing.availability'))) {
            dbDelete('availability', 'availability_id=?', array($row['availability_id']));
            continue;
        }

        $avail = \LibreNMS\Device\Availability::availability($device, $row['duration']);
        $human_time = \LibreNMS\Util\Time::humanTime($row['duration']);

        $rrd_name = array('availability', $row['duration']);
        $rrd_def = RrdDefinition::make()
            ->addDataset('availability', 'GAUGE', 0);

         $fields = array(
             'availability' => $avail,
         );

         $tags = array('name' => $row['duration'], 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
         data_update($device, 'availability', $tags, $fields);

         dbUpdate(array('availability_perc' => $avail), 'availability', '`availability_id` = ?', array($row['availability_id']));

         echo $human_time . ' : ' . $avail . '%'. PHP_EOL;
    }
    unset($duration);
}
