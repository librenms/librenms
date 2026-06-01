<?php

/**
 * SyslogController.php
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

namespace App\Http\Controllers\Table;

use App\Models\Device;
use App\Models\Vminfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LibreNMS\Util\Url;

/**
 * @extends TableController<Vminfo>
 */
class VminfoController extends TableController
{
    public function searchFields(Request $request): array
    {
        return ['vmwVmDisplayName', 'vmwVmGuestOS', 'devices.hostname', 'devices.sysname'];
    }

    public function sortFields(Request $request): array
    {
        return ['vmwVmDisplayName', 'vmwVmGuestOS', 'vmwVmMemSize', 'vmwVmCpus', 'vmwVmState', 'hostname'];
    }

    /**
     * Defines the base query for this resource
     */
    public function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Vminfo::class);

        return Vminfo::hasAccess($request->user())
            ->select('vminfo.*')
            ->with('device')
            ->with('parentDevice')
            ->when($request->input('searchPhrase') || in_array('hostname', array_keys($request->input('sort', []))), function ($query): void {
                $query->leftJoin('devices', 'devices.device_id', 'vminfo.device_id');
            });
    }

    /**
     * @param  Vminfo  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        return [
            'vmwVmState' => '<span class="label ' . $model->stateLabel[1] . '">' . $model->stateLabel[0] . '</span>',
            'vmwVmDisplayName' => is_null($model->parentDevice) ? $model->vmwVmDisplayName : self::getHostname($model->parentDevice),
            'vmwVmGuestOS' => $model->operatingSystem,
            'vmwVmMemSize' => $model->memoryFormatted,
            'vmwVmCpus' => $model->vmwVmCpus,
            'hostname' => self::getHostname($model->device),
            'deviceid' => $model->device_id,
            'sysname' => $model->device->sysName,

        ];
    }

    private static function getHostname(Device $device): string
    {
        return '<a class="list-device" href="' . Url::deviceUrl($device) . '">' . $device->displayName() . '</a>';
    }
}
