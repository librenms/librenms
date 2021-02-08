<?php
/**
 * DeviceFieldController.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Select;

use App\Models\Device;
use LibreNMS\Config;

class DeviceFieldController extends SelectController
{
    /**
     * Defines validation rules (will override base validation rules for select2 responses too)
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'field' => 'required|in:features,hardware,os,type,version',
        ];
    }

    /**
     * Defines search fields will be searched in order
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function searchFields($request)
    {
        return [$request->get('field')];
    }

    /**
     * Defines the base query for this resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function baseQuery($request)
    {
        $field = $request->get('field');
        $query = Device::hasAccess($request->user())
            ->select($field)->orderBy($field)->distinct();

        if ($device_id = $request->get('device')) {
            $query->where('ports.device_id', $device_id);
        }

        return $query;
    }

    /**
     * @param Device $device
     * @return array
     */
    public function formatItem($device)
    {
        $field = \Request::get('field');

        $text = $device[$field];
        if ($field == 'os') {
            $text = Config::getOsSetting($text, 'text');
        } elseif ($field == 'type') {
            $text = ucfirst($text);
        }

        return [
            'id' => $device[$field],
            'text' => $text,
        ];
    }
}
