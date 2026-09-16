<?php

/**
 * VminfoController.php
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
 * @copyright  2026 Bennet Gallein
 * @author     Tony Murray <murraytony@gmail.com>
 * @author     Bennet Gallein <me@bennetgallein.de>
 */

namespace App\Http\Controllers\Table;

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
    protected ?string $model = Vminfo::class;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'device_id' => 'nullable|integer',
            ...Vminfo::filterValidationRules(),
        ];
    }

    public function searchFields(Request $request): array
    {
        return ['vmwVmDisplayName', 'vmwVmGuestOS', 'devices.hostname', 'devices.sysname'];
    }

    public function sortFields(Request $request): array
    {
        return ['vmwVmDisplayName', 'vmwVmGuestOS', 'vmwVmMemSize', 'vmwVmCpus', 'vmwVmState', 'hostname'];
    }

    public function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Vminfo::class);

        return Vminfo::hasAccess($request->user())
            ->select('vminfo.*')
            ->with('device')
            ->with('parentDevice')
            ->when($request->integer('device_id'), fn (Builder $query, int $deviceId) => $query->where('vminfo.device_id', $deviceId))
            ->when($request->array('filter'), fn (Builder $query, array $filters) => $query->applyFilters($filters))
            ->when($request->input('searchPhrase') || in_array('hostname', array_keys($request->input('sort', []))), function ($query): void {
                $query->leftJoin('devices', 'devices.device_id', 'vminfo.device_id');
            });
    }

    /**
     * @return list<string>
     */
    protected function getExportHeaders(): array
    {
        return [
            __('Power Status'),
            __('VM Name'),
            __('Type'),
            __('Operating System'),
            __('Memory'),
            __('vCPUs'),
            __('Host'),
            __('Device Id'),
            __('Sysname'),
        ];
    }

    /**
     * @param  Vminfo  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        return [
            'vmwVmState' => '<span class="label ' . $model->stateLabel[1] . '">' . $model->stateLabel[0] . '</span>',
            'vmwVmDisplayName' => is_null($model->parentDevice) ? $model->vmwVmDisplayName : Url::modernDeviceLink($model->parentDevice),
            'vm_type' => $model->vm_type,
            'vmwVmGuestOS' => $model->operatingSystem,
            'vmwVmMemSize' => $model->memoryFormatted,
            'vmwVmCpus' => $model->vmwVmCpus,
            'hostname' => Url::modernDeviceLink($model->device),
            'deviceid' => $model->device_id,
            'sysname' => $model->device?->sysName,
        ];
    }

    /**
     * @param  Vminfo  $item
     * @return array<string, scalar|null>
     */
    protected function formatExportRow(Model $item): array
    {
        return [
            'vmwVmState' => $item->stateLabel[0],
            'vmwVmDisplayName' => is_null($item->parentDevice) ? $item->vmwVmDisplayName : $item->parentDevice->display,
            'vm_type' => $item->vm_type,
            'vmwVmGuestOS' => $item->operatingSystem,
            'vmwVmMemSize' => $item->memoryFormatted,
            'vmwVmCpus' => $item->vmwVmCpus,
            'hostname' => $item->device?->display,
            'deviceid' => $item->device_id,
            'sysname' => $item->device?->sysName,
        ];
    }
}
