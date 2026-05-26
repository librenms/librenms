<?php

use App\Facades\LibrenmsConfig;
use LibreNMS\Enum\Sensor;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Url;
use Symfony\Component\Yaml\Yaml;

// Sensor overview panels, rendered in this order. Each empty class is skipped below.
$sensor_overview_order = [
    Sensor::Charge,
    Sensor::Temperature,
    Sensor::Humidity,
    Sensor::Fanspeed,
    Sensor::Dbm,
    Sensor::Voltage,
    Sensor::Current,
    Sensor::Runtime,
    Sensor::Power,
    Sensor::PowerConsumed,
    Sensor::PowerFactor,
    Sensor::Frequency,
    Sensor::Load,
    Sensor::State,
    Sensor::Count,
    Sensor::Percent,
    Sensor::Signal,
    Sensor::TvSignal,
    Sensor::Bitrate,
    Sensor::Airflow,
    Sensor::Snr,
    Sensor::Pressure,
    Sensor::Cooling,
    Sensor::Delay,
    Sensor::QualityFactor,
    Sensor::ChromaticDispersion,
    Sensor::Ber,
    Sensor::Eer,
    Sensor::Waterflow,
    Sensor::Loss,
    Sensor::SignalLoss,
];

// App-specific overview panels render their own sensor data, so suppress
// matching app-owned sensors from the generic overview blocks.
$agentDefs = Yaml::parseFile(base_path('resources/definitions/agent/unix.yaml')) ?? [];
$appOverviewGroupPrefixes = [];
foreach ($agentDefs as $def) {
    foreach ((array) ($def['sensor_group_prefix'] ?? []) as $prefix) {
        $appOverviewGroupPrefixes[] = (string) $prefix;
    }
}

foreach ($sensor_overview_order as $sensor_class) {
    $sensors = DeviceCache::getPrimary()->sensors
        ->where('sensor_class', $sensor_class->value)
        ->where('group', '!=', 'transceiver')
        ->filter(static function ($sensor) use ($appOverviewGroupPrefixes): bool {
            $group = (string) $sensor->group;
            foreach ($appOverviewGroupPrefixes as $prefix) {
                if (str_starts_with($group, $prefix)) {
                    return false;
                }
            }

            return true;
        })
        ->sortBy([
            ['group', 'asc'],
            ['sensor_descr', 'asc'],
        ]); // cache all sensors on device and exclude transceivers

    if ($sensors->isNotEmpty()) {
        // prepare each sensor for display: normalise the description (ipmi sensors get a
        // friendly name, all are truncated) and build the link through to the graphs page
        $sensors->each(function ($sensor) use ($device, $sensor_class): void {
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
}
