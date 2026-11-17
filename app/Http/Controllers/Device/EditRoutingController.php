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
use App\Models\Vrf as VrfModel;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class EditRoutingController
{
    use AuthorizesRequests;

    public function index(Device $device): View
    {
        $this->authorize('update', $device);

        return view('device.edit.routing', [
            'device' => $device,
            'vrfs' => $this->vrfs($device),
            'peers' => $device->bgppeers()
                ->orderBy('bgpPeerRemoteAs')
                ->orderBy('bgpPeerIdentifier')
                ->get(),
            'can_update_peers' => Gate::allows('update', BgpPeer::class),
        ]);
    }

    /**
     * Add a VRF the device does not list through its SNMP context
     */
    public function storeVrf(Request $request, Device $device): JsonResponse
    {
        $this->authorize('update', $device);

        $validated = $request->validate([
            'context' => ['bail', 'required', 'string', 'max:128', $this->unusedContext($device), function (string $attribute, string $value, Closure $fail) use ($device): void {
                if (VrfModel::isDefaultName($value)) {
                    $fail(__('The default VRF is polled without a context.'));

                    return;
                }

                $vrf = $device->vrfs->first(fn (VrfModel $vrf) => strcasecmp((string) $vrf->vrf_name, $value) == 0);
                if ($vrf !== null) {
                    $fail(__('VRF :vrf exists, set its context in the table.', ['vrf' => $vrf->vrf_name]));
                }
            }],
        ]);

        $vrf = $device->vrfs()->create([
            'vrf_oid' => VrfModel::contextOnlyOid($validated['context']),
            'vrf_name' => $validated['context'],
            'context_name' => $validated['context'],
            'context_manual' => true,
            'vrf_manual' => true,
            'mplsVpnVrfDescription' => '',
        ]);

        return response()->json([
            'message' => __('VRF :vrf added', ['vrf' => $vrf->vrf_name]),
            'vrfs' => $this->vrfs($device),
        ]);
    }

    public function updateVrf(Request $request, Device $device, VrfModel $vrf): JsonResponse
    {
        $this->authorize('update', $device);

        $validated = $request->validate([
            'context_enabled' => 'sometimes|boolean',
            'context_name' => ['sometimes', 'nullable', 'string', 'max:128', $this->unusedContext($device, $vrf)],
        ]);

        if (array_key_exists('context_name', $validated)) {
            if (! $this->contextEditable($vrf)) {
                throw ValidationException::withMessages(['context_name' => __('The context of VRF :vrf cannot be changed.', ['vrf' => $vrf->vrf_name])]);
            }

            if ($validated['context_name'] !== $vrf->context_name) {
                // the next discovery checks the context before it is walked
                $vrf->fill(['context_name' => $validated['context_name'], 'context_manual' => $validated['context_name'] !== null, 'context_verified' => false, 'context_failures' => 0]);
            }
        }

        if (array_key_exists('context_enabled', $validated)) {
            $vrf->context_enabled = (bool) $validated['context_enabled'];
        }

        $vrf->save();

        return response()->json($this->vrfData($device, $vrf));
    }

    /**
     * Remove a VRF only known through its SNMP context
     */
    public function destroyVrf(Device $device, VrfModel $vrf): JsonResponse
    {
        $this->authorize('update', $device);
        abort_unless($vrf->vrf_manual, 422, __('VRF :vrf is listed by the device.', ['vrf' => $vrf->vrf_name]));

        $vrf->delete();

        return response()->json([
            'vrfs' => $this->vrfs($device),
        ]);
    }

    /**
     * A context exposes a single VRF. SNMP contexts are case-sensitive.
     */
    private function unusedContext(Device $device, ?VrfModel $except = null): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($device, $except): void {
            $vrf = $device->vrfs->first(fn (VrfModel $vrf) => $vrf->context_name === $value && $vrf->vrf_id !== $except?->vrf_id);
            if ($vrf !== null) {
                $fail(__('Context :context is used by VRF :vrf.', ['context' => $value, 'vrf' => $vrf->vrf_name]));
            }
        };
    }

    /**
     * Users set the context of VRFs the device lists without a context.
     * Contexts the device maps and the context of VRFs only known through it are fixed.
     */
    private function contextEditable(VrfModel $vrf): bool
    {
        return ! $vrf->isDefault() && ! $vrf->vrf_manual && ($vrf->context_name === null || $vrf->context_manual);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function vrfs(Device $device): array
    {
        return $device->vrfs()->orderBy('vrf_name')->get()->map(fn (VrfModel $vrf) => $this->vrfData($device, $vrf))->all();
    }

    /**
     * @return array{name: string|null, rd: string|null, context: string|null, context_only: bool, device_context: bool, enabled: bool, editable: bool, status: string, update_url: string, destroy_url: string}
     */
    private function vrfData(Device $device, VrfModel $vrf): array
    {
        return [
            'name' => $vrf->vrf_name,
            'rd' => $vrf->mplsVpnVrfRouteDistinguisher,
            'context' => $vrf->context_name,
            'context_only' => $vrf->vrf_manual,
            'device_context' => $vrf->context_name !== null && ! $vrf->context_manual,
            'enabled' => $vrf->context_enabled,
            'editable' => $this->contextEditable($vrf),
            'status' => match (true) {
                $vrf->context_name === null => 'none',
                ! $vrf->context_enabled => 'disabled',
                $vrf->context_verified => 'answering',
                $vrf->context_failures == 0 => 'waiting',
                default => 'failing',
            },
            'update_url' => route('device.edit.routing.vrf.update', [$device, $vrf]),
            'destroy_url' => route('device.edit.routing.vrf.destroy', [$device, $vrf]),
        ];
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
