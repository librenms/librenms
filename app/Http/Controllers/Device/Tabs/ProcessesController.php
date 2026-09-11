<?php

/**
 * ProcessesController.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Interfaces\UI\DeviceTab;

class ProcessesController implements DeviceTab
{
    private const COLUMNS = [
        'pid' => ['label' => 'PID'],
        'vsz' => ['label' => 'VSZ', 'title' => 'Virtual Memory'],
        'rss' => ['label' => 'RSS', 'title' => 'Resident Memory'],
        'cputime' => ['label' => 'cputime'],
        'user' => ['label' => 'user'],
        'command' => ['label' => 'command'],
    ];

    public function visible(Device $device): bool
    {
        return $device->processes()->exists();
    }

    public function slug(): string
    {
        return 'processes';
    }

    public function icon(): string
    {
        return 'fa-microchip';
    }

    public function name(): string
    {
        return __('Processes');
    }

    public function data(Device $device, Request $request): array
    {
        $validated = $request->validate([
            'order' => ['nullable', 'string', Rule::in(array_keys(self::COLUMNS))],
            'by' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);

        $order = $validated['order'] ?? 'pid';
        $by = $validated['by'] ?? 'asc';

        $processes = $device->processes()->orderBy($order, $by)->get();

        $columns = [];
        foreach (self::COLUMNS as $colKey => $colMeta) {
            $isSorted = $order === $colKey;
            $nextBy = ($isSorted && $by === 'asc') ? 'desc' : 'asc';
            $columns[$colKey] = [
                'label' => $colMeta['label'],
                'title' => isset($colMeta['title']) ? __($colMeta['title']) : null,
                'icon' => $isSorted ? ($by === 'asc' ? 'fa fa-chevron-up' : 'fa fa-chevron-down') : '',
                'url' => route('device', [
                    'device' => $device,
                    'tab' => 'processes',
                    'order' => $colKey,
                    'by' => $nextBy,
                ]),
            ];
        }

        return [
            'order' => $order,
            'by' => $by,
            'columns' => $columns,
            'processes' => $processes,
        ];
    }
}
