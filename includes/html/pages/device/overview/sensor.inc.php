<?php

use App\Facades\LibrenmsConfig;
use LibreNMS\Enum\Sensor;
use LibreNMS\Util\Clean;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Url;

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
$appOverviewOidPrefixes = DeviceCache::getPrimary()->applications
    ->filter(static function ($app): bool {
        return is_file('includes/html/pages/device/overview/apps/' . Clean::fileName($app->app_type) . '.inc.php');
    })
    ->map(static function ($app): string {
        return 'app:' . $app->app_type . ':';
    })
    ->all();

foreach ($sensor_overview_order as $sensor_class) {
    $sensors = DeviceCache::getPrimary()->sensors
        ->where('sensor_class', $sensor_class->value)
        ->where('group', '!=', 'transceiver')
        ->filter(static function ($sensor) use ($appOverviewOidPrefixes): bool {
            foreach ($appOverviewOidPrefixes as $prefix) {
                if (str_starts_with((string) $sensor->sensor_oid, $prefix)) {
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
