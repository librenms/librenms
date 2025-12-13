<?php

/**
 * SensorController.php
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SensorController
{
    public function index(Request $request, string $metric = '', string $legacyview = ''): View
    {
        $metric = str_replace('metric=', '', $metric);
        $view = str_replace('view=', '', $legacyview) ?: $request->get('view', 'detail');
        $status = $request->get('status', 'all');

        $metrics = $this->getMetrics($request);
        $metric = $metric ?: array_key_first($metrics);

        if (! array_key_exists($metric, $metrics)) {
            abort(404);
        }

        $views = [
            'graphs' => ['text' => __('Graphs'), 'link' => $request->fullUrlWithQuery(['view' => 'graphs'])],
            'detail' => ['text' => __('No Graphs'), 'link' => $request->fullUrlWithoutQuery('view')],
        ];

        $status_bar = [
            'all' => [ 'text' => __('All'), 'link' => $request->fullUrlWithQuery(['status' => 'all'])],
            'alert' => [ 'text' => __('Alert') , 'link' => $request->fullUrlWithQuery(['status' => 'alert'])],
            'error' => [ 'text' => __('Error') , 'link' => $request->fullUrlWithQuery(['status' => 'error'])],
            'warning' => [ 'text' => __('Warning') , 'link' => $request->fullUrlWithQuery(['status' => 'warning'])],
        ];

        if($metric == 'all') {
            unset($status_bar['all']);
            if($status == 'all') $status = 'alert';
        }

        if(in_array($metric, ['all', 'state'])) {
            $status_bar['unknown'] = [ 'text' => __('Unknown') , 'link' => $request->fullUrlWithQuery(['status' => 'unknown'])];
        }

        if(in_array($metric ,['mempool', 'processor', 'storage'])) {
            unset($status_bar['alert']);
            unset($status_bar['error']);
            unset($status_bar['unknown']);
        }

        if(!array_key_exists($status, $status_bar )) $status = array_key_first($status_bar);

        $title = 'Health :: ' . match ($metric) {
            'dbm' => 'dBm',
            'snr' => 'SNR',
            default => ucfirst((string) $metric),
        };

        $blade_view = match ($metric) {
            'mempool' => 'sensor.mempool',
            'processor' => 'sensor.processor',
            'storage' => 'sensor.storage',
            'diskio' => 'sensor.diskio',
            'printer-supply' => 'sensor.printer-supply',
            default => 'sensor.index',
        };

        return view($blade_view, [
            'title' => $title,
            'metrics' => $metrics,
            'metric' => $metric,
            'views' => $views,
            'view' => $view,
            'status_bar' => $status_bar,
            'status' => $status,
        ]);
    }

    /**
     * @return array<array<string, (array|string)>>
     */
    private function getMetrics(Request $request): array
    {
        $metrics = [
            'all' => [
                'text' => __('All'),
                'link' => route('sensor.index', $request->all() + ['metric' => 'all']),
                'icon' => 'fa-bell',
            ],
            'mempool' => [
                'text' => __('Memory'),
                'link' => route('sensor.index', $request->all() + ['metric' => 'mempool']),
                'icon' => 'fa-memory',
            ],
            'processor' => [
                'text' => __('Processor'),
                'link' => route('sensor.index', $request->all() + ['metric' => 'processor']),
                'icon' => 'fa-microchip',
            ],
            'storage' => [
                'text' => __('Storage'),
                'link' => route('sensor.index', $request->all() + ['metric' => 'storage']),
                'icon' => 'fa-hdd',
            ],
            'diskio' => [
                'text' => __('Disk I/O'),
                'link' => route('sensor.index', $request->all() + ['metric' => 'diskio']),
                'icon' => 'fa-hdd',
            ],
            'printer-supply' => [
                'text' => __('sensors.printer-supply.long'),
                'link' => route('sensor.index', $request->all() + ['metric' => 'printer-supply']),
                'icon' => 'fa-print',
            ],
        ];

        $sensors_menu = \LibreNMS\Util\ObjectCache::sensors();
        foreach ($sensors_menu as $types) {
            foreach ($types as $entry) {
                $class = $entry['class'];
                $metrics[$class] = [
                    'text' => trans('sensors.' . $class . '.short'),
                    'link' => route('sensor.index', $request->all() + ['metric' => $class]),
                    'icon' => 'fa-' . $entry['icon'],
                ];
            }
        }

        return $metrics;
    }
}
