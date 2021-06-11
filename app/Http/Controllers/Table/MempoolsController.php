<?php
/*
 * MempoolsController.php
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

namespace App\Http\Controllers\Table;

use App\Models\Device;
use App\Models\Mempool;
use Illuminate\Support\Arr;
use LibreNMS\Config;
use LibreNMS\Util\Html;
use LibreNMS\Util\Number;
use LibreNMS\Util\Url;

class MempoolsController extends TableController
{
    protected function searchFields($request)
    {
        return ['hostname', 'mempool_descr'];
    }

    protected function sortFields($request)
    {
        return ['mempool_descr', 'mempool_perc', 'mempool_used', 'hostname'];
    }

    /**
     * {@inheritdoc}
     */
    protected function baseQuery($request)
    {
        if ($request->get('view') == 'graphs') {
            return Device::hasAccess($request->user())->has('mempools')->with('mempools');
        }

        $query = Mempool::hasAccess($request->user())->with('device');

        // join devices table to sort by hostname or search
        if (array_key_exists('hostname', $request->get('sort', $this->default_sort)) || $request->get('searchPhrase')) {
            $query->join('devices', 'mempools.device_id', 'devices.device_id')
                ->select('mempools.*');
        }

        return $query;
    }

    /**
     * @param Device|Mempool $mempool
     */
    public function formatItem($mempool)
    {
        if ($mempool instanceof Device) {
            $device = $mempool;
            $graphs = \LibreNMS\Util\Html::graphRow([
                'device' => $device->device_id,
                'type' => 'device_mempool',
                'height' => 100,
                'width' => 216,
            ]);

            return [
                'hostname'      => Url::deviceLink($device),
                'mempool_descr' => $graphs[0],
                'graph'         => $graphs[1],
                'mempool_used'  => $graphs[2],
                'mempool_perc'  => $graphs[3],
            ];
        }

        /** @var Mempool $mempool */
        return [
            'hostname'      => Url::deviceLink($mempool->device),
            'mempool_descr' => $mempool->mempool_descr,
            'graph'         => $this->miniGraph($mempool),
            'mempool_used'  => $this->barLink($mempool),
            'mempool_perc'  => $mempool->mempool_perc . '%',
        ];
    }

    private function miniGraph(Mempool $mempool)
    {
        $graph = [
            'type' => 'mempool_usage',
            'id' => $mempool->mempool_id,
            'from' => Config::get('time.day'),
            'height' => 20,
            'width' => 80,
        ];

        $link = Url::generate(['page' => 'graphs'], Arr::only($graph, ['id', 'type', 'from']));

        return Url::overlibLink($link, Url::graphTag($graph), Url::graphTag(['height' => 150, 'width' => 400] + $graph));
    }

    private function barLink(Mempool $mempool)
    {
        $graph = [
            'type' => 'mempool_usage',
            'id' => $mempool->mempool_id,
            'from' => Config::get('time.day'),
            'height' => 150,
            'width' => 400,
        ];

        $is_percent = $mempool->mempool_total == 100;
        $free = $is_percent ? $mempool->mempool_free : Number::formatBi($mempool->mempool_free);
        $used = $is_percent ? $mempool->mempool_used : Number::formatBi($mempool->mempool_used);
        $total = $is_percent ? $mempool->mempool_total : Number::formatBi($mempool->mempool_total);

        $percent = Html::percentageBar(400, 20, $mempool->mempool_perc, "$used / $total", $free, $mempool->mempool_perc_warn);
        $link = Url::generate(['page' => 'graphs'], Arr::only($graph, ['id', 'type', 'from']));

        return Url::overlibLink($link, $percent, Url::graphTag($graph));
    }
}
