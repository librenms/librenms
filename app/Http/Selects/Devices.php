<?php
/**
 * Devices.php
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

namespace App\Http\Selects;

use App\Models\Device;
use Illuminate\Support\Collection;

class Devices extends BaseSelect
{
    protected $searchFields = ['hostname', 'sysName'];
    protected $selectFields = ['device_id', 'hostname', 'sysName'];

    /**
     * Get the base query for this object
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    protected function baseQuery()
    {
        return Device::hasAccess($this->request->user());
    }

    /**
     * Override this to format the data as needed
     * Default implementation filters out all keys except, id and text
     *
     * @param Collection $items
     * @return Collection
     */
    protected function format($items)
    {
        return $items->map(function (Device $device) {
            return [
                'id' => $device->device_id,
                'text' => $device->displayName(),
            ];
        });
    }
}
