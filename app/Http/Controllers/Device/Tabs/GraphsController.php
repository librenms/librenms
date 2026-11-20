<?php

/**
 * GraphsController.php
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

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Interfaces\UI\DeviceTab;

class GraphsController extends Controller implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return true;
    }

    public function slug(): string
    {
        return 'graphs';
    }

    public function icon(): string
    {
        return 'fa-area-chart';
    }

    public function name(): string
    {
        return __('Graphs');
    }

    /**
     * @return array<string, mixed>
     */
    public function data(Device $device, Request $request): array
    {
        return [];
    }

    public function index(Device $device, Request $request): View
    {
        $this->authorize('view', $device);

        $request->validate([
            'group' => 'nullable|string',
        ]);

        // graphs are grouped by the section of their graph type
        $groups = $device->graphs()->orderBy('graph')->pluck('graph')
            ->groupBy(fn ($graph) => strtolower((string) LibrenmsConfig::get("graph_types.device.$graph.section")))
            ->forget('');

        $group = $request->string('group')->value();
        $group = $groups->has($group) ? $group : $groups->keys()->first();

        return view('device.tabs.graphs', [
            'device' => $device,
            'group' => $group,
            'groups' => $groups->map(fn ($graphs, $group) => [
                'text' => $group == 'customoid' ? __('Custom OID') : ucwords($group),
                'link' => route('device.graphs', ['device' => $device->device_id, 'group' => $group]),
            ])->all(),
            'graphs' => $groups->get($group, collect())
                ->mapWithKeys(fn ($graph) => ["device_$graph" => LibrenmsConfig::get("graph_types.device.$graph.descr")])
                ->all(),
            'availability' => $group == 'availability' ? $device->availability()->orderBy('duration')->get() : collect(),
            'customoids' => $group == 'customoid' ? $device->customoids()->orderBy('customoid_descr')->get() : collect(),
        ]);
    }
}
