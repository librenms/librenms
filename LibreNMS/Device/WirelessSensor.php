<?php
/**
 * WirelessSensor.php
 *
 * Wireless Sensors
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Device;

use LibreNMS\Interfaces\Discovery\DiscoveryItem;
use LibreNMS\Interfaces\Discovery\DiscoveryModule;
use LibreNMS\Interfaces\Polling\PollerModule;
use LibreNMS\Model;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class WirelessSensor extends Model implements DiscoveryModule, PollerModule, DiscoveryItem
{
    protected static $table = 'wireless_sensors';
    protected static $primaryKey = 'sensor_id';
    protected static $name = 'Wireless Sensor';
    protected static $data_name = 'wireless-sensor';

    private $valid = true;

    public $sensor_id;
    public $type;
    public $device_id;
    public $oids;
    public $subtype;
    public $index;
    public $description;
    public $current;
    public $multiplier;
    public $divisor;
    public $aggregator;
    public $high_limit;
    public $low_limit;
    public $high_warn;
    public $low_warn;
    public $entPhysicalIndex;
    public $entPhysicalMeasured;
    public $access_point_ip;

    /**
     * Sensor constructor. Create a new sensor to be discovered.
     *
     * @param string $type Class of this sensor, must be a supported class
     * @param int $device_id the device_id of the device that owns this sensor
     * @param array|string $oids an array or single oid that contains the data for this sensor
     * @param string $subtype the type of sensor an additional identifier to separate out sensors of the same class, generally this is the os name
     * @param int|string $index the index of this sensor, must be stable, generally the index of the oid
     * @param string $description A user visible description of this sensor, may be truncated in some places (like graphs)
     * @param int|float $current The current value of this sensor, will seed the db and may be used to guess limits
     * @param int $multiplier a number to multiply the value(s) by
     * @param int $divisor a number to divide the value(s) by
     * @param string $aggregator an operation to combine multiple numbers. Supported: sum, avg
     * @param int $access_point_id The id of the AP in the access_points sensor this belongs to (generally used for controllers)
     * @param int|float $high_limit Alerting: Maximum value
     * @param int|float $low_limit Alerting: Minimum value
     * @param int|float $high_warn Alerting: High warning value
     * @param int|float $low_warn Alerting: Low warning value
     * @param int|float $entPhysicalIndex The entPhysicalIndex this sensor is associated, often a port
     * @param int|float $entPhysicalMeasured the table to look for the entPhysicalIndex, for example 'ports' (maybe unused)
     * @return WirelessSensor
     */
    public static function discover(
        $type,
        $device_id,
        $oids,
        $subtype,
        $index,
        $description,
        $current = null,
        $multiplier = 1,
        $divisor = 1,
        $aggregator = 'sum',
        $access_point_id = null,
        $high_limit = null,
        $low_limit = null,
        $high_warn = null,
        $low_warn = null
    ) {
        $sensor = new static();

        $sensor->type = $type;
        $sensor->device_id = $device_id;
        $sensor->oids = (array)$oids;
        $sensor->subtype = $subtype;
        $sensor->index = $index;
        $sensor->description = $description;
        $sensor->current = $current;
        $sensor->multiplier = $multiplier;
        $sensor->divisor = $divisor;
        $sensor->aggregator = $aggregator;
        $sensor->access_point_ip = $access_point_id;
        $sensor->high_limit = $high_limit;
        $sensor->low_limit = $low_limit;
        $sensor->high_warn = $high_warn;
        $sensor->low_warn = $low_warn;

        // ensure leading dots
        array_walk($sensor->oids, function (&$oid) {
            $oid = '.' . ltrim($oid, '.');
        });


        $tmp_sensor = $sensor->toArray();
        // validity not checked yet
        if (is_null($sensor->current)) {
            $tmp_sensor['sensor_oids'] = $sensor->oids;
            $sensors = [$tmp_sensor];

            $prefetch = self::fetchSnmpData(device_by_id_cache($device_id), $sensors);
            $data = static::processSensorData($sensors, $prefetch);

            $sensor->current = current($data);
            $sensor->valid = is_numeric($sensor->current);
        }

        d_echo('Discovered ' . get_called_class() . ' ' . print_r($sensor, true));

        return $sensor;
    }

    public function toArray($exclude = [])
    {
        $array = [
            'sensor_class' => $this->type,
            'device_id' => $this->device_id,
            'sensor_oids' => json_encode($this->oids),
            'sensor_index' => $this->index,
            'sensor_type' => $this->subtype,
            'sensor_descr' => $this->description,
            'sensor_current' => (float)$this->current,
            'sensor_divisor' => (int)$this->divisor,
            'sensor_multiplier' => (int)$this->multiplier,
            'sensor_aggregator' => $this->aggregator,
            'sensor_limit' => (float)$this->high_limit,
            'sensor_limit_warn' => (float)$this->high_warn,
            'sensor_limit_low' => (float)$this->low_limit,
            'sensor_limit_low_warn' => (float)$this->low_warn,
            'access_point_id' => (int)$this->access_point_ip,
            'entPhysicalIndex' => $this->entPhysicalIndex,
            'entPhysicalIndex_measured' => $this->entPhysicalMeasured,
        ];

        return array_diff_key($array, array_flip($exclude));
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
     * @return array
     */
    public static function getTypes($valid = false, $device_id = null)
    {
        // Add new types here
        // FIXME I'm really bad with icons, someone please help!
        static $types = array(
            'ap-count' => array(
                'short' => 'APs',
                'long' => 'AP Count',
                'unit' => '',
                'icon' => 'wifi',
            ),
            'clients' => array(
                'short' => 'Clients',
                'long' => 'Client Count',
                'unit' => '',
                'icon' => 'tablet',
            ),
            'quality' => array(
                'short' => 'Quality',
                'long' => 'Quality',
                'unit' => '%',
                'icon' => 'feed',
            ),
            'capacity' => array(
                'short' => 'Capacity',
                'long' => 'Capacity',
                'unit' => '%',
                'icon' => 'feed',
            ),
            'utilization' => array(
                'short' => 'Utilization',
                'long' => 'utilization',
                'unit' => '%',
                'icon' => 'percent',
            ),
            'rate' => array(
                'short' => 'Rate',
                'long' => 'TX/RX Rate',
                'unit' => 'bps',
                'icon' => 'tachometer',
            ),
            'ccq' => array(
                'short' => 'CCQ',
                'long' => 'Client Connection Quality',
                'unit' => '%',
                'icon' => 'wifi',
            ),
            'snr' => array(
                'short' => 'SNR',
                'long' => 'Signal-to-Noise Ratio',
                'unit' => 'dB',
                'icon' => 'signal',
            ),
            'ssr' => array(
                'short' => 'SSR',
                'long' => 'Signal Strength Ratio',
                'unit' => 'dB',
                'icon' => 'signal',
            ),
            'mse' => array(
                'short' => 'MSE',
                'long' => 'Mean Square Error',
                'unit' => 'dB',
                'icon' => 'signal',
            ),
            'rssi' => array(
                'short' => 'RSSI',
                'long' => 'Received Signal Strength Indicator',
                'unit' => 'dBm',
                'icon' => 'signal',
            ),
            'power' => array(
                'short' => 'Power/Signal',
                'long' => 'TX/RX Power or Signal',
                'unit' => 'dBm',
                'icon' => 'bolt',
            ),
            'noise-floor' => array(
                'short' => 'Noise Floor',
                'long' => 'Noise Floor',
                'unit' => 'dBm/Hz',
                'icon' => 'signal',
            ),
            'errors' => array(
                'short' => 'Errors',
                'long' => 'Errors',
                'unit' => '',
                'icon' => 'exclamation-triangle',
                'type' => 'counter',
            ),
            'error-ratio' => array(
                'short' => 'Error Ratio',
                'long' => 'Bit/Packet Error Ratio',
                'unit' => '%',
                'icon' => 'exclamation-triangle',
            ),
            'error-rate' => array(
                'short' => 'BER',
                'long' => 'Bit Error Rate',
                'unit' => 'bps',
                'icon' => 'exclamation-triangle',
            ),
            'frequency' => array(
                'short' => 'Frequency',
                'long' => 'Frequency',
                'unit' => 'MHz',
                'icon' => 'line-chart',
            ),
            'distance' => array(
                'short' => 'Distance',
                'long' => 'Distance',
                'unit' => 'km',
                'icon' => 'space-shuttle',
            ),
        );

        if ($valid) {
            $sql = 'SELECT `sensor_class` FROM `wireless_sensors`';
            $params = array();
            if (isset($device_id)) {
                $sql .= ' WHERE `device_id`=?';
                $params[] = $device_id;
            }
            $sql .= ' GROUP BY `sensor_class`';

            $sensors = dbFetchColumn($sql, $params);
            return array_intersect_key($types, array_flip($sensors));
        }

        return $types;
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
     * Convert a WiFi channel to a Frequency in MHz
     *
     * @param $channel
     * @return int
     */
    public static function channelToFrequency($channel)
    {
        $channels = array(
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
        );

        return $channels[$channel];
    }

    /**
     * Returns if this model passes validation and should be saved to the database
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    public static function runDiscovery(OS $os)
    {
        $sensors  = [];

        // check yaml first
//        $processors = self::processYaml($os);

        foreach (self::getTypes() as $type => $descr) {
            $sensors = array_merge($sensors, static::discoverType($os, $type));
        }

        self::sync(
            $os->getDeviceId(),
            $sensors,
            ['sensor_class', 'sensor_type', 'sensor_index'],
            ['sensor_current']
        );

        dbDeleteOrphans(static::$table, array('devices.device_id'));

        echo PHP_EOL;
    }

    protected static function discoverType(OS $os, $type)
    {
        $typeInterface = static::getDiscoveryInterface($type);
        if (!interface_exists($typeInterface)) {
            echo "ERROR: Discovery Interface doesn't exist! $typeInterface\n";
        }

        $have_discovery = $os instanceof $typeInterface;
        if ($have_discovery) {
            echo "$type: ";
            $function = static::getDiscoveryMethod($type);
            $sensors = $os->$function();
            if (!is_array($sensors)) {
                c_echo("%RERROR:%n $function did not return an array! Skipping discovery.");
                $sensors = [];
            }
        } else {
            $sensors = [];  // delete non existent sensors
        }


        if ($have_discovery) {
            echo PHP_EOL;
        }

        return $sensors;
    }

    private static function checkForDuplicateSensors($sensors)
    {
        $duplicate_check = array();
        $dup = false;

        foreach ($sensors as $sensor) {
            /** @var WirelessSensor $sensor */
            $key = $sensor->getUniqueId();
            if (isset($duplicate_check[$key])) {
                c_echo("%R ERROR:%n A sensor already exists at this index $key ");
                $dup = true;
            }
            $duplicate_check[$key] = 1;
        }

        return $dup;
    }

    /**
     * Returns a string that must be unique for each sensor
     * type (class), subtype (type), index (index)
     *
     * @return string
     */
    private function getUniqueId()
    {
        return $this->type . '-' . $this->subtype . '-' . $this->index;
    }

    public static function poll(OS $os)
    {
        $table = static::$table;

        // fetch and group sensors, decode oids
        $sensors = array_reduce(
            dbFetchRows("SELECT * FROM `$table` WHERE `device_id` = ?", array($os->getDeviceId())),
            function ($carry, $sensor) {
                $sensor['sensor_oids'] = json_decode($sensor['sensor_oids']);
                $carry[$sensor['sensor_class']][] = $sensor;
                return $carry;
            },
            array()
        );

        foreach ($sensors as $type => $type_sensors) {
            // check for custom polling
            $typeInterface = static::getPollingInterface($type);
            if (!interface_exists($typeInterface)) {
                echo "ERROR: Polling Interface doesn't exist! $typeInterface\n";
            }

            // fetch custom data
            if ($os instanceof $typeInterface) {
                unset($sensors[$type]);  // remove from sensors array to prevent double polling
                static::pollSensorType($os, $type, $type_sensors);
            }
        }

        // pre-fetch all standard sensors
        $standard_sensors = call_user_func_array('array_merge', $sensors);
        $pre_fetch = self::fetchSnmpData($os->getDevice(), $standard_sensors);

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
     * @param array $sensors
     * @param array $prefetch
     */
    protected static function pollSensorType($os, $type, $sensors, $prefetch = array())
    {
        echo "$type:\n";

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
     * @param array $device
     * @param array $sensors
     * @return array
     */
    private static function fetchSnmpData($device, $sensors)
    {
        $oids = self::getOidsFromSensors($sensors, get_device_oid_limit($device));

        $snmp_data = array();
        foreach ($oids as $oid_chunk) {
            $multi_data = snmp_get_multi_oid($device, $oid_chunk, '-OUQnt');
            $snmp_data = array_merge($snmp_data, $multi_data);
        }

        // deal with string values that may be surrounded by quotes
        array_walk($snmp_data, function (&$oid) {
            $oid = trim($oid, '"') + 0;
        });

        return $snmp_data;
    }


    /**
     * Process the snmp data for the specified sensors
     * Returns an array sensor_id => value
     *
     * @param $sensors
     * @param $prefetch
     * @return array
     * @internal param $device
     */
    protected static function processSensorData($sensors, $prefetch)
    {
        $sensor_data = array();
        foreach ($sensors as $sensor) {
            // pull out the data for this sensor
            $requested_oids = array_flip($sensor['sensor_oids']);
            $data = array_intersect_key($prefetch, $requested_oids);

            // if no data set null and continue to the next sensor
            if (empty($data)) {
                $data[$sensor['sensor_id']] = null;
                continue;
            }

            if (count($data) > 1) {
                // aggregate data
                if ($sensor['sensor_aggregator'] == 'avg') {
                    $sensor_value = array_sum($data) / count($data);
                } else {
                    // sum
                    $sensor_value = array_sum($data);
                }
            } else {
                $sensor_value = current($data);
            }

            if ($sensor['sensor_divisor'] && $sensor_value !== 0) {
                $sensor_value = ($sensor_value / $sensor['sensor_divisor']);
            }

            if ($sensor['sensor_multiplier']) {
                $sensor_value = ($sensor_value * $sensor['sensor_multiplier']);
            }

            $sensor_data[$sensor['sensor_id']] = $sensor_value;
        }

        return $sensor_data;
    }


    /**
     * Get a list of unique oids from an array of sensors and break it into chunks.
     *
     * @param $sensors
     * @param int $chunk How many oids per chunk.  Default 10.
     * @return array
     */
    private static function getOidsFromSensors($sensors, $chunk = 10)
    {
        // Sort the incoming oids and sensors
        $oids = array_reduce($sensors, function ($carry, $sensor) {
            return array_merge($carry, $sensor['sensor_oids']);
        }, array());

        // only unique oids and chunk
        $oids = array_chunk(array_keys(array_flip($oids)), $chunk);

        return $oids;
    }

    /**
     * Record sensor data in the database and data stores
     *
     * @param $os
     * @param $sensors
     * @param $data
     */
    protected static function recordSensorData(OS $os, $sensors, $data)
    {
        $types = static::getTypes();

        foreach ($sensors as $sensor) {
            $sensor_value = $data[$sensor['sensor_id']];

            echo "  {$sensor['sensor_descr']}: $sensor_value {$types[$sensor['sensor_class']]['unit']}\n";

            // update rrd and database
            $rrd_name = array(
                static::$data_name,
                $sensor['sensor_class'],
                $sensor['sensor_type'],
                $sensor['sensor_index']
            );
            $rrd_type = isset($types[$sensor['sensor_class']]['type']) ? strtoupper($types[$sensor['sensor_class']]['type']) : 'GAUGE';
            $rrd_def = RrdDefinition::make()->addDataset('sensor', $rrd_type);

            $fields = array(
                'sensor' => isset($sensor_value) ? $sensor_value : 'U',
            );

            $tags = array(
                'sensor_class' => $sensor['sensor_class'],
                'sensor_type' => $sensor['sensor_type'],
                'sensor_descr' => $sensor['sensor_descr'],
                'sensor_index' => $sensor['sensor_index'],
                'rrd_name' => $rrd_name,
                'rrd_def' => $rrd_def
            );
            data_update($os->getDevice(), static::$data_name, $tags, $fields);

            $update = array(
                'sensor_prev' => $sensor['sensor_current'],
                'sensor_current' => $sensor_value,
                'lastupdate' => array('NOW()'),
            );
            dbUpdate($update, static::$table, "`sensor_id` = ?", array($sensor['sensor_id']));
        }
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
//        $sensor->type = $type;
//        $sensor->device_id = $device_id;
//        $sensor->oids = $oids;
//        $sensor->subtype = $subtype;
//        $sensor->index = $index;
//        $sensor->description = $description;
//        $sensor->current = $current;
//        $sensor->multiplier = $multiplier;
//        $sensor->divisor = $divisor;
//        $sensor->aggregator = $aggregator;
//        $sensor->access_point_ip = $access_point_id;
//        $sensor->high_limit = $high_limit;
//        $sensor->low_limit = $low_limit;
//        $sensor->high_warn = $high_warn;
//        $sensor->low_warn = $low_warn;
//        $sensor->entPhysicalIndex = $entPhysicalIndex;
//        $sensor->entPhysicalMeasured = $entPhysicalMeasured;

        $precision = $data['precision'] ?: 1;

        return static::discover(
            $data['type'] ?: $os->getName(),
            $os->getDeviceId(),
            (array)$data['oids'],
            isset($data['index']) ? $data['index'] : $index,
            $data['descr'] ?: 'Processor',
            $precision,
            static::processData($data['value'], $precision),
            $data['warn_percent'],
            $data['entPhysicalIndex'],
            $data['hrDeviceIndex']
        );
    }
}
