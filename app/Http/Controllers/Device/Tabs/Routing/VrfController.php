<?php

/**
 * VrfController.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2026 LibreNMS
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs\Routing;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class VrfController extends Controller
{
    public function __invoke(Device $device, Request $request): View
    {
        $this->authorize('view', $device);
        abort_if(Gate::none(['routing.view', 'routing.viewAll']), 403);

        Validator::validate($request->all(), [
            'view' => 'nullable|in:basic,graphs',
            'graph' => 'nullable|in:bits,pkts,upkts,nupkts,errors',
        ]);

        $view = $request->query('view', 'basic');
        $graph = $request->query('graph', 'bits');

        $vrfOptions = [
            'basic' => [
                'text' => __('Basic'),
                'link' => route('device.routing.vrf', ['device' => $device, 'view' => 'basic']),
            ],
            'graphs' => [
                'text' => __('Graphs'),
                'link' => route('device.routing.vrf', ['device' => $device, 'view' => 'graphs', 'graph' => $graph]),
            ],
        ];

        if ($view === 'graphs') {
            $vrfOptions['bits'] = [
                'text' => __('Bits'),
                'link' => route('device.routing.vrf', ['device' => $device, 'view' => 'graphs', 'graph' => 'bits']),
            ];
            $vrfOptions['pkts'] = [
                'text' => __('Packets'),
                'link' => route('device.routing.vrf', ['device' => $device, 'view' => 'graphs', 'graph' => 'pkts']),
            ];
            $vrfOptions['upkts'] = [
                'text' => __('Ucast'),
                'link' => route('device.routing.vrf', ['device' => $device, 'view' => 'graphs', 'graph' => 'upkts']),
            ];
            $vrfOptions['nupkts'] = [
                'text' => __('NUcast'),
                'link' => route('device.routing.vrf', ['device' => $device, 'view' => 'graphs', 'graph' => 'nupkts']),
            ];
            $vrfOptions['errors'] = [
                'text' => __('Errors'),
                'link' => route('device.routing.vrf', ['device' => $device, 'view' => 'graphs', 'graph' => 'errors']),
            ];
        }

        $selectedOption = $view === 'graphs' ? $graph : $view;

        $vrfs = $device->vrfs()
            ->with(['ports' => function ($query): void {
                $query->orderBy('ifDescr');
            }])
            ->get();

        return view('device.tabs.routing.vrf', [
            'device' => $device,
            'view' => $view,
            'graph' => $graph,
            'selected_option' => $selectedOption,
            'vrf_options' => $vrfOptions,
            'vrfs' => $vrfs,
        ]);
    }
}
