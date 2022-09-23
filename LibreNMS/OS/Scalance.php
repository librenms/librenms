<?php
/**
 * Scalance.php
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

use App\Models\Mempool;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class Scalance extends OS implements MempoolsDiscovery, ProcessorDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $oid = '.1.3.6.1.4.1.4329.20.1.1.1.1.79.3.1.13.0';

        return [
            Processor::discover(
                'scalance-cpu',
                $this->getDeviceId(),
                $oid,
                0,
                'Processor',
            ),
        ];
    }

    public function discoverMempools()
    {
        $perc_oid = '.1.3.6.1.4.1.4329.20.1.1.1.1.79.3.1.13.1';
        $warn_oid = '.1.3.6.1.4.1.4329.20.1.1.1.1.79.3.1.16.1';
        $mempool_data = snmp_get_multi_oid($this->getDeviceArray(), [$perc_oid, $warn_oid]);

        if ($mempool_data[$perc_oid] === false) {
            return collect();
        }

        return collect()->push((new Mempool([
            'mempool_index' => 0,
            'mempool_type' => 'scalance',
            'mempool_class' => 'system',
            'mempool_descr' => 'Memory',
            'mempool_perc_oid' => $perc_oid,
            'mempool_perc_warn' => $mempool_data[$warn_oid],
        ]))->fillUsage(null, null, null, $mempool_data[$perc_oid]));
    }
}
