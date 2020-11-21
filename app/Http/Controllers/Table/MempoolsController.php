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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\Device;
use App\Models\Mempool;
use LibreNMS\Config;
use LibreNMS\Util\Colors;
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
            return Device::query()->has('mempools')->with('mempools');
        }

        $query = Mempool::query()->with('device');

        // join devices table to sort by hostname or search
        if (array_key_exists('hostname', $request->get('sort', $this->default_sort)) || $request->get('searchPhrase')) {
            $query->join('devices', 'mempools.device_id', 'devices.device_id')
                ->select('mempools.*');
        }

        return $query;
    }

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

        return Url::overlibLink($this->graphLink($graph), Url::lazyGraphTag($graph), Url::graphTag(['height' => 150, 'width' => 400] + $graph));
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
        $free = $this->formatNumber($mempool->mempool_free, $is_percent);
        $used = $this->formatNumber($mempool->mempool_used, $is_percent);
        $total = $this->formatNumber($mempool->mempool_total, $is_percent);

        $background = Colors::percentage($mempool->mempool_perc, $mempool->mempool_perc_warn);
        $percent = Html::percentageBar(400, 20, $mempool->mempool_perc, "$used / $total", 'ffffff', $background['left'], $free, 'ffffff', $background['right']);

        return Url::overlibLink($this->graphLink($graph), $percent, Url::graphTag($graph));
    }

    private function formatNumber($number, $is_percent)
    {
        return $is_percent ? $number : Number::formatBi($number);
    }

    private function graphLink(array $graph)
    {
        return url('graphs/id=' . $graph['id'] . '/type=' . $graph['type'] . '/from=' . $graph['from']);
    }
}
