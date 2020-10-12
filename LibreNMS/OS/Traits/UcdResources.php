<?php
/**
 * UcdProcessor.php
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
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use LibreNMS\Device\Processor;
use LibreNMS\RRD\RrdDefinition;

trait UcdResources
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        echo 'UCD Resources: ';

        return [
            Processor::discover(
                'ucd-old',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2021.11.11.0',
                0,
                'CPU',
                -1
            ),
        ];
    }

    public function pollMempools()
    {
        $snmpdata = snmp_get_multi($this->getDeviceArray(), [
            'memTotalSwap.0',
            'memAvailSwap.0',
            'memTotalReal.0',
            'memAvailReal.0',
            'memTotalFree.0',
            'memShared.0',
            'memBuffer.0',
            'memCached.0',
            'memSysAvail.0',
        ], '-OQUs', 'UCD-SNMP-MIB');

        if (! empty($snmpdata[0])) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('totalswap', 'GAUGE', 0)
                ->addDataset('availswap', 'GAUGE', 0)
                ->addDataset('totalreal', 'GAUGE', 0)
                ->addDataset('availreal', 'GAUGE', 0)
                ->addDataset('totalfree', 'GAUGE', 0)
                ->addDataset('shared', 'GAUGE', 0)
                ->addDataset('buffered', 'GAUGE', 0)
                ->addDataset('cached', 'GAUGE', 0)
                ->addDataset('available', 'GAUGE', 0);

            $fields = [
                'totalswap'    => $snmpdata[0]['memTotalSwap'],
                'availswap'    => $snmpdata[0]['memAvailSwap'],
                'totalreal'    => $snmpdata[0]['memTotalReal'],
                'availreal'    => $snmpdata[0]['memAvailReal'],
                'totalfree'    => $snmpdata[0]['memTotalFree'],
                'shared'       => $snmpdata[0]['memShared'],
                'buffered'     => $snmpdata[0]['memBuffer'],
                'cached'       => $snmpdata[0]['memCached'],
                'available'    => $snmpdata[0]['memSysAvail'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'ucd_mem', $tags, $fields);

            $this->enableGraph('ucd_memory');
        }
    }
}
