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

use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Models\Device;
use App\Models\PollerGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LibreNMS\Exceptions\HostRenameException;
use LibreNMS\Util\File;
use LibreNMS\Util\Number;

class EditDeviceController
{
    public function index(Device $device): View
    {
        $types = array_column(LibrenmsConfig::get('device_types'), 'text', 'type');
        if (! isset($types[$device->type])) {
            $types[$device->type] = $device->type;
        }
        [$rrd_size, $rrd_num] = File::getFolderSize(Rrd::dirFromHost($device->hostname));

        return view('device.edit.device', [
            'device' => $device,
            'types' => $types,
            'parents' => $device->parents()->pluck('device_id'),
            'devices' => Device::orderBy('hostname')->whereNot('device_id', $device->device_id)->select(['device_id', 'hostname', 'sysName'])->get(),
            'poller_groups' => PollerGroup::orderBy('group_name')->pluck('group_name', 'id'),
            'default_poller_group' => LibrenmsConfig::get('distributed_poller_group'),
            'maintenance' => $device->isUnderMaintenance(),
            'override_sysContact_bool' => $device->getAttrib('override_sysContact_bool'),
            'override_sysContact_string' => $device->getAttrib('override_sysContact_string', $device->sysContact),
            'rrd_size' => Number::formatBi($rrd_size),
            'rrd_num' => $rrd_num,
        ]);
    }

    public function update(Request $request, Device $device): RedirectResponse
    {
        $validated = $request->validate([
            'hostname' => 'nullable|ip_or_hostname',
            'display' => 'nullable|string',
            'overwrite_ip' => 'nullable|string',
            'purpose' => 'nullable|string',
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

        // sync parent ids
        if (isset($validated['parent_id'])) {
            $parents = array_diff((array) $validated['parent_id'], ['0']);
            // TODO avoid loops!
            $device->parents()->sync($parents);
        }

        // fill validated fields
         $device->fill($validated);

        // handle sysLocation update
        if ($device->override_sysLocation) {
            $device->setLocation($validated['sysLocation'], true, true);
            $device->location?->save();
        } elseif ($device->isDirty('override_sysLocation')) {
            // no longer overridden, clear location
            $device->location()->dissociate();
        }

        // check if type was overridden
        if ($device->isDirty('type')) {
            $device->setAttrib('override_device_type', true);
        }

        // check if sysContact is overridden
        dd($validated['override_sysContact']);
        if ($validated['override_sysContact']) {
            $device->setAttrib('override_sysContact_bool', true);
            $device->setAttrib('override_sysContact_string', $validated['sysContact']);
        } else {
            $device->forgetAttrib('override_sysContact_bool');
        }

        // save it, no message if no changes
        try {
            if ($device->isDirty()) {
                if ($device->save()) {
                    toast()->success(__('Device record updated'));
                } else {
                    toast()->error(__('Device record update error'));
                }
            }
        } catch (HostRenameException $e) {
            toast()->error($e->getMessage());
        }

        return response()->redirectToRoute('device', ['device' => $device->device_id, 'edit']);
    }
}
