<?php
/**
 * EditDeviceController.php
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device;

use App\Models\Device;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EditDeviceController
{
    public function index(Device $device): View
    {
        return view('device.edit.device', [
            'device' => $device,
        ]);
    }

    public function update(Request $request, Device $device): JsonResponse
    {
        $validated = $request->validate([
            'display' => 'nullable|string',
            'overwrite_ip' => 'nullable|string',
            'descr' => 'nullable|string',
            'type' => 'nullable|string',
            'parent_id' => 'nullable|array',
            'parent_id.*' => 'integer',
            'override_sysLocation' => 'nullable|boolean',
            'sysLocation' => 'nullable|string',
            'override_sysContact' => 'nullable|boolean',
            'sysContact' => 'nullable|string',
            'disable_notify' => 'nullable|boolean',
            'ignore' => 'nullable|boolean',
            'ignore_status' => 'nullable|boolean',
        ]);

        dd($request->all(), $validated);

        return response()->json([]);
    }
}
