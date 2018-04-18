<?php
/**
 * WirelessSensor.php
 *
 * -Description-
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\DiscoveryItem;
use LibreNMS\Interfaces\Discovery\DiscoveryModule;
use LibreNMS\Interfaces\Polling\PollerModule;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\DiscoveryModelObserver;

class WirelessSensor extends BaseModel implements DiscoveryModule, DiscoveryItem, PollerModule
{
    protected $primaryKey = 'wireless_sensor_id';
    protected $fillable = ['type', 'device_id', 'oids', 'subtype', 'index', 'description', 'value', 'multiplier', 'divisor', 'aggregator', 'access_point_id', 'alert_high', 'alert_low', 'warn_high', 'warn_low'];
    protected $casts = ['oids' => 'array'];

    protected static $rrd_name = 'wireless-sensor';
    private $valid = true;

    public static function discover(
        $type,
        $device_id,
        $oids,
        $subtype,
        $index,
        $description,
        $value = null,
        $multiplier = 1,
        $divisor = 1,
        $aggregator = 'sum',
        $access_point_id = null,
        $alert_high = null,
        $warn_high = null,
        $alert_low = null,
        $warn_low = null
    )
    {
        // ensure leading dots
        $oids = array_map(function($oid) {
            return '.' . ltrim($oid, '.');
        }, (array)$oids);

        // capture all input variables
        $sensor_array = get_defined_vars();


        $sensor = WirelessSensor::where('device_id', $device_id)
            ->firstOrNew(compact(['type', 'subtype', 'index']), $sensor_array);

        // if existing, update selected data
        if ($sensor->wireless_sensor_id) {
            $ignored = ['value'];
            if ($sensor->custom_thresholds) {
                $ignored[] = 'alert_high';
                $ignored[] = 'alert_low';
                $ignored[] = 'warn_high';
                $ignored[] = 'warn_low';
            }

            $sensor->fill(array_diff_key($sensor_array, array_flip($ignored)));
        }

        if (is_null($value)) {
            $sensors = collect([$sensor]);

            $prefetch = self::fetchSnmpData(Device::find($device_id), $sensors);
            $data = static::processSensorData($sensors, $prefetch);

            $sensor->value = $data->first();
        }

        $sensor->valid = is_numeric($sensor->value);

        return $sensor;
    }

    // ---- Helper Functions ----

    public function classDescr()
    {
        return collect(collect(\LibreNMS\Device\Wireless::getTypes())
            ->get($this->sensor_class, []))
            ->get('short', ucwords(str_replace('_', ' ', $this->sensor_class)));
    }

    public function icon()
    {
        return collect(collect(\LibreNMS\Device\Wireless::getTypes())
            ->get($this->sensor_class, []))
            ->get('icon', 'signal');
    }

    // ---- Query Scopes ----

    public function scopeHasAccess($query, User $user)
    {
        return $this->hasDeviceAccess($query, $user);
    }

    // ---- Define Relationships ----

    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id');
    }

    public static function runDiscovery(OS $os)
    {
        // check yaml first
//        $processors = self::processYaml($os);

        // output update status
        WirelessSensor::observe(new DiscoveryModelObserver());

        foreach (self::getTypes() as $type => $descr) {
            echo "$type: ";

            // save valid sensors, and collect ids
            $valid_ids = static::discoverType($os, $type)
                ->filter->isValid()
                ->each->save()
                ->pluck(['wireless_sensor_id']);

            // remove invalid sensors (mass delete will not trigger Eloquent deleted event)
            $deleted = WirelessSensor::where('device_id', $os->getDeviceId())
                ->where('type', $type)
                ->whereNotIn('wireless_sensor_id', $valid_ids)->delete();
            echo str_repeat('-', $deleted);

            echo PHP_EOL;
        }
    }

    protected static function discoverType(OS $os, $type)
    {
        $typeInterface = static::getDiscoveryInterface($type);
        if (!interface_exists($typeInterface)) {
            echo "ERROR: Discovery Interface doesn't exist! $typeInterface\n";
        }

        $have_discovery = $os instanceof $typeInterface;
        if ($have_discovery) {
            $function = static::getDiscoveryMethod($type);
            $sensors = $os->$function();

            if (is_array($sensors)) {
                return collect($sensors);
            } else {
                c_echo("%RERROR:%n $function did not return an array! Skipping discovery.");
            }
        }

        return collect();  // delete non existent sensors
    }


    /**
     * Return a list of valid types with metadata about each type
     * $class => array(
     *  'short' - short text for this class
     *  'long'  - long text for this class
     *  'unit'  - units used by this class 'dBm' for example
     *  'icon'  - font awesome icon used by this class
     * )
     * @param bool $valid filter this list by valid types in the database
     * @param int $device_id when filtering, only return types valid for this device_id
     * @return Collection
     */
    public static function getTypes($valid = false, $device_id = null)
    {
        // Add new types here
        // FIXME I'm really bad with icons, someone please help!
        static $types = [
            'ap-count' => [
                'short' => 'APs',
                'long' => 'AP Count',
                'unit' => '',
                'icon' => 'wifi',
            ],
            'clients' => [
                'short' => 'Clients',
                'long' => 'Client Count',
                'unit' => '',
                'icon' => 'tablet',
            ],
            'quality' => [
                'short' => 'Quality',
                'long' => 'Quality',
                'unit' => '%',
                'icon' => 'feed',
            ],
            'capacity' => [
                'short' => 'Capacity',
                'long' => 'Capacity',
                'unit' => '%',
                'icon' => 'feed',
            ],
            'utilization' => [
                'short' => 'Utilization',
                'long' => 'utilization',
                'unit' => '%',
                'icon' => 'percent',
            ],
            'rate' => [
                'short' => 'Rate',
                'long' => 'TX/RX Rate',
                'unit' => 'bps',
                'icon' => 'tachometer',
            ],
            'ccq' => [
                'short' => 'CCQ',
                'long' => 'Client Connection Quality',
                'unit' => '%',
                'icon' => 'wifi',
            ],
            'snr' => [
                'short' => 'SNR',
                'long' => 'Signal-to-Noise Ratio',
                'unit' => 'dB',
                'icon' => 'signal',
            ],
            'ssr' => [
                'short' => 'SSR',
                'long' => 'Signal Strength Ratio',
                'unit' => 'dB',
                'icon' => 'signal',
            ],
            'mse' => [
                'short' => 'MSE',
                'long' => 'Mean Square Error',
                'unit' => 'dB',
                'icon' => 'signal',
            ],
            'rssi' => [
                'short' => 'RSSI',
                'long' => 'Received Signal Strength Indicator',
                'unit' => 'dBm',
                'icon' => 'signal',
            ],
            'power' => [
                'short' => 'Power/Signal',
                'long' => 'TX/RX Power or Signal',
                'unit' => 'dBm',
                'icon' => 'bolt',
            ],
            'noise-floor' => [
                'short' => 'Noise Floor',
                'long' => 'Noise Floor',
                'unit' => 'dBm/Hz',
                'icon' => 'signal',
            ],
            'errors' => [
                'short' => 'Errors',
                'long' => 'Errors',
                'unit' => '',
                'icon' => 'exclamation-triangle',
                'type' => 'counter',
            ],
            'error-ratio' => [
                'short' => 'Error Ratio',
                'long' => 'Bit/Packet Error Ratio',
                'unit' => '%',
                'icon' => 'exclamation-triangle',
            ],
            'error-rate' => [
                'short' => 'BER',
                'long' => 'Bit Error Rate',
                'unit' => 'bps',
                'icon' => 'exclamation-triangle',
            ],
            'frequency' => [
                'short' => 'Frequency',
                'long' => 'Frequency',
                'unit' => 'MHz',
                'icon' => 'line-chart',
            ],
            'distance' => [
                'short' => 'Distance',
                'long' => 'Distance',
                'unit' => 'km',
                'icon' => 'space-shuttle',
            ],
        ];

        if ($valid) {
            $query = WirelessSensor::select('sensor_class');

            if (isset($device_id)) {
                $query->where('device_id', $device_id);
            }

            return collect($types)
                ->intersectKey($query->groupBy('sensor_class')
                    ->pluck('sensor_class')->flip());
        }

        return collect($types);
    }

    protected static function getDiscoveryInterface($type)
    {
        return str_to_class($type, 'LibreNMS\\Interfaces\\Discovery\\Sensors\\Wireless') . 'Discovery';
    }

    protected static function getDiscoveryMethod($type)
    {
        return 'discoverWireless' . str_to_class($type);
    }

    protected static function getPollingInterface($type)
    {
        return str_to_class($type, 'LibreNMS\\Interfaces\\Polling\\Sensors\\Wireless') . 'Polling';
    }

    protected static function getPollingMethod($type)
    {
        return 'pollWireless' . str_to_class($type);
    }

    /**
     * Does this item represent an actual item or did it fail validation
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Generate an instance of this class from yaml data.
     * The data is processed and any snmp data is filled in
     *
     * @param OS $os
     * @param int $index the index of the current entry
     * @param array $data
     * @return static
     */
    public static function fromYaml(OS $os, $index, array $data)
    {
        // TODO: Implement fromYaml() method.
    }

    public static function poll(OS $os)
    {

        // fetch and group sensors, decode oids
        $sensors = WirelessSensor::where('device_id', $os->getDeviceId())->get()->groupBy('type');

        foreach ($sensors as $type => $type_sensors) {
            // check for custom polling
            $typeInterface = static::getPollingInterface($type);
            if (!interface_exists($typeInterface)) {
                echo "ERROR: Polling Interface doesn't exist! $typeInterface\n";
            }

            // fetch custom data
            if ($os instanceof $typeInterface) {
                static::pollSensorType($os, $type, $type_sensors, collect());
                $sensors->forget($type); // remove from sensors array to prevent double polling
            }
        }

        // pre-fetch all standard sensors
        $standard_sensors = $sensors->flatten();
        $pre_fetch = self::fetchSnmpData(Device::find($os->getDeviceId()), $standard_sensors);

        // poll standard sensors
        foreach ($sensors as $type => $type_sensors) {
            static::pollSensorType($os, $type, $type_sensors, $pre_fetch);
        }
    }

    /**
     * Poll all sensors of a specific class
     *
     * @param OS $os
     * @param string $type
     * @param Collection $sensors
     * @param Collection|array $prefetch
     */
    protected static function pollSensorType($os, $type, $sensors, $prefetch = [])
    {
        echo "$type:\n";
        $prefetch = is_array($prefetch) ? collect($prefetch) : $prefetch;

        // process data or run custom polling
        $typeInterface = static::getPollingInterface($type);
        if ($os instanceof $typeInterface) {
            d_echo("Using OS polling for $type\n");
            $function = static::getPollingMethod($type);
            $data = $os->$function($sensors);
        } else {
            $data = static::processSensorData($sensors, $prefetch);
        }

        d_echo($data);

        self::recordSensorData($os, $sensors, $data);
    }

    /**
     * Fetch snmp data from the device
     * Return an array keyed by oid
     *
     * @param Device $device
     * @param Collection $sensors
     * @return Collection
     */
    private static function fetchSnmpData($device, $sensors)
    {
        $device_array = $device->toArray();

        return self::getOidsFromSensors($sensors, get_device_oid_limit($device_array))
            // fetch data in chunks
            ->reduce(function ($carry, $oid_chunk) use ($device_array) {
                $multi_data = snmp_get_multi_oid($device_array, $oid_chunk->toArray(), '-OUQnt');
                return $carry->merge(collect($multi_data));
            }, collect())
            // deal with string values that may be surrounded by quotes
            ->filter()
            ->map(function ($oid) {
                return trim($oid, '"') + 0;
            });
    }

    /**
     * Get a list of unique oids from an array of sensors and break it into chunks.
     *
     * @param Collection $sensors
     * @param int $chunk How many oids per chunk.  Default 10.
     * @return Collection OIDs
     */
    private static function getOidsFromSensors($sensors, $chunk = 10)
    {
        // collect all oids and chunk them
        return $sensors->pluck('oids')->flatten()->unique()->chunk($chunk);
    }

    /**
     * Process the snmp data for the specified sensors
     * Returns an array sensor_id => value
     *
     * @param Collection $sensors
     * @param Collection $prefetch
     * @return Collection
     */
    protected static function processSensorData($sensors, $prefetch)
    {
        return $sensors->reduce(function ($all_data, $sensor) use ($prefetch) {
            // pull out the data for this sensor
            $data = $prefetch->intersectKey(array_flip($sensor->oids));

            // if no data set null and continue to the next sensor
            if ($data->count() == 0) {
                return $all_data->put($sensor->wireless_sensor_id, null);
            }

            // distill data down to a single value
            if ($data->count() > 1) {
                // aggregate data
                if ($sensor['sensor_aggregator'] == 'avg') {
                    $sensor_value = $data->average();
                } else {
                    // sum
                    $sensor_value = $data->sum();
                }
            } else {
                $sensor_value = $data->first();
            }

            // apply multiplier and divisor
            $sensor_value = $sensor_value * $sensor->multiplier / $sensor->divisor;

            return $all_data->put($sensor->wireless_sensor_id, $sensor_value);
        }, collect());
    }

    /**
     * Record sensor data in the database and data stores
     *
     * @param $os
     * @param Collection $sensors
     * @param Collection $data
     */
    protected static function recordSensorData(OS $os, $sensors, $data)
    {
        $types = static::getTypes();

        /** @var WirelessSensor $sensor */
        foreach ($sensors as $sensor) {
            $sensor_value = $data[$sensor->wireless_sensor_id];

            $type = $types->get($sensor->type);
            echo "  $sensor->description: $sensor_value {$type['unit']}\n";

            // update rrd and database
            $rrd_name = [
                static::$rrd_name,
                $sensor->type,
                $sensor->subtype,
                $sensor->index
            ];
            $rrd_type = isset($type['type']) ? strtoupper($type['type']) : 'GAUGE';
            $rrd_def = RrdDefinition::make()->addDataset('sensor', $rrd_type);

            $fields = [
                'sensor' => isset($sensor_value) ? $sensor_value : 'U',
            ];

            $tags = [
                'type' => $sensor->type,
                'subtype' => $sensor->subtype,
                'index' => $sensor->index,
                'description' => $sensor->description,
                'rrd_name' => $rrd_name,
                'rrd_def' => $rrd_def
            ];
            data_update($os->getDevice(), static::$rrd_name, $tags, $fields);

            if ($sensor_value != $sensor->value) {
                $sensor->fill([
                    'previous_value' => $sensor->value,
                    'value' => $sensor_value,
                    //                'updated_at' => Carbon::now(),
                ]);
                $sensor->save();
            }
        }
    }
}
