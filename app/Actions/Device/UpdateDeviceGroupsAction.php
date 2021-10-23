<?php
/**
 * UpdateDeviceGroupsAction.php
 *
 * Update device group associations by re-checking rules
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
 * @link       http://librenms.org
 *
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DeviceGroup;
use Log;

class UpdateDeviceGroupsAction
{
    /**
     * @var \App\Models\Device
     */
    private $device;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    /**
     * @return array[]
     */
    public function execute(): array
    {
        if (! $this->device->exists) {
            // Device not saved to DB, cowardly refusing
            return [
                'attached' => [],
                'detached' => [],
                'updated' => [],
            ];
        }

        $device_group_ids = DeviceGroup::query()
            ->with(['devices' => function ($query) {
                $query->select('devices.device_id');
            }])
            ->get()
            ->filter(function (DeviceGroup $device_group) {
                if ($device_group->type == 'dynamic') {
                    try {
                        return $device_group->getParser()
                            ->toQuery()
                            ->where('devices.device_id', $this->device->device_id)
                            ->exists();
                    } catch (\Illuminate\Database\QueryException $e) {
                        Log::error("Device Group '$device_group->name' generates invalid query: " . $e->getMessage());

                        return false;
                    }
                }

                // for static, if this device is include, keep it.
                return $device_group->devices
                    ->where('device_id', $this->device->device_id)
                    ->isNotEmpty();
            })->pluck('id');

        return $this->device->groups()->sync($device_group_ids);
    }
}
