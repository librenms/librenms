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

namespace App\Http\Controllers\Routing;

use App\Http\Controllers\Controller;
use App\Models\MplsLsp;
use App\Models\MplsLspPath;
use App\Models\MplsSap;
use App\Models\MplsSdp;
use App\Models\MplsSdpBind;
use App\Models\MplsService;
use App\View\Components\Routing\MplsTable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MplsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $request->validate([
            'view' => 'nullable|in:' . implode(',', MplsTable::VIEWS),
        ]);

        $view = (string) $request->query('view', 'lsp');
        $user = $request->user();

        return view('routing.mpls', [
            'view' => $view,
            'mpls_options' => MplsTable::options(fn (string $option) => route('routing.mpls', ['view' => $option])),
            'items' => match ($view) {
                'paths' => MplsLspPath::hasAccess($user)->with('lsp')->orderBy('device_id')->get()
                    ->sortBy(fn (MplsLspPath $path) => [$path->device_id, $path->lsp?->mplsLspName])->values(),
                'sdps' => MplsSdp::hasAccess($user)->orderBy('device_id')->orderBy('sdp_oid')->get(),
                'sdpbinds' => MplsSdpBind::hasAccess($user)->orderBy('device_id')->orderBy('sdp_oid')->orderBy('svc_oid')->get(),
                'services' => MplsService::hasAccess($user)->orderBy('device_id')->orderBy('svc_oid')->get(),
                'saps' => MplsSap::hasAccess($user)->orderBy('device_id')->orderBy('svc_oid')->orderBy('sapPortId')->orderBy('sapEncapValue')->get(),
                default => MplsLsp::hasAccess($user)->orderBy('device_id')->orderBy('mplsLspName')->get(),
            },
        ]);
    }
}
