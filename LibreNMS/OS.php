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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;

use App\Models\Device;
use App\Models\DeviceGraph;
use DeviceCache;
use Illuminate\Support\Str;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Device\YamlDiscovery;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS\Generic;
use LibreNMS\OS\Traits\HostResources;
use LibreNMS\OS\Traits\UcdResources;
use LibreNMS\OS\Traits\YamlMempoolsDiscovery;
use LibreNMS\OS\Traits\YamlOSDiscovery;

class OS implements ProcessorDiscovery, OSDiscovery, MempoolsDiscovery
{
    use HostResources {
        HostResources::discoverProcessors as discoverHrProcessors;
        HostResources::discoverMempools as discoverHrMempools;
    }
    use UcdResources {
        UcdResources::discoverProcessors as discoverUcdProcessors;
        UcdResources::discoverMempools as discoverUcdMempools;
    }
    use YamlOSDiscovery;
    use YamlMempoolsDiscovery;

    private $device; // annoying use of references to make sure this is in sync with global $device variable
    private $graphs; // stores device graphs
    private $cache; // data cache
    private $pre_cache; // pre-fetch data cache

    /**
     * OS constructor. Not allowed to be created directly.  Use OS::make()
     *
     * @param array $device
     */
    private function __construct(&$device)
    {
        $this->device = &$device;
        $this->graphs = [];
    }

    /**
     * Get the device array that owns this OS instance
     *
     * @return array
     */
    public function &getDeviceArray()
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
        return (int) $this->device['device_id'];
    }

    /**
     * Get the Eloquent Device Model for the current device
     *
     * @return Device
     */
    public function getDevice()
    {
        return DeviceCache::get($this->getDeviceId());
    }

    /**
     * Enable a graph for this device
     *
     * @param string $name
     */
    public function enableGraph($name)
    {
        $this->graphs[$name] = true;
    }

    public function persistGraphs()
    {
        $device = $this->getDevice();
        $graphs = collect(array_keys($this->graphs));

        // delete extra graphs
        $device->graphs->keyBy('graph')->collect()->except($graphs)->each->delete();
        // create missing graphs
        $device->graphs()->saveMany($graphs->diff($device->graphs->pluck('graph'))->map(function ($graph) {
            return new DeviceGraph(['graph' => $graph]);
        }));
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
     * @return array|null array indexed by the snmp index with the value as the data returned by snmp
     */
    public function getCacheByIndex($oid, $mib = null, $snmpflags = '-OQUs')
    {
        if (Str::contains($oid, '.')) {
            echo "Error: don't use this with numeric oids!\n";

            return null;
        }

        if (! isset($this->cache['cache_oid'][$oid])) {
            $data = snmpwalk_cache_oid($this->getDeviceArray(), $oid, [], $mib, null, $snmpflags);
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
     * @param int $depth depth for snmpwalk_group (optional)
     * @return array|null array indexed by the snmp index with the value as the data returned by snmp
     */
    public function getCacheTable($oid, $mib = null, $depth = 1)
    {
        if (Str::contains($oid, '.')) {
            echo "Error: don't use this with numeric oids!\n";

            return null;
        }

        if (! isset($this->cache['group'][$depth][$oid])) {
            $this->cache['group'][$depth][$oid] = snmpwalk_group($this->getDeviceArray(), $oid, $mib, $depth);
        }

        return $this->cache['group'][$depth][$oid];
    }

    /**
     * Check if an OID has been cached
     *
     * @param string $oid
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
        preg_match_all('/[A-Z][a-z]*/', $name, $segments);

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
            return [];
        }

        $oids = [];
        foreach ($sensors as $sensor) {
            $oids[$sensor['sensor_id']] = current($sensor['sensor_oids']);
        }

        $snmp_data = snmp_get_multi_oid($this->getDeviceArray(), $oids);

        $data = [];
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

    public function discoverMempools()
    {
        if ($this->hasYamlDiscovery('mempools')) {
            return $this->discoverYamlMempools();
        }

        $mempools = $this->discoverHrMempools();
        if ($mempools->isNotEmpty()) {
            return $mempools;
        }

        return $this->discoverUcdMempools();
    }

    public function getDiscovery($module = null)
    {
        if (! array_key_exists('dynamic_discovery', $this->device)) {
            $file = base_path('/includes/definitions/discovery/' . $this->getName() . '.yaml');
            if (file_exists($file)) {
                $this->device['dynamic_discovery'] = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($file));
            }
        }

        if ($module) {
            return $this->device['dynamic_discovery']['modules'][$module] ?? [];
        }

        return $this->device['dynamic_discovery'] ?? [];
    }

    public function hasYamlDiscovery(string $module = null)
    {
        return $module ? isset($this->getDiscovery()['modules'][$module]) : ! empty($this->getDiscovery());
    }
}
