<?php

/**
 * Dnos.php
 *
 * Dell Network OS
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
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Processor;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class Dnos extends OS implements ProcessorDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return Collection<Processor>
     */
    public function discoverProcessors(): Collection
    {
        $device = $this->getDeviceArray();
        $processors = new Collection;

        if (Str::startsWith($device['sysObjectID'], '.1.3.6.1.4.1.6027.1.3')) {
            d_echo('Dell S Series Chassis');
            $this->findProcessors(
                $processors,
                'F10-S-SERIES-CHASSIS-MIB::chStackUnitCpuUtil5Sec',
                '.1.3.6.1.4.1.6027.3.10.1.2.9.1.2',
                'Stack Unit'
            );
        } elseif (Str::startsWith($device['sysObjectID'], '.1.3.6.1.4.1.6027.1.2')) {
            d_echo('Dell C Series Chassis');
            $this->findProcessors(
                $processors,
                'F10-C-SERIES-CHASSIS-MIB::chRpmCpuUtil5Sec',
                '.1.3.6.1.4.1.6027.3.8.1.3.7.1.3',
                'Route Process Module',
                $this->getName() . '-rpm'
            );
            $this->findProcessors(
                $processors,
                'F10-C-SERIES-CHASSIS-MIB::chLineCardCpuUtil5Sec',
                '.1.3.6.1.4.1.6027.3.8.1.5.1.1.1',
                'Line Card'
            );
        } elseif (Str::startsWith($device['sysObjectID'], '.1.3.6.1.4.1.6027.1.4')) {
            d_echo('Dell M Series Chassis');
            $this->findProcessors(
                $processors,
                'F10-M-SERIES-CHASSIS-MIB::chStackUnitCpuUtil5Sec',
                '.1.3.6.1.4.1.6027.3.19.1.2.8.1.2',
                'Stack Unit'
            );
        } elseif (Str::startsWith($device['sysObjectID'], '.1.3.6.1.4.1.6027.1.5')) {
            d_echo('Dell Z Series Chassis');
            $this->findProcessors(
                $processors,
                'F10-Z-SERIES-CHASSIS-MIB::chSysCpuUtil5Sec',
                '.1.3.6.1.4.1.6027.3.25.1.2.3.1.1',
                'Chassis'
            );
        }

        return $processors;
    }

    /**
     * Find processors and append them to the $processors array
     *
     * @param  Collection<Processor>  $processors
     * @param  string  $oid  Textual OID with MIB
     * @param  string  $num_oid  Numerical OID
     * @param  string  $name  Name prefix to display to user
     * @param  string  $type  custom type (if there are multiple in one chassis)
     */
    private function findProcessors($processors, $oid, $num_oid, $name, $type = null): void
    {
        $data = \SnmpQuery::walk($oid)->pluck();
        foreach ($data as $index => $usage) {
            if (is_numeric($usage)) {
                $processors->push(new Processor([
                    'processor_type' => $type ?: $this->getName(),
                    'processor_oid' => "$num_oid.$index",
                    'processor_index' => $index,
                    'processor_descr' => "$name $index CPU",
                    'processor_precision' => 1,
                    'entPhysicalIndex' => 0,
                    'hrDeviceIndex' => null,
                    'processor_perc_warn' => null,
                    'processor_usage' => $usage,
                ]));
            }
        }
    }
}
