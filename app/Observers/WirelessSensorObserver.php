<?php

namespace App\Observers;

use App\Models\WirelessSensor;

class WirelessSensorObserver
{
    /**
     * Handle the WirelessSensor "updating" event.
     *
     * @param  \App\Models\WirelessSensor  $sensor
     * @return void
     */
    public function updating(WirelessSensor $sensor): void
    {
        // prevent update of limits
        if ($sensor->sensor_custom == 'Yes') {
            // if custom is set to yes (future someone's problem to allow ui to update this with eloquent)
            $sensor->sensor_limit = $sensor->getOriginal('sensor_limit');
            $sensor->sensor_limit_warn = $sensor->getOriginal('sensor_limit_warn');
            $sensor->sensor_limit_low_warn = $sensor->getOriginal('sensor_limit_low_warn');
            $sensor->sensor_limit_low = $sensor->getOriginal('sensor_limit_low');
        } else {
            // only allow update if it wasn't previously set
            if ($sensor->getOriginal('sensor_limit') !== null) {
                $sensor->sensor_limit = $sensor->getOriginal('sensor_limit');
            }
            if ($sensor->getOriginal('sensor_limit_warn') !== null) {
                $sensor->sensor_limit_warn = $sensor->getOriginal('sensor_limit_warn');
            }
            if ($sensor->getOriginal('sensor_limit_low_warn') !== null) {
                $sensor->sensor_limit_low_warn = $sensor->getOriginal('sensor_limit_low_warn');
            }
            if ($sensor->getOriginal('sensor_limit_low') !== null) {
                $sensor->sensor_limit_low = $sensor->getOriginal('sensor_limit_low');
            }
        }
    }
}
