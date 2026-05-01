<?php

/**
 * PortPopupController.php
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Models\Port;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PortPopupController
{
    public function __invoke(Request $request, Port $port)
    {
        if (! LibrenmsConfig::get('web_mouseover', true)) {
            return response('Disabled');
        }

        // Check access permissions
        Gate::authorize('view', $port);

        $graph_type = $request->string('type', 'port_bits');
        $graphs = [
            [
                'port' => $port,
                'type' => $graph_type,
                'title' => $request->string('title', Str::title(str_replace('_', ' ', $graph_type))),
                'graphs' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']],
            ],
        ];

        return view('port.popup', [
            'port' => $port,
            'device' => $port->device,
            'label' => $port->getLabel(),
            'description' => $port->getDescription(),
            'href' => \LibreNMS\Util\Url::portUrl($port),
            'graphs' => $graphs,
        ]);
    }
}
