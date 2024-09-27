<?php

namespace App\Observers;

use App\Models\Eventlog;
use App\Models\Sensor;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Log;
use LibreNMS\Config;
use LibreNMS\Enum\Severity;

class SensorObserver
{
    private bool $runningInConsole;

    public function __construct(Application $app)
    {
        $this->runningInConsole = $app->runningInConsole();
    }

    public function saving(Sensor $sensor): void
    {
        if ($this->runningInConsole && ! $sensor->isDirty()) {
            echo '.';
        }
    }

    public function creating(Sensor $sensor): void
    {
        // fix inverted warn limits
        if ($sensor->sensor_limit_warn !== null && $sensor->sensor_limit_low_warn !== null && $sensor->sensor_limit_low_warn > $sensor->sensor_limit_warn) {
            Log::error('Fixing swapped sensor warn limits');

            // Fix high/low thresholds (i.e. on negative numbers)
            [$sensor->sensor_limit_warn, $sensor->sensor_limit_low_warn] = [$sensor->sensor_limit_low_warn, $sensor->sensor_limit_warn];
        }

        if (Config::get('sensors.guess_limits') && $sensor->sensor_current !== null) {
            $sensor->guessLimits($sensor->sensor_limit === null, $sensor->sensor_limit_low === null);
        }

        // Fix high/low thresholds (i.e. on negative numbers)
        if ($sensor->sensor_limit !== null && $sensor->sensor_limit_low > $sensor->sensor_limit) {
            Log::error('Fixing swapped sensor limits');

            [$sensor->sensor_limit, $sensor->sensor_limit_low] = [$sensor->sensor_limit_low, $sensor->sensor_limit];
        }
    }

    /**
     * Handle the service "created" event.
     *
     * @param  Sensor  $sensor
     * @return void
     */
    public function created(Sensor $sensor): void
    {
        EventLog::log('Sensor Added: ' . $sensor->sensor_class . ' ' . $sensor->sensor_type . ' ' . $sensor->sensor_index . ' ' . $sensor->sensor_descr, $sensor->device_id, 'sensor', Severity::Notice, $sensor->sensor_id);
        Log::info("$sensor->sensor_descr: Cur $sensor->sensor_current, Low: $sensor->sensor_limit_low, Low Warn: $sensor->sensor_limit_low_warn, Warn: $sensor->sensor_limit_warn, High: $sensor->sensor_limit");

        if ($this->runningInConsole) {
            echo '+';
        }
    }

    /**
     * Handle the Stp "updating" event.
     *
     * @param  \App\Models\Sensor  $sensor
     * @return void
     */
    public function updating(Sensor $sensor)
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

    public function updated(Sensor $sensor): void
    {
        // log limit changes
        if ($sensor->sensor_custom == 'No') {
            if ($sensor->isDirty('sensor_limit')) {
                EventLog::log('Sensor High Limit Updated: ' . $sensor->sensor_class . ' ' . $sensor->sensor_type . ' ' . $sensor->sensor_index . ' ' . $sensor->sensor_descr . ' (' . $sensor->sensor_limit . ')', $sensor->device_id, 'sensor', Severity::Notice, $sensor->sensor_id);

                if ($this->runningInConsole) {
                    echo 'H';
                }
            }

            if ($sensor->isDirty('sensor_limit_low')) {
                EventLog::log('Sensor Low Limit Updated: ' . $sensor->sensor_class . ' ' . $sensor->sensor_type . ' ' . $sensor->sensor_index . ' ' . $sensor->sensor_descr . ' (' . $sensor->sensor_limit_low . ')', $sensor->device_id, 'sensor', Severity::Notice, $sensor->sensor_id);

                if ($this->runningInConsole) {
                    echo 'L';
                }
            }

            if ($sensor->isDirty('sensor_limit_warn')) {
                EventLog::log('Sensor Warn High Limit Updated: ' . $sensor->sensor_class . ' ' . $sensor->sensor_type . ' ' . $sensor->sensor_index . ' ' . $sensor->sensor_descr . ' (' . $sensor->sensor_limit_warn . ')', $sensor->device_id, 'sensor', Severity::Notice, $sensor->sensor_id);

                if ($this->runningInConsole) {
                    echo 'WH';
                }
            }

            if ($sensor->isDirty('sensor_limit_low_warn')) {
                EventLog::log('Sensor Warn Low Limit Updated: ' . $sensor->sensor_class . ' ' . $sensor->sensor_type . ' ' . $sensor->sensor_index . ' ' . $sensor->sensor_descr . ' (' . $sensor->sensor_limit_low_warn . ')', $sensor->device_id, 'sensor', Severity::Notice, $sensor->sensor_id);

                if ($this->runningInConsole) {
                    echo 'WL';
                }
            }
        }

        if ($this->runningInConsole) {
            echo 'U';
        }

        // only post eventlog when relevant columns change
        if ($sensor->isDirty(['sensor_class', 'sensor_oid', 'sensor_index', 'sensor_type', 'sensor_descr', 'group', 'sensor_divisor', 'sensor_multiplier', 'entPhysicalIndex', 'entPhysicalIndex_measured', 'user_func'])) {
            EventLog::log('Sensor Updated: ' . $sensor->sensor_class . ' ' . $sensor->sensor_type . ' ' . $sensor->sensor_index . ' ' . $sensor->sensor_descr, $sensor->device_id, 'sensor', Severity::Notice, $sensor->sensor_id);
        }
    }

    public function deleted(): void
    {
        if ($this->runningInConsole) {
            echo '-';
        }
    }
}
