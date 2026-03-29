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
            'override_icmp_disable' => $device->getAttrib('override_icmp_disable'),
            'override_Oxidized_disable' => $device->getAttrib('override_Oxidized_disable'),
            'override_device_ssh_port' => $device->getAttrib('override_device_ssh_port'),
            'override_device_telnet_port' => $device->getAttrib('override_device_telnet_port'),
            'override_device_http_port' => $device->getAttrib('override_device_http_port'),
            'override_Unixagent_port' => $device->getAttrib('override_Unixagent_port'),
            'override_rrdtool_tune' => $device->getAttrib('override_rrdtool_tune'),
            'selected_ports' => $device->getAttrib('selected_ports'),
        ]);
    }

    public function update(UpdateDeviceMiscRequest $request, Device $device): RedirectResponse
    {
        $this->updateAttribute($device, 'override_icmp_disable', $request->validated('override_icmp_disable'));
        $this->updateAttribute($device, 'override_Oxidized_disable', $request->validated('override_Oxidized_disable'));
        $this->updateAttribute($device, 'override_device_ssh_port', $request->validated('override_device_ssh_port'));
        $this->updateAttribute($device, 'override_device_telnet_port', $request->validated('override_device_telnet_port'));
        $this->updateAttribute($device, 'override_device_http_port', $request->validated('override_device_http_port'));
        $this->updateAttribute($device, 'override_Unixagent_port', $request->validated('override_Unixagent_port'));
        $this->updateAttribute($device, 'override_rrdtool_tune', $request->validated('override_rrdtool_tune'));
        $this->updateAttribute($device, 'selected_ports', $request->validated('selected_ports'));

        toast()->success(__('Device record updated'));

        return response()->redirectToRoute('device.edit.misc', ['device' => $device->device_id]);
    }

    private function updateAttribute(Device $device, string $attrib, mixed $value): void
    {
        if ($value === true) {
            $device->setAttrib($attrib, 'true');
        } elseif ($value === null || $value === '' || $value === false) {
            $device->forgetAttrib($attrib);
        } else {
            $device->setAttrib($attrib, (string) $value);
        }
    }
}
