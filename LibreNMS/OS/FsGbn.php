<?php

/**
 * Fs.php
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
 * @copyright  2019 PipoCanaja
 * @author     PipoCanaja <pipocanaja@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Processor;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class FsGbn extends OS implements ProcessorDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return Collection<Processor>
     */
    public function discoverProcessors(): Collection
    {
        $response = \SnmpQuery::get([
            'GBNPlatformOAM-MIB::cpuDescription.0',
            'GBNPlatformOAM-MIB::cpuIdle.0',
        ]);

        $idle = $response->value('GBNPlatformOAM-MIB::cpuIdle');
        if (is_numeric($idle)) {
            $description = $response->value('GBNPlatformOAM-MIB::cpuDescription');

            return collect([new Processor([
                'processor_type' => $this->getName(),
                'processor_oid' => '.1.3.6.1.4.1.13464.1.2.1.1.2.11.0',
                'processor_index' => '0',
                'processor_descr' => $description,
                'processor_precision' => -1,
                'processor_usage' => 100 - $idle,
            ])]);
        }

        return new Collection;
    }
}
