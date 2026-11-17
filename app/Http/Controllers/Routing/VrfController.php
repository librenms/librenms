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
 *
 * @copyright  2026 LibreNMS
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Routing;

use App\Http\Controllers\Controller;
use App\Models\Vrf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VrfController extends Controller
{
    public function __invoke(Request $request): View
    {
        $request->validate([
            'view' => 'nullable|in:basic,graphs',
            'graph' => 'nullable|in:bits,pkts,upkts,nupkts,errors',
            'vrf' => 'nullable|string',
        ]);

        $view = $request->query('view', 'basic');
        $graph = $request->query('graph', 'bits');
        $name = $request->query('vrf');

        $link = fn (array $params) => route('routing.vrf', array_filter(['vrf' => $name] + $params));
        $options = [
            'basic' => ['text' => __('Basic'), 'link' => $link([])],
            'graphs' => ['text' => __('Graphs'), 'link' => $link(['view' => 'graphs', 'graph' => $graph])],
        ];

        if ($view === 'graphs') {
            $graphs = ['bits' => __('Bits'), 'pkts' => __('Packets'), 'upkts' => __('Ucast'), 'nupkts' => __('NUcast'), 'errors' => __('Errors')];
            foreach ($graphs as $type => $text) {
                $options[$type] = ['text' => $text, 'link' => $link(['view' => 'graphs', 'graph' => $type])];
            }
        }

        $vrfs = Vrf::hasAccess($request->user())
            ->when($name, fn ($query) => $query->where('vrf_name', $name))
            ->with(['device', 'ports' => fn ($query) => $query->orderBy('ifDescr')->with('device')])
            ->orderBy('vrf_name')
            ->orderBy('mplsVpnVrfRouteDistinguisher')
            ->get()
            ->groupBy(fn (Vrf $vrf) => $vrf->vrf_name . '|' . $vrf->mplsVpnVrfRouteDistinguisher);

        return view('routing.vrf', [
            'vrfs' => $vrfs,
            'view' => $view,
            'graph' => $view === 'graphs' ? $graph : null,
            'name' => $name,
            'options' => $options,
            'selected_option' => $view === 'graphs' ? $graph : $view,
        ]);
    }
}
