<?php

/**
 * EditRoutingController.php
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

namespace App\Http\Controllers\Device;

use App\Models\BgpPeer;
use App\Models\Device;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EditRoutingController
{
    use AuthorizesRequests;

    public function index(Device $device): View
    {
        $this->authorize('update', $device);

        $snmpContexts = json_decode((string) $device->getAttrib('routing_snmp_contexts', '[]'), true);

        return view('device.edit.routing', [
            'device' => $device,
            'snmp_contexts' => is_array($snmpContexts) ? $snmpContexts : [],
            'peers' => $device->bgppeers()
                ->orderBy('bgpPeerRemoteAs')
                ->orderBy('bgpPeerIdentifier')
                ->get(),
            'can_update_peers' => Gate::allows('update', BgpPeer::class),
        ]);
    }

    public function updateContexts(Request $request, Device $device): RedirectResponse
    {
        $this->authorize('update', $device);

        $validated = $request->validate([
            'snmp_contexts' => 'nullable|array',
            'snmp_contexts.*' => 'nullable|string|max:255',
        ]);

        /** @var array<int, string|null> $submittedContexts */
        $submittedContexts = $validated['snmp_contexts'] ?? [];

        $snmpContexts = collect($submittedContexts)
            ->map(fn (?string $context) => trim((string) $context))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($snmpContexts !== []) {
            $device->setAttrib('routing_snmp_contexts', json_encode($snmpContexts));
        } else {
            $device->forgetAttrib('routing_snmp_contexts');
        }

        toast()->success(__('SNMP contexts updated'));

        return response()->redirectToRoute('device.edit.routing', ['device' => $device->device_id]);
    }

    public function updatePeer(Request $request, Device $device, BgpPeer $bgpPeer): JsonResponse
    {
        $this->authorize('update', $device);
        if (Gate::denies('update', $bgpPeer)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'descr' => 'nullable|string|max:255',
        ]);

        $bgpPeer->bgpPeerDescr = $validated['descr'] ?? '';

        if ($bgpPeer->save()) {
            return response()->json([
                'status' => 'ok',
                'message' => __('Routing information updated'),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => __('Could not update Routing information'),
        ]);
    }
}
