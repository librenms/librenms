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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
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
            'id' => 'nullable|in:device_id,hostname'
        ];
    }

    protected function searchFields($request)
    {
        return ['hostname', 'sysName'];
    }

    protected function baseQuery($request)
    {
        $this->id = $request->get('id', 'device_id');

        return Device::hasAccess($request->user())->select('device_id', 'hostname', 'sysName');
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
