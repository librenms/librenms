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
 *
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Select;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @extends SelectController<Device>
 */
class DeviceFieldController extends SelectController
{
    /**
     * Defines validation rules (will override base validation rules for select2 responses too)
     */
    protected function rules(): array
    {
        return [
            'field' => 'required|in:features,hardware,os,type,version',
        ];
    }

    /**
     * Defines search fields will be searched in order
     */
    protected function searchFields(Request $request): array
    {
        return [$request->input('field')];
    }

    /**
     * Defines the base query for this resource
     */
    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Device::class);

        $field = $request->input('field');
        $query = Device::hasAccess($request->user())
            ->select($field)->orderBy($field)->distinct();

        if ($device_id = $request->input('device')) {
            $query->where('ports.device_id', $device_id);
        }

        return $query;
    }

    /**
     * @param  Device  $model
     * @return array{id: int|string, text: string, icon?: string}
     */
    public function formatItem(Model $model): array
    {
        $field = \Request::input('field');

        $text = $model->$field;
        if ($field == 'os') {
            $text = LibrenmsConfig::getOsSetting($text, 'text');
        } elseif ($field == 'type') {
            $text = ucfirst((string) $text);
        }

        return [
            'id' => $model->$field,
            'text' => $text,
        ];
    }
}
