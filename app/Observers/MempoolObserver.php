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

use Log;
use Rrd;

class MempoolObserver extends ModuleModelObserver
{
    /** @param \App\Models\Mempool $model  */
    public function updated($model)
    {
        parent::updated($model);

        if ($model->isDirty('mempool_class')) {
            Log::debug("Mempool class changed $model->mempool_descr ($model->mempool_id)");
            Rrd::renameFile($model->device->toArray(), ['mempool', $model->mempool_type, $model->getOriginal('mempool_class'), $model->mempool_index], ['mempool', $model->mempool_type, $model->mempool_class, $model->mempool_index]);
        }
    }
}
