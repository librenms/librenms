<?php

use App\Facades\LibrenmsConfig;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Url;

$sensors = DeviceCache::getPrimary()->sensors->where('sensor_class', $sensor_class->value)->where('group', '!=', 'transceiver')->sortBy([
    ['group', 'asc'],
    ['sensor_descr', 'asc'],
]); // cache all sensors on device and exclude transceivers

if ($sensors->isNotEmpty()) {
    // prepare each sensor for display: normalise the description (ipmi sensors get a
    // friendly name, all are truncated) and build the link through to the graphs page
    $sensors->each(function ($sensor) use ($device, $sensor_class) {
        $descr = $sensor->poller_type == 'ipmi'
            ? ipmiSensorName($device['hardware'], $sensor->sensor_descr)
            : $sensor->sensor_descr;

        $sensor->sensor_descr = Rewrite::shortenIfName(substr((string) $descr, 0, 48));
        $sensor->graph_link = Url::generate([
            'page' => 'graphs',
            'id' => $sensor->sensor_id,
            'type' => 'sensor_' . $sensor_class->value,
            'from' => LibrenmsConfig::get('time.day'),
            'to' => LibrenmsConfig::get('time.now'),
        ]);
    });

    echo view('device.overview.sensor', [
        'sensor_class' => $sensor_class,
        'sensor_link' => route('device', ['device' => DeviceCache::getPrimary()->device_id, 'tab' => 'health', 'vars' => 'metric=' . $sensor_class->value]),
        'groupedSensors' => $sensors->groupBy('group'),
    ]);
}
