<?php

/**
 * EditStorageController.php
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
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EditStorageController
{
    use AuthorizesRequests;

    public function index(Device $device): View
    {
        $this->authorize('update', $device);

        return view('device.edit.storage', [
            'device' => $device,
            'storages' => $device->storage()->orderBy('storage_descr')->get(),
        ]);
    }

    public function update(Request $request, Device $device, int $storage): JsonResponse
    {
        $this->authorize('update', $device);
        $storage = $device->storage()->findOrFail($storage);
        if (Gate::denies('update', $storage)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'storage_perc_warn' => 'required|numeric|between:0,100',
        ]);

        $storage->storage_perc_warn = (int) round((float) $validated['storage_perc_warn']);

        if ($storage->save()) {
            return response()->json([
                'status' => 'ok',
                'message' => __('Storage information updated'),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => __('Could not update storage information'),
        ]);
    }
}
