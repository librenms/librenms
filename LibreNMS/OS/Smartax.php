<?php
/**
 * Smartax.php
 *
 * SmartAX OS
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
 * @copyright  2018 TheGreatDoc
 * @author     TheGreatDoc <doctoruve@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class Smartax extends OS implements ProcessorDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $data = snmpwalk_group($this->getDevice(), 'enterprises.2011.2.6.7.1.1.2.1.5.0', 'SNMPv2-SMI');
        //$proc_descr = snmpwalk_group($this->getDevice(), '1.3.6.1.4.1.2011.2.6.7.1.1.2.1.7.0');

        $processors = array();
        foreach ($data as $index => $entry) {
            if ($entry != -1) {
                $index = substr(strrchr($index, '.'), 1);
                $proc_desc = snmp_get($this->getDevice(), 'enterprises.2011.2.6.7.1.1.2.1.7.0.' . $index, '-Oqv');
                $processors[] = Processor::discover(
                    "smartax",
                    $this->getDeviceId(),
                    "enterprises.2011.2.6.7.1.1.2.1.5.0.$index",
                    $index,
                    "$proc_desc processor",
                    1,
                    $entry
                );
            }
        }


        return $processors;
    }
}
