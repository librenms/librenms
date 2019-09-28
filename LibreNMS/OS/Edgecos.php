<?php
/**
 * Edgecos.php
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

namespace LibreNMS\OS;

use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class Edgecos extends OS implements ProcessorDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $device = $this->getDevice();

        if (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.10.1.24.')) { //ECS4510
            $oid = '.1.3.6.1.4.1.259.10.1.24.1.39.2.1.0';
        } elseif (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.10.1.22.')) { //ECS3528
            $oid = '.1.3.6.1.4.1.259.10.1.22.1.39.2.1.0';
        } elseif (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.10.1.39.')) { //ECS4110
            $oid = '.1.3.6.1.4.1.259.10.1.39.1.39.2.1.0';
        } elseif (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.10.1.45.')) { //ECS4120
            $oid = '.1.3.6.1.4.1.259.10.1.45.1.39.2.1.0';
        } elseif (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.10.1.42.')) { //ECS4210
            $oid = '.1.3.6.1.4.1.259.10.1.42.101.1.39.2.1.0';
        } elseif (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.10.1.27.')) { //ECS3510
            $oid = '.1.3.6.1.4.1.259.10.1.27.1.39.2.1.0';
        } elseif (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.8.1.11.')) { //ES3510MA
            $oid = '.1.3.6.1.4.1.259.8.1.11.1.39.2.1.0';
        };

        if (isset($oid)) {
            return array(
                Processor::discover(
                    $this->getName(),
                    $this->getDeviceId(),
                    $oid,
                    0
                )
            );
        }

        return array();
    }
}
