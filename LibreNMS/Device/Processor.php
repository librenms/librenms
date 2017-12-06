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

use LibreNMS\Interfaces\Discovery\DiscoveryItem;
use LibreNMS\Interfaces\Discovery\DiscoveryModule;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Polling\PollerModule;
use LibreNMS\Interfaces\Polling\ProcessorPolling;
use LibreNMS\Model;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class Processor extends Model implements DiscoveryModule, PollerModule, DiscoveryItem
{
    protected static $table = 'processors';
    protected static $primaryKey = 'processor_id';

    private $valid = true;

    public $processor_id;
    public $device_id;
    public $processor_type;
    public $processor_usage;
    public $processor_oid;
    public $processor_index;
    public $processor_descr;
    public $processor_precision;
    public $entPhysicalIndex;
    public $hrDeviceIndex;
    public $processor_perc_warn = 75;

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
     * @return Processor
     */
    public static function make(
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
        $proc = new static();
        $proc->processor_type = $type;
        $proc->device_id = $device_id;
        $proc->processor_oid = '.' . ltrim($oid, '.'); // ensure leading dot
        $proc->processor_index = $index;
        $proc->processor_descr = $description;
        $proc->processor_precision = $precision;
        $proc->processor_usage = $current_usage;
        $proc->entPhysicalIndex = $entPhysicalIndex;
        $proc->hrDeviceIndex = $hrDeviceIndex;

        // validity not checked yet
        if (is_null($proc->processor_usage)) {
            $data = snmp_get(device_by_id_cache($device_id), $proc->processor_oid, '-Ovq');
            $proc->valid = ($data !== false);
            if (!$proc->valid) {
                return $proc;
            }
            $proc->processor_usage = static::processData($data, $precision);
        }

        d_echo('Discovered ' . get_called_class() . ' ' . print_r($proc->toArray(), true));
        return $proc;
    }

    public static function fromYaml(OS $os, array $data)
    {
        return static::make(
            $data['type'],
            $os->getDeviceId(),
            $data['num_oid'],
            $data['index'],
            $data['descr'] ?: 'Processor',
            $data['precision'] ?: 1,
            $data['value']
        );
    }

    public static function discover(OS $os)
    {
        if ($os instanceof ProcessorDiscovery) {
            $processors = $os->discoverProcessors();

            if (is_array($processors)) {
                self::sync(
                    $os->getDeviceId(),
                    $processors,
                    array('processor_index', 'processor_type'),
                    array('processor_usage')
                );
            }
        }

        dbDeleteOrphans('devices', static::$table, 'device_id');
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
            'processor_id' => $this->processor_id,
            'entPhysicalIndex' => (int)$this->entPhysicalIndex,
            'hrDeviceIndex' => (int)$this->hrDeviceIndex,
            'device_id' => $this->device_id,
            'processor_oid' => $this->processor_oid,
            'processor_index' => $this->processor_index,
            'processor_type' => $this->processor_type,
            'processor_usage' => $this->processor_usage,
            'processor_descr' => $this->processor_descr,
            'processor_precision' => (int)$this->processor_precision,
            'processor_perc_warn' => (int)$this->processor_perc_warn,
        );

        return array_diff_key($array, array_flip($exclude));
    }

    public static function onCreate($processor)
    {
        $message = "Processor Discovered: {$processor->processor_type} {$processor->processor_index} {$processor->processor_descr}";
        log_event($message, $processor->device_id, static::$table, 3, $processor->processor_id);

        parent::onCreate($processor);
    }

    public static function onDelete($processor)
    {
        $message = "Processor Deleted: {$processor->processor_type} {$processor->processor_index} {$processor->processor_descr}";
        log_event($message, $processor->device_id, static::$table, 3, $processor->processor_id);

        parent::onDelete($processor); // TODO: Change the autogenerated stub
    }
}
