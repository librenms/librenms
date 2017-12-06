<?php
/**
 * OS.php
 *
 * Base OS class
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

namespace LibreNMS;

use LibreNMS\Device\Processor;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Device\YamlDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS\Generic;

class OS implements ProcessorDiscovery
{
    private $device; // annoying use of references to make sure this is in sync with global $device variable
    private $cache; // data cache

    /**
     * OS constructor. Not allowed to be created directly.  Use OS::make()
     * @param $device
     */
    private function __construct(&$device)
    {
        $this->device = &$device;
    }

    /**
     * Get the device array that owns this OS instance
     *
     * @return array
     */
    public function &getDevice()
    {
        return $this->device;
    }

    /**
     * Get the device_id of the device that owns this OS instance
     *
     * @return int
     */
    public function getDeviceId()
    {
        return (int)$this->device['device_id'];
    }

    public function preCache()
    {
        if (is_null($this->cache)) {
            $this->cache = YamlDiscovery::preCache($this);
        }

        return $this->cache;
    }


    /**
     * Snmpwalk the specified oid and return an array of the data indexed by the oid index.
     * If the data is cached, return the cached data.
     * DO NOT use numeric oids with this function! The snmp result must contain only one oid.
     *
     * @param string $oid textual oid
     * @param string $mib mib for this oid
     * @return array array indexed by the snmp index with the value as the data returned by snmp
     * @throws \Exception
     */
    protected function getCacheByIndex($oid, $mib = null)
    {
        if (str_contains($oid, '.')) {
            throw new \Exception('Error: don\'t use this with numeric oids');
        }

        if (!isset($this->cache[$oid])) {
            $data = snmpwalk_cache_oid($this->getDevice(), $oid, array(), $mib);
            $this->cache[$oid] = array_map('current', $data);
        }

        return $this->cache[$oid];
    }

    /**
     * OS Factory, returns an instance of the OS for this device
     * If no specific OS is found, returns Generic
     *
     * @param array $device device array, must have os set
     * @return OS
     */
    public static function make(&$device)
    {
        $class = str_to_class($device['os'], 'LibreNMS\\OS\\');
        d_echo('Attempting to initialize OS: ' . $device['os'] . PHP_EOL);
        if (class_exists($class)) {
            d_echo("OS initialized: $class\n");
            return new $class($device);
        }

        return new Generic($device);
    }

    public function newYaml($class, $data)
    {
        d_echo($class);
        d_echo($data);

        return new Processor(
            $data['type'],
            $this->getDeviceId(),
            $data['num_oid'],
            $data['index'],
            $data['descr'],
            $data['precision'],
            $data['value']
        );
    }


    /**
     * Poll a channel based OID, but return data in MHz
     *
     * @param array $sensors
     * @param callable $callback Function to modify the value before converting it to a frequency
     * @return array
     */
    protected function pollWirelessChannelAsFrequency($sensors, $callback = null)
    {
        if (empty($sensors)) {
            return array();
        }

        $oids = array();
        foreach ($sensors as $sensor) {
            $oids[$sensor['sensor_id']] = current($sensor['sensor_oids']);
        }

        $snmp_data = snmp_get_multi_oid($this->getDevice(), $oids);

        $data = array();
        foreach ($oids as $id => $oid) {
            if (isset($callback)) {
                $channel = call_user_func($callback, $snmp_data[$oid]);
            } else {
                $channel = $snmp_data[$oid];
            }

            $data[$id] = WirelessSensor::channelToFrequency($channel);
        }

        return $data;
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        echo "Yaml: ";
        $yamlProcs = Processor::processYaml($this);

        if (!empty($yamlProcs)) {
            return $yamlProcs;
        }

        $hrProcs = $this->discoverHrProcessors();
        if (!empty($hrProcs)) {
            return $hrProcs;
        }

        return $this->discoverUcdProcessors();
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    private function discoverHrProcessors()
    {  // TODO PHP 5.4 extract to trait
        $processors = array();

        echo ' hrDevice: ';
        $hrDeviceDescr = $this->getCacheByIndex('hrDeviceDescr', 'HOST-RESOURCES-MIB');

        if (empty($hrDeviceDescr)) {
            // no hr data, return
            return array();
        }

        $hrProcessorLoad = $this->getCacheByIndex('hrProcessorLoad', 'HOST-RESOURCES-MIB');

        foreach ($hrProcessorLoad as $index => $usage) {
            $usage_oid = '.1.3.6.1.2.1.25.3.3.1.2.' . $index;
            $descr = $hrDeviceDescr[$index];

            if (!is_numeric($usage)) {
                continue;
            }

            $device = $this->getDevice();
            if ($device['os'] == 'arista-eos' && $index == '1') {
                continue;
            }

            if (empty($descr)
                || $descr == 'Unknown Processor Type' // Windows: Unknown Processor Type
                || $descr == 'An electronic chip that makes the computer work.'
            ) {
                $descr = 'Processor';
            } else {
                // Make the description a bit shorter
                $remove_strings = array(
                    'CPU ',
                    '(TM)',
                    '(R)',
                );
                $descr = str_replace($remove_strings, '', $descr);
            }

            $old_name = array('hrProcessor', $index);
            $new_name = array('processor', 'hr', $index);
            rrd_file_rename($this->getDevice(), $old_name, $new_name);

            $processors[] = new Processor(
                'hr',
                $this->getDeviceId(),
                $usage_oid,
                $index,
                $descr,
                1,
                $usage,
                '',
                $index
            );
        }

        return $processors;
    }

    private function discoverUcdProcessors()
    {
        echo 'UCD: ';

        return array(
            new Processor(
                'ucd-old',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2021.11.11.0',
                0,
                'CPU',
                -1
            )
        );
    }
}
