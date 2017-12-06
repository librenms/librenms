<?php
/**
 * Processor.php
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */


namespace LibreNMS\Device;

use LibreNMS\Interfaces\Discovery\DiscoveryModule;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Polling\PollerModule;
use LibreNMS\Interfaces\Polling\ProcessorPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class Processor implements DiscoveryModule, PollerModule
{
    protected static $name = 'Processor';
    protected static $table = 'processors';
    protected static $data_name = 'processor';

    private $valid = true;

    private $id;
    private $type;
    private $usage;
    private $device_id;
    private $oid;
    private $index;
    private $description;
    private $precision;
    private $entPhysicalIndex;
    private $hrDeviceIndex;
    private $perc_warn = 75;

    /**
     * Processor constructor.
     * @param string $type
     * @param int $device_id
     * @param string $oid
     * @param int $index
     * @param string $description
     * @param int $precision The returned value will be divided by this number (should be factor of 10) If negative this oid returns idle cpu
     * @param int $current_usage
     * @param int $entPhysicalIndex
     * @param int $hrDeviceIndex
     */
    public function __construct(
        $type,
        $device_id,
        $oid,
        $index,
        $description,
        $precision = 1,
        $current_usage = null,
        $entPhysicalIndex = null,
        $hrDeviceIndex = null)
    {
        $this->type = $type;
        $this->device_id = $device_id;
        $this->oid = '.' . ltrim($oid, '.'); // ensure leading dot
        $this->index = $index;
        $this->description = $description;
        $this->precision = $precision;
        $this->usage = $current_usage;
        $this->entPhysicalIndex = $entPhysicalIndex;
        $this->hrDeviceIndex = $hrDeviceIndex;

        // validity not checked yet
        if (is_null($this->usage)) {
            $data = snmp_get(device_by_id_cache($device_id), $this->oid, '-Ovq');
            $this->valid = ($data !== false);
            if (!$this->valid) {
                return;
            }
            $this->usage = static::processData($data, $precision);
        }

        d_echo('Discovered ' . get_called_class() . ' ' . print_r($this->toArray(), true));
    }

    public static function discover(OS $os)
    {
        if ($os instanceof ProcessorDiscovery) {
            $processors = $os->discoverProcessors();

            if (is_array($processors)) {
                self::sync($os->getDeviceId(), $processors);
            }
        }
    }

    public static function poll(OS $os)
    {
        $processors = dbFetchRows('SELECT * FROM processors WHERE device_id=?', array($os->getDeviceId()));

        if ($os instanceof ProcessorPolling) {
            $data = $os->pollProcessors($processors);
        } else {
            $data = static::pollProcessors($os, $processors);
        }

        $rrd_def = RrdDefinition::make()->addDataset('usage', 'GAUGE', -273, 1000);

        foreach ($processors as $index => $processor) {
            extract($processor); // extract db fields to variables
            /** @var int $processor_id */
            /** @var string $processor_type */
            /** @var int $processor_index */
            /** @var int $processor_usage */

            if (isset($data[$processor_id])) {
                $usage = round($data[$processor_id], 2);
                echo "$usage%\n";

                $rrd_name = array('processor', $processor_type, $processor_index);
                $fields = compact('usage');
                $tags = compact('processor_type', 'processor_index', 'rrd_name', 'rrd_def');
                data_update($os->getDevice(), 'processors', $tags, $fields);

                if ($usage != $processor_usage) {
                    dbUpdate(array('processor_usage' => $usage), 'processors', '`processor_id` = ?', array($processor_id));
                }
            }
        }
    }

    private static function pollProcessors(OS $os, $processors)
    {
        if (empty($processors)) {
            return array();
        }

        $oids = array_column($processors, 'processor_oid');

        // don't fetch too many at a time TODO build into snmp_get_multi_oid?
        $snmp_data = array();
        foreach (array_chunk($oids, get_device_oid_limit($os->getDevice())) as $oid_chunk) {
            $multi_data = snmp_get_multi_oid($os->getDevice(), $oid_chunk);
            $snmp_data = array_merge($snmp_data, $multi_data);
        }

        $results = array();
        foreach ($processors as $processor) {
            if (isset($snmp_data[$processor['processor_oid']])) {
                $value = static::processData(
                    $snmp_data[$processor['processor_oid']],
                    $processor['processor_precision']
                );
            } else {
                $value = 0;
            }

            $results[$processor['processor_id']] = $value;
        }

        return $results;
    }

    private static function processData($data, $precision) {
        preg_match('/([0-9]{1,3}(\.[0-9])?)/', $data, $matches);
        $value = $matches[1];

        if ($precision < 0) {
            // idle value, subtract from 100
            $value = 100 - ($value / ($precision * -1));
        } elseif ($precision > 1) {
            $value = $value / $precision;
        }

        return $value;
    }

    public static function processYaml(OS $os)
    {
        $device = $os->getDevice();
        if (empty($device['dynamic_discovery']['modules']['processors'])) {
            return array();
        }

        return YamlDiscovery::discover($os, get_class(), $device['dynamic_discovery']['modules']['processors']);
    }


    /**
     * Fetch the sensor from the database.
     * If it doesn't exist, returns null.
     *
     * @return array|null
     */
    private function fetch()
    {
        $table = static::$table;
        $id = 'processor_id';
        if (isset($this->id)) {
            return dbFetchRow(
                "SELECT `$table` FROM ? WHERE `$id`=?",
                array($this->id)
            );
        }

        $row = dbFetchRow(
            "SELECT * FROM `$table` " .
            "WHERE `device_id`=? AND `processor_index`=? AND `processor_type`=?",
            array($this->device_id, $this->index, $this->type)
        );
        $this->id = $row[$id];
        return $row;
    }

    /**
     * Save processors and remove invalid processors
     * This the sensors array should contain all the sensors of a specific class
     * It may contain sensors from multiple tables and devices, but that isn't the primary use
     *
     * @param int $device_id
     * @param array $processors
     */
    final public static function sync($device_id, array $processors)
    {
        // save and collect valid ids
        $valid_ids = array();
        foreach ($processors as $processor) {
            /** @var $this $processor */
            if ($processor->isValid()) {
                $valid_ids[] = $processor->save();
            }
        }

        // delete invalid sensors
        self::clean($device_id, $valid_ids);
    }

    /**
     * Save this processor to the database.
     *
     * @return int the processor_id of this processor in the database
     */
    final public function save()
    {
        $db_proc = $this->fetch();

        if ($db_proc) {
            $new_proc = $this->toArray(array('processor_id', 'processor_usage'));
            $update = array_diff($new_proc, $db_proc);

            if (empty($update)) {
                echo '.';
            } else {
                dbUpdate($update, static::$table, '`processor_id`=?', array($this->id));
                echo 'U';
            }
        } else {
            $new_proc = $this->toArray(array('processor_id'));
            $this->id = dbInsert($new_proc, static::$table);
            if ($this->id !== null) {
                $name = static::$name;
                $message = "$name Discovered: {$this->type} {$this->index} {$this->description}";
                log_event($message, $this->device_id, static::$table, 3, $this->id);
                echo '+';
            }
        }

        return $this->id;
    }

    /**
     * Remove invalid processors.  Passing an empty array will remove all processors
     *
     * @param int $device_id
     * @param array $processor_ids valid processor ids
     */
    private static function clean($device_id, $processor_ids)
    {
        $table = static::$table;
        $params = array($device_id);
        $where = '`device_id`=?';

        if (!empty($processor_ids)) {
            $where .= ' AND `sensor_id` NOT IN ' . dbGenPlaceholders(count($processor_ids));
            $params = array_merge($params, $processor_ids);
        }

        $delete = dbFetchRows("SELECT * FROM `$table` WHERE $where", $params);
        foreach ($delete as $processor) {
            echo '-';

            $message = static::$name;
            $message .= " Deleted: {$processor['processor_type']} {$processor['processor_index']} {$processor['processor_descr']}";
            log_event($message, $device_id, static::$table, 3, $processor['processor_id']);
        }
        if (!empty($delete)) {
            dbDelete($table, $where, $params);
        }
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
     * Get an array of this sensor with fields that line up with the database.
     *
     * @param array $exclude exclude columns
     * @return array
     */
    public function toArray($exclude = array())
    {
        $array = array(
            'processor_id' => $this->id,
            'entPhysicalIndex' => (int)$this->entPhysicalIndex,
            'hrDeviceIndex' => (int)$this->hrDeviceIndex,
            'device_id' => $this->device_id,
            'processor_oid' => $this->oid,
            'processor_index' => $this->index,
            'processor_type' => $this->type,
            'processor_usage' => $this->usage,
            'processor_descr' => $this->description,
            'processor_precision' => (int)$this->precision,
            'processor_perc_warn' => (int)$this->perc_warn,
        );

        return array_diff_key($array, array_flip($exclude));
    }
}
