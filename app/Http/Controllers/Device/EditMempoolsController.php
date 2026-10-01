<?php

/**
 * EditMempoolsController.php
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

use App\Models\Device;
use App\Models\Mempool;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EditMempoolsController
{
    use AuthorizesRequests;

    public function index(Device $device): View
    {
        $this->authorize('update', $device);

        return view('device.edit.mempools', [
            'device' => $device,
            'mempools' => $device->mempools()->orderBy('mempool_descr')->get(),
        ]);
    }

    public function update(Request $request, Device $device, Mempool $mempool): JsonResponse
    {
        $this->authorize('update', $device);
        if (Gate::denies('update', $mempool)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'mempool_perc_warn' => 'nullable|numeric|between:0,100',
        ]);

        $warnPercent = $validated['mempool_perc_warn'] ?? null;
        $mempool->mempool_perc_warn = $warnPercent === null ? null : (int) round((float) $warnPercent);

        // MempoolObserver reverts mempool_perc_warn on update so polling cannot overwrite user changes
        if ($mempool->saveQuietly()) {
            return response()->json([
                'status' => 'ok',
                'message' => __('Memory information updated'),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => __('Could not update memory information'),
        ]);
    }
}
