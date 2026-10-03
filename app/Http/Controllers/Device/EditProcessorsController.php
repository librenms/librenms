<?php

/**
 * EditProcessorsController.php
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
use App\Models\Processor;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EditProcessorsController
{
    use AuthorizesRequests;

    public function index(Device $device): View
    {
        $this->authorize('update', $device);

        return view('device.edit.processors', [
            'device' => $device,
            'processors' => $device->processors()->orderBy('processor_descr')->get(),
        ]);
    }

    public function update(Request $request, Device $device, Processor $processor): JsonResponse
    {
        $this->authorize('update', $device);
        if (Gate::denies('update', $processor)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'processor_perc_warn_custom' => 'nullable|numeric|between:0,100',
        ]);

        // empty clears the user threshold, the discovered (or default) threshold is used
        $custom = $validated['processor_perc_warn_custom'] ?? null;
        $processor->processor_perc_warn_custom = $custom === null ? null : (int) round((float) $custom);
        if ($custom === null) {
            $processor->processor_perc_warn = null;
        }

        if ($processor->save()) {
            return response()->json([
                'status' => 'ok',
                'message' => __('Processor information updated'),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => __('Could not update processor information'),
        ]);
    }
}
