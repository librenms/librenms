<?php

/**
 * IsisController.php
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
use App\Models\IsisAdjacency;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IsisController extends Controller
{
    public function __invoke(Request $request): View
    {
        $request->validate([
            'state' => 'nullable|in:all,up,down',
        ]);

        $state = $request->query('state', 'all');

        $adjacencies = IsisAdjacency::hasAccess($request->user())
            ->when($state !== 'all', fn ($q) => $q->where('isisISAdjState', $state))
            ->with(['device', 'port'])
            ->orderBy('device_id')
            ->get();

        return view('routing.isis', [
            'adjacencies' => $adjacencies,
            'state' => $state,
            'state_options' => [
                'all' => ['text' => __('All'), 'link' => route('routing.isis')],
                'up' => ['text' => __('Up'), 'link' => route('routing.isis', ['state' => 'up'])],
                'down' => ['text' => __('Down'), 'link' => route('routing.isis', ['state' => 'down'])],
            ],
        ]);
    }
}
