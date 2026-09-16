<?php

/**
 * ServerStatsController.php
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
 * @copyright  2018-2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Widgets;

use App\Models\Device;
use App\Models\Mempool;
use App\Models\Storage;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Util\Number;

class ServerStatsController extends WidgetController
{
    protected string $name = 'server-stats';
    protected $defaults = [
        'title' => null,
        'columnsize' => 3,
        'device' => null,
        'gauges' => [],
        'cpu' => 0,
        'mempools' => [],
        'disks' => [],
    ];

    public function getTitle(): string
    {
        $settings = $this->getSettings();
        if ($settings['title']) {
            return $settings['title'];
        }

        $device = Device::hasAccess(request()->user())->find($settings['device']);
        if ($device) {
            return $device->displayName() . ' Stats';
        }

        return parent::getTitle();
    }

    public function getView(Request $request): \Illuminate\View\View|string
    {
        $data = $this->getSettings();

        if (is_null($data['device'])) {
            return $this->getSettingsView($request);
        }

        $device = Device::hasAccess($request->user())->find($data['device']);
        if ($device) {
            $hiddenGauges = (array) $data['gauges'];

            $data['cpu'] = round((float) $device->processors()->avg('processor_usage'), 1);
            $data['showCpu'] = ! in_array('cpu', $hiddenGauges);

            $data['mempools'] = $device->mempools()
                ->get(['mempool_descr', 'mempool_used', 'mempool_total'])
                ->filter(fn (Mempool $m) => ! in_array("mempool:$m->mempool_descr", $hiddenGauges))
                ->map(fn (Mempool $m) => $this->formatUsage($m->mempool_descr ?: 'Memory', (float) $m->mempool_used, (float) $m->mempool_total));

            $data['disks'] = $device->storage()
                ->get(['storage_descr', 'storage_used', 'storage_size'])
                ->filter(fn (Storage $d) => ! in_array("storage:$d->storage_descr", $hiddenGauges))
                ->map(fn (Storage $d) => $this->formatUsage($d->storage_descr ?: 'Storage', (float) $d->storage_used, (float) $d->storage_size));

            $numCols = (int) ($data['columnsize'] ?? 3);
            $totalGauges = (int) $data['showCpu'] + count($data['mempools']) + count($data['disks']);

            $data['gridCols'] = match ($numCols) {
                1 => 'tw:grid-cols-1',
                2 => 'tw:grid-cols-2',
                4 => 'tw:grid-cols-4',
                5 => 'tw:grid-cols-5',
                6 => 'tw:grid-cols-6',
                12 => 'tw:grid-cols-12',
                default => 'tw:grid-cols-3',
            };
            $data['gridRows'] = max(1, (int) ceil($totalGauges / max(1, $numCols)));
        }

        return view('widgets.server-stats', $data);
    }

    public function getSettingsView(Request $request): View
    {
        $settings = $this->getSettings(true);
        $settings['device'] = Device::hasAccess($request->user())->find($settings['device']) ?: null;
        $settings['gaugeOptions'] = collect();

        if ($settings['device']) {
            $settings['gaugeOptions']->put('cpu', __('widgets.server-stats.cpu_usage'));
            foreach ($settings['device']->mempools()->pluck('mempool_descr') as $description) {
                $settings['gaugeOptions']->put("mempool:$description", $description ?: __('Memory'));
            }
            foreach ($settings['device']->storage()->pluck('storage_descr') as $description) {
                $settings['gaugeOptions']->put("storage:$description", $description ?: __('Storage'));
            }
        }

        return view('widgets.settings.server-stats', $settings);
    }

    public function getSettings($settingsView = false): array
    {
        $settings = parent::getSettings($settingsView);
        $settings['columns'] = (int) ($settings['columnsize'] ?? 3);

        return $settings;
    }

    /**
     * @return array{descr: string, used: float, total: float, unit: string}
     */
    private function formatUsage(string $descr, float $used, float $total): array
    {
        $formatted = Number::formatSi(max($total, 1), 2, 0, 'B');
        $parts = explode(' ', trim($formatted));
        $unit = array_last($parts) ?: 'B';

        $factor = match ($unit) {
            'kB' => 1000,
            'MB' => 1000 ** 2,
            'GB' => 1000 ** 3,
            'TB' => 1000 ** 4,
            'PB' => 1000 ** 5,
            'EB' => 1000 ** 6,
            default => 1,
        };

        return [
            'descr' => $descr,
            'used' => round($used / $factor, 2),
            'total' => round($total / $factor, 2),
            'unit' => $unit,
        ];
    }
}
