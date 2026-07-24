<?php

/**
 * SapController.php
 *
 * Select2 controller for Nokia SAP (Service Access Point) selection.
 * Used for adding SAPs to a bill.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS Contributors
 */

namespace App\Http\Controllers\Select;

use App\Models\MplsSap;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @extends SelectController<MplsSap>
 */
class SapController extends SelectController
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
     * @return list<string>
     */
    protected function searchFields(Request $request): array
    {
        return ['svc_oid', 'ifName', 'sapEncapValue', 'sapDescription', 'devices.hostname', 'devices.sysName'];
    }

    /**
     * Defines the base query for this resource
     */
    protected function baseQuery(Request $request): Builder
    {
        $query = MplsSap::hasAccess($request->user())
            ->has('device')
            ->with(['device' => function ($query): void {
                $query->select(['device_id', 'hostname', 'sysName', 'display']);
            }])
            ->select(['mpls_saps.device_id', 'sap_id', 'svc_oid', 'sapPortId', 'ifName', 'sapEncapValue', 'sapDescription', 'sapType']);

        if ($request->input('term')) {
            // join with devices for searches
            $query->leftJoin('devices', 'devices.device_id', 'mpls_saps.device_id');
        }

        // Filter by device if specified
        if ($device_id = $request->input('device')) {
            $query->where('mpls_saps.device_id', $device_id);
        }

        $query->orderBy('svc_oid')->orderBy('sapPortId')->orderBy('sapEncapValue');

        return $query;
    }

    /**
     * Format a SAP item for Select2 display
     *
     * @param  MplsSap  $model
     * @return array{id: int|string, text: string, icon?: string, device_id?: int}
     */
    public function formatItem(Model $model): array
    {
        // Match the device MPLS SAPs view: Service ID - SAP Port - Encapsulation
        $port = $model->ifName ?: $model->sapPortId;
        $text = $model->svc_oid . ' - ' . $port . ' - ' . $model->sapEncapValue;
        if ($model->sapDescription) {
            $text .= ' (' . $model->sapDescription . ')';
        }

        return [
            'id' => $model->sap_id,
            'text' => $text,
            'device_id' => $model->device_id,
        ];
    }
}
