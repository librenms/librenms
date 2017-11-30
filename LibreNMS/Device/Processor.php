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

    private $type;
    private $device_id;
    private $oid;
    private $index;
    private $description;
    private $divisor;
    private $current;
    private $entPhysicalIndex;
    private $hrDeviceIndex;

    /**
     * Processor constructor.
     * @param string $type
     * @param int $device_id
     * @param string $oid
     * @param int $index
     * @param string $description
     * @param int $divisor
     * @param int $current
     * @param int $entPhysicalIndex
     * @param int $hrDeviceIndex
     */
    public function __construct(
        $type,
        $device_id,
        $oid,
        $index,
        $description,
        $divisor = 1,
        $current = null,
        $entPhysicalIndex = null,
        $hrDeviceIndex = null)
    {

        $this->type = $type;
        $this->device_id = $device_id;
        $this->oid = $oid;
        $this->index = $index;
        $this->description = $description;
        $this->divisor = $divisor;
        $this->current = $current;
        $this->entPhysicalIndex = $entPhysicalIndex;
        $this->hrDeviceIndex = $hrDeviceIndex;
    }

    public static function discover(OS $os)
    {
        if ($os instanceof ProcessorDiscovery) {
            $processors = $os->discoverProcessors();

            // TODO: sync database
            $db_processors = dbFetchRows('SELECT * FROM processors WHERE device_id=?', array($os->getDeviceId()));
            array_by

            foreach ($processors as $processor) {

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
            /** @var string $processor_descr */

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

        // don't fetch too much at a time TODO build into snmp_get_multi_oid?
        $snmp_data = array();
        foreach (array_chunk($oids, get_device_oid_limit($os->getDevice())) as $oid_chunk) {
            $multi_data = snmp_get_multi_oid($os->getDevice(), $oid_chunk);
            $snmp_data = array_merge($snmp_data, $multi_data);
        }

        $results = array();
        foreach ($processors as $processor) {
            if (isset($snmp_data[$processor['processor_oid']])) {
                preg_match('/([0-9]{1,3}(\.[0-9])?)/', $snmp_data[$processor['processor_oid']], $matches);
                $value = $matches[1];
            } else {
                $value = 0;
            }

            $precision = $processor['processor_precision'];
            if ($precision < 0) {
                // idle value, subtract from 100
                $value = 100 - ($value / ($precision * -1));
            } elseif ($precision > 1) {
                $value = $value / $precision;
            }

            $results[$processor['processor_id']] = $value;
        }

        return $results;
    }

    function discover_processor(&$valid, $device, $oid, $index, $type, $descr, $precision = '1', $current = null, $entPhysicalIndex = null, $hrDeviceIndex = null)
    {
        d_echo("Discover Processor: $oid, $index, $type, $descr, $precision, $current, $entPhysicalIndex, $hrDeviceIndex\n");

        if ($descr) {
            $descr = trim(str_replace('"', '', $descr));
            if (dbFetchCell('SELECT COUNT(processor_id) FROM `processors` WHERE `processor_index` = ? AND `device_id` = ? AND `processor_type` = ?', array($index, $device['device_id'], $type)) == '0') {
                $insert_data = array(
                    'device_id' => $device['device_id'],
                    'processor_descr' => $descr,
                    'processor_index' => $index,
                    'processor_oid' => $oid,
                    'processor_usage' => $current,
                    'processor_type' => $type,
                    'processor_precision' => $precision,
                );
                if (!empty($hrDeviceIndex)) {
                    $insert_data['hrDeviceIndex'] = $hrDeviceIndex;
                }

                if (!empty($entPhysicalIndex)) {
                    $insert_data['entPhysicalIndex'] = $entPhysicalIndex;
                }

                $inserted = dbInsert($insert_data, 'processors');
                echo '+';
                log_event('Processor added: type ' . mres($type) . ' index ' . mres($index) . ' descr ' . mres($descr), $device, 'processor', 3, $inserted);
            } else {
                echo '.';
                $update_data = array(
                    'processor_descr' => $descr,
                    'processor_oid' => $oid,
                    'processor_usage' => $current,
                    'processor_precision' => $precision,
                );
                dbUpdate($update_data, 'processors', '`device_id`=? AND `processor_index`=? AND `processor_type`=?', array($device['device_id'], $index, $type));
            }//end if
            $valid[$type][$index] = 1;
        }//end if
    }
}
