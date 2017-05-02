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

use LibreNMS\Device\Discovery\Sensors\WirelessSensorDiscovery;
use LibreNMS\Device\Discovery\Sensors\WirelessSensorPolling;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\OS\Generic;

class OS
{
    private $device; // annoying use of references to make sure this is in sync with global $device variable

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

        if (!isset($this->$oid)) {
            $data = snmp_cache_oid($oid, $this->getDevice(), array(), $mib);
            $this->$oid = array_map('current', $data);
        }

        return $this->$oid;
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

    /**
     * Poll a channel based OID, but return data in MHz
     *
     * @param $sensors
     * @return array
     */
    protected function pollWirelessChannelAsFrequency($sensors)
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
            $data[$id] = WirelessSensor::channelToFrequency($snmp_data[$oid]);
        }

        return $data;
    }
}
