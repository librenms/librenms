<?php
/*
 * MempoolObserver.php
 *
 * Observe Mempool changes during discovery
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

namespace App\Observers;

use App\Models\Mempool;
use Log;
use Rrd;

class MempoolObserver
{
    public function updating(Mempool $mempool): void
    {
        // prevent update of mempool_perc_warn
        $mempool->mempool_perc_warn = $mempool->getOriginal('mempool_perc_warn');
    }

    public function updated(Mempool $mempool): void
    {
        if ($mempool->isDirty('mempool_class')) {
            Log::debug("Mempool class changed $mempool->mempool_descr ($mempool->mempool_id)");
            $device = [
                'device_id' => $mempool->device->device_id,
                'hostname' => $mempool->device->hostname,
            ];
            Rrd::renameFile($device, ['mempool', $mempool->mempool_type, $mempool->getOriginal('mempool_class'), $mempool->mempool_index], ['mempool', $mempool->mempool_type, $mempool->mempool_class, $mempool->mempool_index]);
        }
    }
}
