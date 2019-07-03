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

use App\Models\Device;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Device\YamlDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS\Generic;
use LibreNMS\OS\Traits\HostResources;
use LibreNMS\OS\Traits\UcdResources;

class OS implements ProcessorDiscovery
{
    use HostResources {
        HostResources::discoverProcessors as discoverHrProcessors;
    }
    use UcdResources {
        UcdResources::discoverProcessors as discoverUcdProcessors;
    }

    private $device; // annoying use of references to make sure this is in sync with global $device variable
    private $device_model;
    private $cache; // data cache
    private $pre_cache; // pre-fetch data cache

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

    /**
     * Get the Eloquent Device Model for the current device
     *
     * @return Device
     */
    public function getDeviceModel()
    {
        if (is_null($this->device_model)) {
            $this->device_model = Device::find($this->getDeviceId());
        }

        return $this->device_model;
    }

    public function preCache()
    {
        if (is_null($this->pre_cache)) {
            $this->pre_cache = YamlDiscovery::preCache($this);
        }

        return $this->pre_cache;
    }

    /**
     * Snmpwalk the specified oid and return an array of the data indexed by the oid index.
     * If the data is cached, return the cached data.
     * DO NOT use numeric oids with this function! The snmp result must contain only one oid.
     *
     * @param string $oid textual oid
     * @param string $mib mib for this oid
     * @param string $snmpflags snmpflags for this oid
     * @return array array indexed by the snmp index with the value as the data returned by snmp
     */
    public function getCacheByIndex($oid, $mib = null, $snmpflags = '-OQUs')
    {
        if (str_contains($oid, '.')) {
            echo "Error: don't use this with numeric oids!\n";
            return null;
        }

        if (!isset($this->cache['cache_oid'][$oid])) {
            $data = snmpwalk_cache_oid($this->getDevice(), $oid, array(), $mib, null, $snmpflags);
            $this->cache['cache_oid'][$oid] = array_map('current', $data);
        }

        return $this->cache['cache_oid'][$oid];
    }

    /**
     * Snmpwalk the specified oid table and return an array of the data indexed by the oid index.
     * If the data is cached, return the cached data.
     * DO NOT use numeric oids with this function! The snmp result must contain only one oid.
     *
     * @param string $oid textual oid
     * @param string $mib mib for this oid (optional)
     * @param string $depth depth for snmpwalk_group (optional)
     * @return array array indexed by the snmp index with the value as the data returned by snmp
     */
    public function getCacheTable($oid, $mib = null, $depth = 1)
    {
        if (str_contains($oid, '.')) {
            echo "Error: don't use this with numeric oids!\n";
            return null;
        }

        if (!isset($this->cache['group'][$depth][$oid])) {
            $this->cache['group'][$depth][$oid] = snmpwalk_group($this->getDevice(), $oid, $mib, $depth);
        }

        return $this->cache['group'][$depth][$oid];
    }

    /**
     * Check if an OID has been cached
     *
     * @param $oid
     * @return bool
     */
    public function isCached($oid)
    {
        return isset($this->cache['cache_oid'][$oid]);
    }

    /**
     * OS Factory, returns an instance of the OS for this device
     * If no specific OS is found, Try the OS group.
     * Otherwise, returns Generic
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

        // If not a specific OS, check for a group one.
        if (isset($device['os_group'])) {
            $class = str_to_class($device['os_group'], 'LibreNMS\\OS\\Shared\\');
            d_echo('Attempting to initialize OS: ' . $device['os_group'] . PHP_EOL);
            if (class_exists($class)) {
                d_echo("OS initialized: $class\n");
                return new $class($device);
            }
        }

        d_echo("OS initialized as Generic\n");
        return new Generic($device);
    }

    public function getName()
    {
        if (isset($this->device['os'])) {
            return $this->device['os'];
        }

        $rf = new \ReflectionClass($this);
        $name = $rf->getShortName();
        preg_match_all("/[A-Z][a-z]*/", $name, $segments);

        return implode('-', array_map('strtolower', $segments[0]));
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
        $processors = $this->discoverHrProcessors();

        if (empty($processors)) {
            $processors = $this->discoverUcdProcessors();
        }

        return $processors;
    }
}
