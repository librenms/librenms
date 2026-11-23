<?php

/**
 * Sensors.php
 *
 * Discover and poll health sensors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Discovery\Sensor as SensorCollector;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Sensor;
use App\Models\StateTranslation;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SensorType;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\SnmpConnectionException;
use LibreNMS\Interfaces\Data\WriteInterface;
use LibreNMS\Interfaces\Discovery\SensorsDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\SensorsPolling;
use LibreNMS\Interfaces\RemovesMethodData;
use LibreNMS\Interfaces\SupportsSubmodules;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Traits\RequiresPollingMethod;
use LibreNMS\Util\Number;
use LibreNMS\Util\ProgressList;
use LibreNMS\Util\UserFuncHelper;

/**
 * Sensors of the SNMP, IPMI and unix agent polling methods, each method has its own sensors (poller_type).
 * Submodules are sensor classes.
 */
class Sensors implements Module, RemovesMethodData, SupportsSubmodules
{
    use RequiresPollingMethod;

    /** @var list<SensorType>|null */
    private ?array $types = null;

    /**
     * @return string[]
     */
    public function dependencies(): array
    {
        return [];
    }

    public function pollingMethods(Device $device): array
    {
        return [PollingMethodType::Snmp, PollingMethodType::Ipmi, PollingMethodType::UnixAgent];
    }

    public function setSubmodules(?array $submodules): void
    {
        $this->types = $submodules === null ? null : array_values(array_filter(array_map(SensorType::tryFrom(...), $submodules)));
    }

    /**
     * Each available method replaces its sensors, the sensors of a method that is not available are kept.
     * IPMI and the unix agent report no sensors when their tool is not responding, their sensors are kept then too,
     * as are SNMP sensors when the device did not answer.
     */
    public function discover(OS $os): void
    {
        $device = $os->getDevice();
        $collector = new SensorCollector($device);
        $types = $this->types ?? SensorType::cases();
        $classes = array_map(fn (SensorType $type) => $type->value, $types);

        foreach ($this->pollingMethods($device) as $method) {
            $source = $os->dataSource($method, SensorsDiscovery::class);
            if ($source === null) {
                continue;
            }

            Log::info($method->label() . ':');
            try {
                $discovered = $source->discoverSensors();
            } catch (SnmpConnectionException $e) {
                Log::warning('Device did not answer, ' . $method->label() . ' sensors were not updated: ' . $e->getMessage());

                continue;
            }

            if ($discovered->isEmpty() && $method !== PollingMethodType::Snmp) {
                Log::info('No sensors reported, keeping the existing sensors');
                continue;
            }

            $pollerType = self::pollerType($method);
            foreach ($discovered as $sensor) {
                if (in_array($sensor->sensor_class, $classes)) {
                    $collector->discover($sensor->setAttribute('poller_type', $pollerType));
                }
            }

            foreach ($types as $type) {
                $progress = ProgressList::start($type->label());
                $collector->sync(sensor_class: $type->value, poller_type: $pollerType);
                $progress->end();
            }
        }
    }

    public function shouldPoll(OS $os): bool
    {
        return $this->hasAvailableMethod($os) && $this->methodSensors($os->getDevice())->exists();
    }

    /**
     * Sensors are polled by their method. IPMI and unix agent sensors that are not read are not recorded,
     * nor are the sensors of a method whose device did not answer.
     */
    public function poll(OS $os, WriteInterface $datastore): void
    {
        $sensors = $this->methodSensors($os->getDevice())->with('translations')->get();
        $values = [];
        $unread = [];

        foreach ($this->pollingMethods($os->getDevice()) as $method) {
            $methodSensors = $sensors->where('poller_type', self::pollerType($method));
            $source = $methodSensors->isEmpty() ? null : $os->dataSource($method, SensorsPolling::class);
            if ($source === null) {
                continue;
            }

            try {
                $values += $source->pollSensors($methodSensors);
            } catch (SnmpConnectionException $e) {
                Log::warning('Device did not answer, ' . $method->label() . ' sensors were not polled: ' . $e->getMessage());

                continue;
            }

            if ($method === PollingMethodType::Snmp) {
                // SNMP sensors that are not read are recorded as 0
                $unread += array_diff_key(array_flip($methodSensors->modelKeys()), $values);
            }
        }

        foreach ($sensors->groupBy('sensor_class') as $class => $classSensors) {
            $progress = ProgressList::start(SensorType::tryFrom($class)?->label() ?? $class);
            foreach ($classSensors as $sensor) {
                /** @var Sensor $sensor */
                if (array_key_exists($sensor->sensor_id, $values)) {
                    $this->recordValue($datastore, $sensor->setRelation('device', $os->getDevice()), $this->convertValue($sensor, $values[$sensor->sensor_id]));
                } elseif (isset($unread[$sensor->sensor_id])) {
                    $this->recordValue($datastore, $sensor->setRelation('device', $os->getDevice()), 0);
                }
            }
            $progress->end();
        }
    }

    public function dataExists(Device $device): bool
    {
        return $this->methodSensors($device)->exists();
    }

    public function cleanup(Device $device): int
    {
        return $this->methodSensors($device)->delete();
    }

    public function removeMethodData(Device $device, PollingMethodType $type): void
    {
        if (in_array($type, $this->pollingMethods($device), true)) {
            $device->sensors()->where('poller_type', self::pollerType($type))->delete();
        }
    }

    /**
     * Ipmi and unix agent sensors are included, their modules do not dump them.
     *
     * @return array{sensors?: list<array<string, mixed>>, state_indexes?: list<array<string, mixed>>}
     */
    public function dump(Device $device, string $type): ?array
    {
        $sensors = $device->sensors()->with('stateIndex')
            ->orderBy('sensor_class')->orderBy('sensor_type')->orderBy('sensor_index')->get()
            ->map(fn (Sensor $sensor) => array_diff_key($sensor->getAttributes(), array_flip(['device_id', 'sensor_id', 'lastupdate']))
                + ['state_name' => $sensor->stateIndex?->state_name]);

        $states = DB::table('state_indexes')
            ->join('state_translations', 'state_indexes.state_index_id', 'state_translations.state_index_id')
            ->whereIn('state_indexes.state_index_id', DB::table('sensors_to_state_indexes')
                ->join('sensors', 'sensors_to_state_indexes.sensor_id', 'sensors.sensor_id')
                ->where('sensors.device_id', $device->device_id)
                ->select('sensors_to_state_indexes.state_index_id'))
            ->orderBy('state_indexes.state_name')->orderBy('state_translations.state_value')
            ->get(['state_name', 'state_descr', 'state_value', 'state_generic_value']);

        return array_filter([
            'sensors' => $sensors->values()->all(),
            'state_indexes' => $states->map(fn ($state) => (array) $state)->values()->all(),
        ]);
    }

    /**
     * The sensors of the polling methods, of the selected classes
     *
     * @return HasMany<Sensor, Device>
     */
    private function methodSensors(Device $device): HasMany
    {
        return $device->sensors()->whereIn('poller_type', array_map(self::pollerType(...), $this->pollingMethods($device)))
            ->when($this->types !== null, fn ($query) => $query->whereIn('sensor_class', array_map(fn (SensorType $type) => $type->value, $this->types)));
    }

    /**
     * The poller_type of the sensors of a polling method
     */
    private static function pollerType(PollingMethodType $method): string
    {
        return match ($method) {
            PollingMethodType::UnixAgent => 'agent',
            default => $method->value,
        };
    }

    private function recordValue(WriteInterface $datastore, Sensor $sensor, int|float|string|null $value): void
    {
        $previous = $sensor->sensor_current;
        Log::info("$sensor->sensor_descr: $value " . SensorType::tryFrom($sensor->sensor_class)?->unit());

        $metric = $sensor->metricIdentity('sensor');
        $datastore->write($metric->measurement, ['sensor' => $value], $metric->tags, [
            'metric' => $metric,
            'rrd_def' => RrdDefinition::make()->addDataset('sensor', $sensor->rrd_type),
        ]);

        $this->logThresholds($sensor, $value, $previous);

        if ($value != $previous) {
            if ($sensor->sensor_class == 'state') {
                $this->logStateChange($sensor, $value, $previous);
            }

            $sensor->sensor_prev = $previous;
            $sensor->sensor_current = $value;
            $sensor->lastupdate = $sensor->freshTimestampString();
        }

        if ($sensor->isDirty()) {
            $sensor->save();
        }
    }

    /**
     * Convert a raw value with the state translations, divisor, multiplier and user_func of the sensor.
     */
    private function convertValue(Sensor $sensor, mixed $raw): int|float|string|null
    {
        if ($sensor->sensor_class == 'state' && ! is_numeric($raw)) {
            $state = $sensor->translations->first(fn (StateTranslation $state) => strcasecmp((string) $state->state_descr, (string) $raw) == 0);
            $raw = $state === null ? $raw : $state->state_value;
        }

        $value = Number::extract($raw);

        if ($value == -32768 || is_nan($value)) {
            Log::info('Invalid (-32768 or NaN)');
            $value = 0;
        }

        if ($sensor->sensor_divisor && $value !== 0) {
            $value /= $sensor->sensor_divisor;
        }

        if ($sensor->sensor_multiplier) {
            $value *= $sensor->sensor_multiplier;
        }

        if ($sensor->user_func) {
            return UserFuncHelper::apply($sensor->user_func, $value, is_scalar($raw) ? $raw : null, $sensor->getAttributes());
        }

        return $value;
    }

    private function logThresholds(Sensor $sensor, int|float|string|null $value, int|float|string|null $previous): void
    {
        if (! $sensor->sensor_alert) {
            return;
        }

        $class = $sensor->classDescr();
        $unit = SensorType::tryFrom($sensor->sensor_class)?->unit();

        if ($sensor->sensor_limit_low !== null && $previous > $sensor->sensor_limit_low && $value < $sensor->sensor_limit_low) {
            Log::info("Alerting for $sensor->sensor_descr");
            Eventlog::log("$class under threshold: $value $unit (< $sensor->sensor_limit_low $unit)", $sensor->device_id, $sensor->sensor_class, Severity::Warning, $sensor->sensor_id);
        } elseif ($sensor->sensor_limit !== null && $previous < $sensor->sensor_limit && $value > $sensor->sensor_limit) {
            Log::info("Alerting for $sensor->sensor_descr");
            Eventlog::log("$class above threshold: $value $unit (> $sensor->sensor_limit $unit)", $sensor->device_id, $sensor->sensor_class, Severity::Warning, $sensor->sensor_id);
        }
    }

    private function logStateChange(Sensor $sensor, int|float|string|null $value, int|float|string|null $previous): void
    {
        $describe = fn ($state) => ($sensor->translations->firstWhere('state_value', $state)->state_descr ?? '#unamed state#') . " ($state)";
        $class = $sensor->classDescr();

        Eventlog::log("$class sensor $sensor->sensor_descr has changed from {$describe($previous)} to {$describe($value)}", $sensor->device_id, $class, Severity::Notice, $sensor->sensor_id);
    }
}
