<?php

/**
 * MoxaEtherdevice.php
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
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class MoxaEtherdevice extends OS implements ProcessorDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return Collection<Processor>
     */
    public function discoverProcessors(): Collection
    {
        // Moxa people enjoy creating MIBs for each model!
        // .1.3.6.1.4.1.8691.7.116.1.54.0 = MOXA-IKS6726A-MIB::cpuLoading30s.0
        // .1.3.6.1.4.1.8691.7.69.1.54.0 = MOXA-EDSG508E-MIB::cpuLoading30s.0
        $oid = $this->getDevice()->sysObjectID . '.1.54.0';
        $value = SnmpQuery::get($oid)->value();

        return collect([
            new Processor([
                'processor_type' => $this->getName(),
                'processor_oid' => $oid,
                'processor_index' => 0,
                'processor_usage' => $value,
            ]),
        ]);
    }
}
