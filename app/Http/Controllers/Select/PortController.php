<?php

/**
 * PortController.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Select;

use App\Models\Port;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @extends SelectController<Port>
 */
class PortController extends SelectController
{
    /**
     * Defines validation rules (will override base validation rules for select2 responses too)
     */
    protected function rules(): array
    {
        return [
            'device' => 'nullable|int',
            'devices' => 'nullable|array',
        ];
    }

    /**
     * Defines search fields will be searched in order
     */
    protected function searchFields(Request $request): array
    {
        return (array) $request->input('field', ['ifAlias', 'ifName', 'ifDescr', 'devices.hostname', 'devices.sysName']);
    }

    /**
     * Defines the base query for this resource
     */
    protected function baseQuery(Request $request): Builder
    {
        $query = Port::hasAccess($request->user())
            ->isNotDeleted()
            ->has('device')
            ->with(['device' => function ($query): void {
                $query->select(['device_id', 'hostname', 'sysName', 'display']);
            }])
            ->select(['ports.device_id', 'port_id', 'ifAlias', 'ifName', 'ifDescr'])
            ->groupBy(['ports.device_id', 'port_id', 'ifAlias', 'ifName', 'ifDescr']);

        if ($request->input('term')) {
            // join with devices for searches
            $query->leftJoin('devices', 'devices.device_id', 'ports.device_id');
        }

        if ($device_id = $request->input('device')) {
            $query->where('ports.device_id', $device_id);
        }

        if ($device_ids = $request->input('devices')) {
            $query->whereIn('ports.device_id', $device_ids);
        }

        return $query;
    }

    /**
     * @param  Port  $model
     *
     * @returns array{id: int|string, text: string, icon?: string}
     */
    public function formatItem(Model $model): array
    {
        $label = $model->getShortLabel();
        $description = ($label == $model->ifAlias ? '' : ' - ' . $model->ifAlias);

        return [
            'id' => $model->port_id,
            'text' => $label . ' - ' . $model->device->shortDisplayName() . $description,
            'device_id' => $model->device_id,
        ];
    }
}
