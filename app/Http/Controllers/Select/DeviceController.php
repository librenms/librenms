<?php
/**
 * DeviceController.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Select;

use App\Models\Device;

class DeviceController extends SelectController
{
    private $id = 'device_id';

    protected function rules()
    {
        return [
            'access' => 'nullable|in:normal,inverted',
            'user' => 'nullable|int',
            'id' => 'nullable|in:device_id,hostname',
        ];
    }

    protected function searchFields($request)
    {
        return ['hostname', 'sysName'];
    }

    protected function baseQuery($request)
    {
        $this->id = $request->get('id', 'device_id');
        $user_id = $request->get('user');

        // list devices the user does not have access to
        if ($request->get('access') == 'inverted' && $user_id && $request->user()->isAdmin()) {
            return Device::query()
                ->select('device_id', 'hostname', 'sysName')
                ->whereNotIn('device_id', function ($query) use ($user_id) {
                    $query->select('device_id')
                        ->from('devices_perms')
                        ->where('user_id', $user_id);
                })
                ->orderBy('hostname');
        }

        return Device::hasAccess($request->user())
            ->select('device_id', 'hostname', 'sysName')
            ->orderBy('hostname');
    }

    public function formatItem($device)
    {
        /** @var Device $device */
        return [
            'id' => $device->{$this->id},
            'text' => $device->displayName(),
        ];
    }
}
