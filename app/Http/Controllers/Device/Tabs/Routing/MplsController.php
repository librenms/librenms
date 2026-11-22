<?php

/**
 * MplsController.php
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

namespace App\Http\Controllers\Device\Tabs\Routing;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\View\Components\Routing\MplsTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MplsController extends Controller
{
    public function __invoke(Device $device, Request $request): View
    {
        $this->authorize('view', $device);
        abort_if(Gate::none(['routing.view', 'routing.viewAll']), 403);

        $request->validate([
            'view' => 'nullable|in:' . implode(',', MplsTable::VIEWS),
        ]);

        $view = (string) $request->query('view', 'lsp');

        return view('device.tabs.routing.mpls', [
            'device' => $device,
            'view' => $view,
            'mpls_options' => MplsTable::options(fn (string $option) => route('device.routing.mpls', ['device' => $device, 'view' => $option])),
            'items' => match ($view) {
                'paths' => $device->mplsLspPaths()->with('lsp')->get()->sortBy(fn ($path) => $path->lsp?->mplsLspName)->values(),
                'sdps' => $device->mplsSdps()->orderBy('sdp_oid')->get(),
                'sdpbinds' => $device->mplsSdpBinds()->orderBy('sdp_oid')->orderBy('svc_oid')->get(),
                'services' => $device->mplsServices()->orderBy('svc_oid')->get(),
                'saps' => $device->mplsSaps()->orderBy('svc_oid')->orderBy('sapPortId')->orderBy('sapEncapValue')->get(),
                default => $device->mplsLsps()->orderBy('mplsLspName')->get(),
            },
        ]);
    }
}
