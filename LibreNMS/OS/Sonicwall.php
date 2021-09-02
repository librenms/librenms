<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2018 LibreNMS
 * @author     LibreNMS Contributors
*/

namespace LibreNMS\OS;

use Illuminate\Support\Str;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class Sonicwall extends OS implements OSPolling, ProcessorDiscovery
{
    public function pollOS()
    {
        $data = snmp_get_multi($this->getDeviceArray(), [
            'sonicCurrentConnCacheEntries.0',
            'sonicMaxConnCacheEntries.0',
        ], '-OQUs', 'SONICWALL-FIREWALL-IP-STATISTICS-MIB');

        if (is_numeric($data)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('activesessions', 'GAUGE', 0)
                ->addDataset('maxsessions', 'GAUGE', 0);
            $fields = [
                'activesessions' => $data[0]['sonicCurrentConnCacheEntries'],
                'maxsessions' => $data[0]['sonicMaxConnCacheEntries'],
            ];
            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'sonicwall_sessions', $tags, $fields);
            $this->enableGraph('sonicwall_sessions');
        }
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        if (Str::startsWith($this->getDeviceArray()['sysObjectID'], '.1.3.6.1.4.1.8741.1')) {
            return [
                Processor::discover(
                    'sonicwall',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.8741.1.3.1.3.0',  // SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicCurrentCPUUtil.0
                    0,
                    'CPU',
                    1
                ),
            ];
        } else {
            return [
                Processor::discover(
                    'sonicwall',
                    $this->getDeviceId(),
                    $this->getDeviceArray()['sysObjectID'] . '.2.1.3.0',  // different OID for each model
                    0,
                    'CPU',
                    1
                ),
            ];
        }
    }
}
