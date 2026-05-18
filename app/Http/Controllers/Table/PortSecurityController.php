<?php

/**
 * PortSecurityController.php
 *
 * Port Security tables data for bootgrid display
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

namespace App\Http\Controllers\Table;

use App\Http\Controllers\PortSecurityController as PortSecurityPageController;
use App\Models\PortSecurity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @extends TableController<PortSecurity>
 */
class PortSecurityController extends TableController
{
    protected ?string $model = PortSecurity::class;

    protected function rules(): array
    {
        return [
            'device_id' => 'nullable|integer',
            'page' => 'nullable|integer',
            'per_page' => 'nullable|integer',
            'perPage' => 'nullable',
            ...PortSecurity::filterValidationRules(),
        ];
    }

    protected function filterFields(Request $request): array
    {
        return [];
    }

    protected function baseQuery(Request $request): Builder
    {
        return PortSecurityPageController::getFilteredQuery(
            $request,
            $request->integer('device_id') ?: null
        );
    }

    protected function prepareExportQuery(Request $request): Builder
    {
        $query = $this->baseQuery($request);

        $page = (int) $request->input('current', $request->input('page', 0));
        $perPage = $request->input('rowCount', $request->input('per_page', $request->input('perPage')));

        if ($page > 0 && $perPage !== null && $perPage !== '' && $perPage !== 'all') {
            $limit = (int) $perPage;
            if ($limit > 0) {
                $query->skip(($page - 1) * $limit)->take($limit);
            }
        }

        return $query;
    }

    /**
     * @return list<string>
     */
    protected function getExportHeaders(): array
    {
        $headers = [];

        if (! request()->integer('device_id')) {
            $headers[] = 'device_id';
            $headers[] = 'hostname';
        }

        return array_merge($headers, [
            'port',
            'ifName',
            'ifDescr',
            'ifAlias',
            'enabled',
            'status',
            'current_macs',
            'max_macs',
            'violation_action',
            'violations',
            'last_mac',
            'sticky',
        ]);
    }

    /**
     * @return list<scalar>
     */
    protected function formatExportRow(Model $item): array
    {
        assert($item instanceof PortSecurity);

        return array_merge(
            request()->integer('device_id') ? [] : [
                $item->device_id,
                $item->device?->displayName() ?? '',
            ],
            [
                $item->port?->getShortLabel() ?? '',
                $item->port->ifName ?? '',
                $item->port->ifDescr ?? '',
                $item->port->ifAlias ?? '',
                $item->port_security_enable ?? '',
                $item->status ?? '',
                $item->address_count ?? '',
                $item->max_addresses ?? '',
                $item->violation_action ?? '',
                $item->violation_count ?? '',
                $item->last_mac_address ?? '',
                $item->sticky_enable ?? '',
            ],
        );
    }
}
