<?php

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\WirelessSensor;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Config;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\StringHelpers;

class Wireless implements Module
{
    use SyncsModels;

    // Add new types here translations/descriptions/units in lang/<lang>/wireless.php
    private array $types = [
        'ap-count',
        'clients',
        'quality',
        'capacity',
        'utilization',
        'rate',
        'ccq',
        'snr',
        'sinr',
        'rsrp',
        'rsrq',
        'ssr',
        'mse',
        'xpi',
        'rssi',
        'power',
        'noise-floor',
        'errors',
        'error-ratio',
        'error-rate',
        'frequency',
        'distance',
        'cell',
        'channel',
    ];

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['os', 'ports'];
    }

    /**
     * @inheritDoc
     */
    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $submodules = Config::get('discovery_submodules.wireless', $this->types);
        $types = array_intersect($this->types, $submodules);
        $existingSensors = $os->getDevice()->wirelessSensors()->get()->groupBy('sensor_class');

        ModuleModelObserver::observe(WirelessSensor::class);
        foreach ($types as $type) {
            $typeInterface = $this->getDiscoveryInterface($type);
            if (! interface_exists($typeInterface)) {
                Log::error("Discovery Interface doesn't exist! $typeInterface");
                continue;
            }

            $sensors = [];

            if ($os instanceof $typeInterface) {
                Log::info("$type: ");
                $function = $this->getDiscoveryMethod($type);
                $sensors = $os->$function();

                // TODO update interfaces and remove this check
                if (! is_array($sensors)) {
                    Log::error("%RERROR:%n $function did not return an array! Skipping discovery.", ['color' => true]);
                    $sensors = [];
                }
            }

            // convert legacy discovery data to Eloquent models
            $sensors = collect($sensors)->map(function (\LibreNMS\Device\WirelessSensor $legacy) {
                $model = $legacy->toModel();

                // legacy discovery auto-fetched sensors with null current values
                if ($model->sensor_current === null && ! empty($model->sensor_oids)) {
                    Log::debug("Data missing for $model->sensor_type $model->sensor_index, fetching");
                    $value = \SnmpQuery::numeric()->get($model->sensor_oids)->values();
                    $model->fillValue($value);
                }

                return $model;
            })->filter(fn (WirelessSensor $sensor) => is_numeric($sensor->sensor_current));

            // sync only models for this type
            $synced = $this->syncModels($os->getDevice(), 'wirelessSensors', $sensors, $existingSensors->get($type, new Collection));

            if ($synced->isNotEmpty()) {
                Log::info('');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        // fetch and group sensors
        $submodules = Config::get('poller_submodules.wireless', []);
        $sensors = $os->getDevice()->wirelessSensors()
            ->when($submodules, fn ($q) => $q->whereIn('sensor_class', $submodules))
            ->get()->keyBy('sensor_id');

        foreach ($sensors->groupBy('sensor_class') as $type => $type_sensors) {
            // check for custom polling
            $custom_polled_data = [];
            $typeInterface = $this->getPollingInterface($type);
            if (! interface_exists($typeInterface)) {
                Log::error("ERROR: Polling Interface doesn't exist! $typeInterface");
            }

            // fetch custom data
            if ($os instanceof $typeInterface) {
                Log::info("$type: ");
                $pollingMethod = $this->getPollingMethod($type);
                $custom_polled_data = $os->$pollingMethod($type_sensors->all());
                foreach ($custom_polled_data as $sensor_id => $data) {
                    $sensor = $sensors->get($sensor_id);
                    $sensor->sensor_current = $data;
                    $this->updateSensor($sensor, $os, $datastore);
                    $sensors->forget($sensor_id);  // remove from sensors array to prevent double polling
                }
            }
        }

        // fetch all standard sensors
        $standard_sensors = $sensors->pluck('sensor_oids')->flatten()->all();
        $fetched_data = \SnmpQuery::numeric()->get($standard_sensors)->values();

        // poll standard sensors
        foreach ($sensors->groupBy('sensor_class') as $type => $type_sensors) {
            Log::info("$type: ");
            foreach ($type_sensors as $sensor) {
                $values = array_intersect_key($fetched_data, array_flip($sensor->sensor_oids));
                $sensor->fillValue($values);
                $this->updateSensor($sensor, $os, $datastore);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->wirelessSensors()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->wirelessSensors()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'wireless_sensors' => $device->wirelessSensors()
                ->orderBy('sensor_class')->orderBy('sensor_type')->orderBy('sensor_index')
                ->get()->map->makeHidden(['device_id', 'sensor_id', 'access_point_id', 'lastupdate']),
        ];
    }

    protected function updateSensor(WirelessSensor $sensor, OS $os, DataStorageInterface $datastore): void
    {
        // populate sensor_prev and save to db
        $sensor->sensor_prev = $sensor->getOriginal('sensor_current');
        $sensor->save();

        // update rrd and database
        $rrd_name = [
            'wireless-sensor',
            $sensor->sensor_class,
            $sensor->sensor_type,
            $sensor->sensor_index,
        ];
        $rrd_def = RrdDefinition::make()->addDataset('sensor', $sensor->rrd_type);

        $fields = [
            'sensor' => $sensor->sensor_current,
        ];

        $tags = [
            'sensor_class' => $sensor->sensor_class,
            'sensor_type' => $sensor->sensor_type,
            'sensor_descr' => $sensor->sensor_descr,
            'sensor_index' => $sensor->sensor_index,
            'rrd_name' => $rrd_name,
            'rrd_def' => $rrd_def,
        ];
        $datastore->put($os->getDeviceArray(), 'wireless-sensor', $tags, $fields);

        Log::info("  $sensor->sensor_descr: $sensor->sensor_current " . __("wireless.$sensor->sensor_class.unit"));
    }

    protected function getDiscoveryInterface($type): string
    {
        return StringHelpers::toClass("wireless $type discovery", 'LibreNMS\\Interfaces\\Discovery\\Sensors\\');
    }

    protected function getDiscoveryMethod($type): string
    {
        return 'discover' . StringHelpers::toClass("wireless $type");
    }

    protected function getPollingInterface($type): string
    {
        return StringHelpers::toClass("wireless $type polling", 'LibreNMS\\Interfaces\\Polling\\Sensors\\');
    }

    protected function getPollingMethod($type): string
    {
        return 'poll' . StringHelpers::toClass("wireless $type");
    }

    public static function channelToFrequency(int $channel): int
    {
        return match ($channel) {
            1 => 2412,
            2 => 2417,
            3 => 2422,
            4 => 2427,
            5 => 2432,
            6 => 2437,
            7 => 2442,
            8 => 2447,
            9 => 2452,
            10 => 2457,
            11 => 2462,
            12 => 2467,
            13 => 2472,
            14 => 2484,
            34 => 5170,
            36 => 5180,
            38 => 5190,
            40 => 5200,
            42 => 5210,
            44 => 5220,
            46 => 5230,
            48 => 5240,
            52 => 5260,
            56 => 5280,
            60 => 5300,
            64 => 5320,
            100 => 5500,
            104 => 5520,
            108 => 5540,
            112 => 5560,
            116 => 5580,
            120 => 5600,
            124 => 5620,
            128 => 5640,
            132 => 5660,
            136 => 5680,
            140 => 5700,
            149 => 5745,
            153 => 5765,
            157 => 5785,
            161 => 5805,
            165 => 5825,
            default => 0,
        };
    }
}
