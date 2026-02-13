<?php
/**
 * EditMiscController.php
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

use App\Http\Requests\UpdateDeviceMiscRequest;
use App\Models\Device;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class EditMiscController
{
    public function index(Device $device): View
    {
        return view('device.edit.misc', [
            'device' => $device,
        ]);
    }

    public function update(UpdateDeviceMiscRequest $request, Device $device): RedirectResponse
    {
        $overrides = [
            'override_icmp_disable',
            'override_Oxidized_disable',
            'override_device_ssh_port',
            'override_device_telnet_port',
            'override_device_http_port',
            'override_Unixagent_port',
            'override_rrdtool_tune',
            'selected_ports',
        ];

        foreach ($overrides as $attrib) {
            $value = $request->validated($attrib);
            if ($value === true) {
                $device->setAttrib($attrib, 'true');
            } elseif (empty($value)) {
                $device->forgetAttrib($attrib);
            } else {
                $device->setAttrib($attrib, (string) $value);
            }
        }

        toast()->success(__('Device record updated'));

        return response()->redirectToRoute('device.edit.misc', ['device' => $device->device_id]);
    }
}
