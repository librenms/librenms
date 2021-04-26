<?php
/**
 * Sensor.php
 *
 * Base Sensor class
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Device;

use LibreNMS\Config;
use LibreNMS\Interfaces\Discovery\DiscoveryModule;
use LibreNMS\Interfaces\Polling\PollerModule;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class Sensor implements DiscoveryModule, PollerModule
{
    protected static $name = 'Sensor';
    protected static $table = 'sensors';
    protected static $data_name = 'sensor';
    protected static $translation_prefix = 'sensors';

    private $valid = true;

    private $sensor_id;

    private $type;
    private $device_id;
    private $oids;
    private $subtype;
    private $index;
    private $description;
    private $current;
    private $multiplier;
    private $divisor;
    private $aggregator;
    private $high_limit;
    private $low_limit;
    private $high_warn;
    private $low_warn;
    private $entPhysicalIndex;
    private $entPhysicalMeasured;

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
     * @param int|float $high_limit Alerting: Maximum value
     * @param int|float $low_limit Alerting: Minimum value
     * @param int|float $high_warn Alerting: High warning value
     * @param int|float $low_warn Alerting: Low warning value
     * @param int|float $entPhysicalIndex The entPhysicalIndex this sensor is associated, often a port
     * @param int|float $entPhysicalMeasured the table to look for the entPhysicalIndex, for example 'ports' (maybe unused)
     */
    public function __construct(
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
        $high_limit = null,
        $low_limit = null,
        $high_warn = null,
        $low_warn = null,
        $entPhysicalIndex = null,
        $entPhysicalMeasured = null
    ) {
        $this->type = $type;
        $this->device_id = $device_id;
        $this->oids = (array) $oids;
        $this->subtype = $subtype;
        $this->index = $index;
        $this->description = $description;
        $this->current = $current;
        $this->multiplier = $multiplier;
        $this->divisor = $divisor;
        $this->aggregator = $aggregator;
        $this->entPhysicalIndex = $entPhysicalIndex;
        $this->entPhysicalMeasured = $entPhysicalMeasured;
        $this->high_limit = $high_limit;
        $this->low_limit = $low_limit;
        $this->high_warn = $high_warn;
        $this->low_warn = $low_warn;

        // ensure leading dots
        array_walk($this->oids, function (&$oid) {
            $oid = '.' . ltrim($oid, '.');
        });

        $sensor = $this->toArray();
        // validity not checked yet
        if (is_null($this->current)) {
            $sensor['sensor_oids'] = $this->oids;
            $sensors = [$sensor];

            $prefetch = self::fetchSnmpData(device_by_id_cache($device_id), $sensors);
            $data = static::processSensorData($sensors, $prefetch);

            $this->current = current($data);
            $this->valid = is_numeric($this->current);
        }

        d_echo('Discovered ' . get_called_class() . ' ' . print_r($sensor, true));
    }

    /**
     * Save this sensor to the database.
     *
     * @return int the sensor_id of this sensor in the database
     */
    final public function save()
    {
        $db_sensor = $this->fetch();

        $new_sensor = $this->toArray();
        if ($db_sensor) {
            unset($new_sensor['sensor_current']); // if updating, don't check sensor_current
            $update = array_diff_assoc($new_sensor, $db_sensor);

            if ($db_sensor['sensor_custom'] == 'Yes') {
                unset($update['sensor_limit']);
                unset($update['sensor_limit_warn']);
                unset($update['sensor_limit_low']);
                unset($update['sensor_limit_low_warn']);
            }

            if (empty($update)) {
                echo '.';
            } else {
                dbUpdate($this->escapeNull($update), $this->getTable(), '`sensor_id`=?', [$this->sensor_id]);
                echo 'U';
            }
        } else {
            $this->sensor_id = dbInsert($this->escapeNull($new_sensor), $this->getTable());
            if ($this->sensor_id !== null) {
                $name = static::$name;
                $message = "$name Discovered: {$this->type} {$this->subtype} {$this->index} {$this->description}";
                log_event($message, $this->device_id, static::$table, 3, $this->sensor_id);
                echo '+';
            }
        }

        return $this->sensor_id;
    }

    /**
     * Fetch the sensor from the database.
     * If it doesn't exist, returns null.
     *
     * @return array|null
     */
    private function fetch()
    {
        $table = $this->getTable();
        if (isset($this->sensor_id)) {
            return dbFetchRow(
                "SELECT `$table` FROM ? WHERE `sensor_id`=?",
                [$this->sensor_id]
            );
        }

        $sensor = dbFetchRow(
            "SELECT * FROM `$table` " .
            'WHERE `device_id`=? AND `sensor_class`=? AND `sensor_type`=? AND `sensor_index`=?',
            [$this->device_id, $this->type, $this->subtype, $this->index]
        );
        $this->sensor_id = $sensor['sensor_id'];

        return $sensor;
    }

    /**
     * Get the table for this sensor
     * @return string
     */
    public function getTable()
    {
        return static::$table;
    }

    /**
     * Get an array of this sensor with fields that line up with the database.
     * Excludes sensor_id and current
     *
     * @return array
     */
    protected function toArray()
    {
        return [
            'sensor_class' => $this->type,
            'device_id' => $this->device_id,
            'sensor_oids' => json_encode($this->oids),
            'sensor_index' => $this->index,
            'sensor_type' => $this->subtype,
            'sensor_descr' => $this->description,
            'sensor_divisor' => $this->divisor,
            'sensor_multiplier' => $this->multiplier,
            'sensor_aggregator' => $this->aggregator,
            'sensor_limit' => $this->high_limit,
            'sensor_limit_warn' => $this->high_warn,
            'sensor_limit_low' => $this->low_limit,
            'sensor_limit_low_warn' => $this->low_warn,
            'sensor_current' => $this->current,
            'entPhysicalIndex' => $this->entPhysicalIndex,
            'entPhysicalIndex_measured' => $this->entPhysicalMeasured,
        ];
    }

    /**
     * Escape null values so dbFacile doesn't mess them up
     * honestly, this should be the default, but could break shit
     *
     * @param array $array
     * @return array
     */
    private function escapeNull($array)
    {
        return array_map(function ($value) {
            return is_null($value) ? ['NULL'] : $value;
        }, $array);
    }

    /**
     * Run Sensors discovery for the supplied OS (device)
     *
     * @param OS $os
     */
    public static function runDiscovery(OS $os)
    {
        // Add discovery types here
    }

    /**
     * Poll sensors for the supplied OS (device)
     *
     * @param OS $os
     */
    public static function poll(OS $os)
    {
        $table = static::$table;

        $query = "SELECT * FROM `$table` WHERE `device_id` = ?";
        $params = [$os->getDeviceId()];

        $submodules = Config::get('poller_submodules.wireless', []);
        if (! empty($submodules)) {
            $query .= ' AND `sensor_class` IN ' . dbGenPlaceholders(count($submodules));
            $params = array_merge($params, $submodules);
        }

        // fetch and group sensors, decode oids
        $sensors = array_reduce(
            dbFetchRows($query, $params),
            function ($carry, $sensor) {
                $sensor['sensor_oids'] = json_decode($sensor['sensor_oids']);
                $carry[$sensor['sensor_class']][] = $sensor;

                return $carry;
            },
            []
        );

        foreach ($sensors as $type => $type_sensors) {
            // check for custom polling
            $typeInterface = static::getPollingInterface($type);
            if (! interface_exists($typeInterface)) {
                echo "ERROR: Polling Interface doesn't exist! $typeInterface\n";
            }

            // fetch custom data
            if ($os instanceof $typeInterface) {
                unset($sensors[$type]);  // remove from sensors array to prevent double polling
                static::pollSensorType($os, $type, $type_sensors);
            }
        }

        // pre-fetch all standard sensors
        $standard_sensors = collect($sensors)->flatten(1)->all();
        $pre_fetch = self::fetchSnmpData($os->getDeviceArray(), $standard_sensors);

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
    protected static function pollSensorType($os, $type, $sensors, $prefetch = [])
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

        $snmp_data = [];
        foreach ($oids as $oid_chunk) {
            $multi_data = snmp_get_multi_oid($device, $oid_chunk, '-OUQnt');
            $snmp_data = array_merge($snmp_data, $multi_data);
        }

        // deal with string values that may be surrounded by quotes, scientific number format and remove non numerical characters
        array_walk($snmp_data, function (&$oid) {
            preg_match('/-?\d+(\.\d+)?(e-?\d+)?/i', $oid, $matches);
            if (isset($matches[0])) {
                $oid = cast_number($matches[0]);
            } else {
                $oid = trim('"', $oid); // allow string only values
            }
        });

        return $snmp_data;
    }

    /**
     * Process the snmp data for the specified sensors
     * Returns an array sensor_id => value
     *
     * @param array $sensors
     * @param array $prefetch
     * @return array
     * @internal param $device
     */
    protected static function processSensorData($sensors, $prefetch)
    {
        $sensor_data = [];
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
     * @param array $sensors
     * @param int $chunk How many oids per chunk.  Default 10.
     * @return array
     */
    private static function getOidsFromSensors($sensors, $chunk = 10)
    {
        // Sort the incoming oids and sensors
        $oids = array_reduce($sensors, function ($carry, $sensor) {
            return array_merge($carry, $sensor['sensor_oids']);
        }, []);

        // only unique oids and chunk
        $oids = array_chunk(array_keys(array_flip($oids)), $chunk);

        return $oids;
    }

    protected static function discoverType(OS $os, $type)
    {
        $typeInterface = static::getDiscoveryInterface($type);
        if (! interface_exists($typeInterface)) {
            echo "ERROR: Discovery Interface doesn't exist! $typeInterface\n";
        }

        $have_discovery = $os instanceof $typeInterface;
        if ($have_discovery) {
            echo "$type: ";
            $function = static::getDiscoveryMethod($type);
            $sensors = $os->$function();
            if (! is_array($sensors)) {
                c_echo("%RERROR:%n $function did not return an array! Skipping discovery.");
                $sensors = [];
            }
        } else {
            $sensors = [];  // delete non existent sensors
        }

        self::checkForDuplicateSensors($sensors);

        self::sync($os->getDeviceId(), $type, $sensors);

        if ($have_discovery) {
            echo PHP_EOL;
        }
    }

    private static function checkForDuplicateSensors($sensors)
    {
        $duplicate_check = [];
        $dup = false;

        foreach ($sensors as $sensor) {
            /** @var Sensor $sensor */
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

    protected static function getDiscoveryInterface($type)
    {
        return str_to_class($type, 'LibreNMS\\Interfaces\\Discovery\\Sensors\\') . 'Discovery';
    }

    protected static function getDiscoveryMethod($type)
    {
        return 'discover' . str_to_class($type);
    }

    protected static function getPollingInterface($type)
    {
        return str_to_class($type, 'LibreNMS\\Interfaces\\Polling\\Sensors\\') . 'Polling';
    }

    protected static function getPollingMethod($type)
    {
        return 'poll' . str_to_class($type);
    }

    /**
     * Is this sensor valid?
     * If not, it should not be added to or in the database
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Save sensors and remove invalid sensors
     * This the sensors array should contain all the sensors of a specific class
     * It may contain sensors from multiple tables and devices, but that isn't the primary use
     *
     * @param int $device_id
     * @param string $type
     * @param array $sensors
     */
    final public static function sync($device_id, $type, array $sensors)
    {
        // save and collect valid ids
        $valid_sensor_ids = [];
        foreach ($sensors as $sensor) {
            /** @var $this $sensor */
            if ($sensor->isValid()) {
                $valid_sensor_ids[] = $sensor->save();
            }
        }

        // delete invalid sensors
        self::clean($device_id, $type, $valid_sensor_ids);
    }

    /**
     * Remove invalid sensors.  Passing an empty array will remove all sensors of that class
     *
     * @param int $device_id
     * @param string $type
     * @param array $sensor_ids valid sensor ids
     */
    private static function clean($device_id, $type, $sensor_ids)
    {
        $table = static::$table;
        $params = [$device_id, $type];
        $where = '`device_id`=? AND `sensor_class`=?';

        if (! empty($sensor_ids)) {
            $where .= ' AND `sensor_id` NOT IN ' . dbGenPlaceholders(count($sensor_ids));
            $params = array_merge($params, $sensor_ids);
        }

        $delete = dbFetchRows("SELECT * FROM `$table` WHERE $where", $params);
        foreach ($delete as $sensor) {
            echo '-';

            $message = static::$name;
            $message .= " Deleted: $type {$sensor['sensor_type']} {$sensor['sensor_index']} {$sensor['sensor_descr']}";
            log_event($message, $device_id, static::$table, 3, $sensor['sensor_id']);
        }
        if (! empty($delete)) {
            dbDelete($table, $where, $params);
        }
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
        return [];
    }

    /**
     * Record sensor data in the database and data stores
     *
     * @param OS $os
     * @param array $sensors
     * @param array $data
     */
    protected static function recordSensorData(OS $os, $sensors, $data)
    {
        $types = static::getTypes();

        foreach ($sensors as $sensor) {
            $sensor_value = $data[$sensor['sensor_id']];

            echo "  {$sensor['sensor_descr']}: $sensor_value " . __(static::$translation_prefix . '.' . $sensor['sensor_class'] . '.unit') . PHP_EOL;

            // update rrd and database
            $rrd_name = [
                static::$data_name,
                $sensor['sensor_class'],
                $sensor['sensor_type'],
                $sensor['sensor_index'],
            ];
            $rrd_type = isset($types[$sensor['sensor_class']]['type']) ? strtoupper($types[$sensor['sensor_class']]['type']) : 'GAUGE';
            $rrd_def = RrdDefinition::make()->addDataset('sensor', $rrd_type);

            $fields = [
                'sensor' => isset($sensor_value) ? $sensor_value : 'U',
            ];

            $tags = [
                'sensor_class' => $sensor['sensor_class'],
                'sensor_type' => $sensor['sensor_type'],
                'sensor_descr' => $sensor['sensor_descr'],
                'sensor_index' => $sensor['sensor_index'],
                'rrd_name' => $rrd_name,
                'rrd_def' => $rrd_def,
            ];
            data_update($os->getDeviceArray(), static::$data_name, $tags, $fields);

            $update = [
                'sensor_prev' => $sensor['sensor_current'],
                'sensor_current' => $sensor_value,
                'lastupdate' => ['NOW()'],
            ];
            dbUpdate($update, static::$table, '`sensor_id` = ?', [$sensor['sensor_id']]);
        }
    }
}
