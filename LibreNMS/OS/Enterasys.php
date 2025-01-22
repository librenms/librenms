<?php
/*
 * Enterasys.php
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Mempool;
use App\Models\Storage;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use SnmpQuery;

class Enterasys extends \LibreNMS\OS implements MempoolsDiscovery
{
    public function discoverMempools()
    {
        $mempools = new Collection();
        $mem = snmpwalk_group($this->getDeviceArray(), 'etsysResourceStorageTable', 'ENTERASYS-RESOURCE-UTILIZATION-MIB', 3);

        foreach ($mem as $index => $mem_data) {
            foreach ($mem_data['ram'] ?? [] as $mem_id => $ram) {
                $descr = $ram['etsysResourceStorageDescr'];
                if ($index > 1000) {
                    $descr = 'Slot #' . substr($index, -1) . " $descr";
                }

                $mempools->push((new Mempool([
                    'mempool_index' => $index,
                    'mempool_type' => 'enterasys',
                    'mempool_class' => 'system',
                    'mempool_descr' => $descr,
                    'mempool_precision' => 1024,
                    'mempool_free_oid' => ".1.3.6.1.4.1.5624.1.2.49.1.3.1.1.5.$index.2.$mem_id",
                    'mempool_perc_warn' => 90,
                ]))->fillUsage(null, $ram['etsysResourceStorageSize'] ?? null, $ram['etsysResourceStorageAvailable'] ?? null));
            }
        }

        return $mempools;
    }

    public function discoverStorage(): Collection
    {
        $storage = new Collection;

        $free = SnmpQuery::get('ENTERASYS-RESOURCE-UTILIZATION-MIB::etsysResourceStorageAvailable.3.flash.0')->value();
        $total = SnmpQuery::get('ENTERASYS-RESOURCE-UTILIZATION-MIB::etsysResourceStorageSize.3.flash.0')->value();
        if (is_numeric($free) && is_numeric($total)) {
            $storage->push((new Storage([
                'type' => 'enterasys',
                'storage_type' => 'Flash',
                'storage_descr' => 'Internal Flash Storage',
                'storage_units' => 1024,
                'storage_index' => 0,
                'storage_free_oid' => '.1.3.6.1.4.1.5624.1.2.49.1.3.1.1.5.3.3.0',
            ]))->fillUsage(total: $total, free: $free));
        }

        return $storage;
    }
}
