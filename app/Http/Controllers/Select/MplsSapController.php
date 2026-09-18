<?php

/**
 * MplsSapController.php
 *
 * Select2 ajax controller for Nokia MPLS SAPs (service access points)
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
 */

namespace App\Http\Controllers\Select;

use App\Models\MplsSap;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @extends SelectController<MplsSap>
 */
class MplsSapController extends SelectController
{
    /**
     * Defines validation rules (will override base validation rules for select2 responses too)
     *
     * @return array<string, string>
     */
    protected function rules(): array
    {
        return [
            'device' => 'nullable|int',
        ];
    }

    /**
     * Defines search fields will be searched in order
     *
     * @return array<int, string>
     */
    protected function searchFields(Request $request): array
    {
        return ['ifName', 'svc_oid', 'sapDescription'];
    }

    /**
     * Defines the base query for this resource
     */
    protected function baseQuery(Request $request): Builder
    {
        $query = MplsSap::hasAccess($request->user())
            ->with(['device' => function ($query): void {
                $query->select(['device_id', 'hostname', 'sysName', 'display']);
            }])
            ->with(['service' => function ($query): void {
                $query->select(['svc_id', 'svcDescription']);
            }]);

        if ($device_id = $request->input('device')) {
            $query->where('mpls_saps.device_id', $device_id);
        }

        return $query;
    }

    /**
     * @param  MplsSap  $model
     *
     * @returns array{id: int|string, text: string}
     */
    public function formatItem(Model $model): array
    {
        $description = (string) $model->service?->svcDescription;
        if ($description == '') {
            $description = (string) $model->sapDescription;
        }

        return [
            'id' => $model->sap_id,
            'text' => $model->ifName . ':' . $model->encap_display . ' - Svc ' . $model->svc_oid . ' - ' . $model->device->shortDisplayName() . ($description == '' ? '' : ' - ' . $description),
            'device_id' => $model->device_id,
        ];
    }
}
