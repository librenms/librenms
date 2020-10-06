<?php
/*
 * Mempools.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Models\Mempool;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\MempoolsPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\ModuleModelObserver;

class Mempools implements Module
{
    use SyncsModels;

    public function discover(OS $os)
    {
        if ($os instanceof MempoolsDiscovery) {
            $mempools = $os->discoverMempools();
            ModuleModelObserver::observe('\App\Models\Mempool');
            $this->syncModels($os->getDevice(), 'mempools', $mempools);
        }
    }

    public function poll(OS $os)
    {
        if ($os instanceof MempoolsPolling) {
            $mempools = $os->pollMempools();

            $mempools->each(function (Mempool $mempool) use ($os) {
                echo "Mempool $mempool->mempool_descr: $mempool->mempool_perc%\n";

                $rrd_name = ['mempool', $mempool->mempool_type, $mempool->mempool_index];
                $rrd_def = RrdDefinition::make()
                    ->addDataset('used', 'GAUGE', 0)
                    ->addDataset('free', 'GAUGE', 0);
                $fields = [
                    'used' => $mempool->mempool_used,
                    'free' => $mempool->mempool_free,
                ];

                $tags = compact('mempool_type', 'mempool_index', 'rrd_name', 'rrd_def');
                data_update($os->getDeviceArray(), 'mempool', $tags, $fields);

                $mempool->save();
            });
        }
    }

    public function cleanup(OS $os)
    {
        // TODO: Implement cleanup() method.
    }
}
