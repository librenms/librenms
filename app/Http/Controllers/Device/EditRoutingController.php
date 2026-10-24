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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Modules\Vrf;

class EditRoutingController
{
    use AuthorizesRequests;

    public function index(Device $device): View
    {
        $this->authorize('update', $device);

        $manualContexts = Vrf::manualContexts($device);
        $vrfs = $device->vrfs()->orderBy('vrf_name')->get();

        return view('device.edit.routing', [
            'device' => $device,
            'vrfs' => $vrfs,
            'snmp_contexts' => $manualContexts,
            'pending_contexts' => $this->pendingContexts($manualContexts, $vrfs),
            'peers' => $device->bgppeers()
                ->orderBy('bgpPeerRemoteAs')
                ->orderBy('bgpPeerIdentifier')
                ->get(),
            'can_update_peers' => Gate::allows('update', BgpPeer::class),
        ]);
    }

    /**
     * Manual contexts without a VRF yet, with the context the device mapped to a VRF of the same name
     * (discovery ignores the manual context then)
     *
     * @param  string[]  $manualContexts
     * @param  Collection<int, \App\Models\Vrf>  $vrfs
     * @return array<string, string|null>
     */
    private function pendingContexts(array $manualContexts, Collection $vrfs): array
    {
        $pending = [];
        foreach (array_diff($manualContexts, $vrfs->pluck('context_name')->filter()->all()) as $context) {
            $pending[$context] = $vrfs->first(fn ($vrf) => strcasecmp((string) $vrf->vrf_name, $context) == 0)?->context_name;
        }

        return $pending;
    }

    public function updateContexts(Request $request, Device $device): RedirectResponse
    {
        $this->authorize('update', $device);

        $validated = $request->validate([
            'snmp_contexts' => 'nullable|array',
            'snmp_contexts.*' => 'nullable|string|max:255',
            'context_enabled' => 'nullable|array',
            'context_enabled.*' => 'boolean',
        ]);

        /** @var array<int|string, string> $contextEnabled */
        $contextEnabled = $validated['context_enabled'] ?? [];
        foreach ($device->vrfs()->whereNotNull('context_name')->whereIn('vrf_id', array_keys($contextEnabled))->get() as $vrf) {
            $vrf->context_enabled = (bool) $contextEnabled[$vrf->vrf_id];
            $vrf->save();
        }

        /** @var array<int, string|null> $submittedContexts */
        $submittedContexts = $validated['snmp_contexts'] ?? [];

        $snmpContexts = collect($submittedContexts)
            ->map(fn (?string $context) => trim((string) $context))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($snmpContexts !== []) {
            $device->setAttrib(Vrf::MANUAL_CONTEXTS_ATTRIB, json_encode($snmpContexts));
        } else {
            $device->forgetAttrib(Vrf::MANUAL_CONTEXTS_ATTRIB);
        }

        toast()->success(__('VRFs updated'));

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
