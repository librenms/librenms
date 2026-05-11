<?php

use App\Facades\LibrenmsConfig;
use App\Models\Sensor;
use Illuminate\Support\Facades\Log;
use LibreNMS\Data\Source\Ipmitool;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Number;

/** @var array $device */
$ipmiSensors = DeviceCache::getPrimary()->sensors()->where('poller_type', 'ipmi')
    ->get()->groupBy('sensor_class')->map->keyBy('sensor_descr');
if ($ipmiSensors->isEmpty()) {
    return;
}

if ($ipmi = Ipmitool::init()) {
    Log::info('Fetching IPMI sensor data...');
    foreach ($ipmi->sdr() as $values) {
        [$descr, $value, $unit, $status, $detail] = $values;
        $descr = trim($descr, ' ');
        $ipmi_unit_type = LibrenmsConfig::get("ipmi_unit.$unit");
        $sensor = $ipmiSensors->get($ipmi_unit_type)?->get($descr);

        /** @var Sensor $sensor */
        if ($sensor) {
            // SDR records can include hexadecimal values, identified by an h like "93h"
            if (preg_match('/^([0-9A-Fa-f]+)h$/', $value, $matches)) {
                $value = hexdec($matches[1]);
            }
            $value = Number::cast($value);
            $sensor->sensor_current = $value;
            $sensor->save();

            Log::info("  $descr: $value $unit");

            $rrd_name = ['sensor', ...array_values($sensor->labels())];
            $rrd_def = RrdDefinition::make()->addDataset('sensor', 'GAUGE', -20000, 20000);

            app('Datastore')->put($device, 'ipmi', [
                'sensor_class' => $sensor->sensor_class,
                'sensor_type' => $sensor->sensor_type,
                'sensor_descr' => $sensor->sensor_descr,
                'sensor_index' => $sensor->sensor_index,
                'rrd_name' => $rrd_name,
                'rrd_def' => $rrd_def,
            ], [
                'sensor' => $value,
            ]);
        }
    }
}

unset($ipmiSensors, $sensor);
