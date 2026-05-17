<?php

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
            [],
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
     * @param  PortSecurity  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        return [
            'device' => $model->device?->displayName() ?? '',
            'interface' => $model->port?->getShortLabel() ?? '',
            'port_description' => $model->port?->ifAlias ?? '',
            'enable' => $model->port_security_enable ?? '',
            'status' => $model->status ?? '',
            'current_secure' => $model->address_count ?? '',
            'max_secure' => $model->max_addresses ?? '',
            'violation_action' => $model->violation_action ?? '',
            'violation_count' => $model->violation_count ?? '',
            'secure_last_mac' => $model->last_mac_address ?? '',
            'sticky_enable' => $model->sticky_enable ?? '',
        ];
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
     * @param  PortSecurity  $item
     * @return list<scalar>
     */
    protected function formatExportRow(Model $item): array
    {
        $row = [];

        if (! request()->integer('device_id')) {
            $row[] = $item->device_id;
            $row[] = $item->device?->displayName() ?? '';
        }

        return array_merge($row, [
            $item->port?->getShortLabel() ?? '',
            $item->port?->ifName ?? '',
            $item->port?->ifDescr ?? '',
            $item->port?->ifAlias ?? '',
            $item->port_security_enable ?? '',
            $item->status ?? '',
            $item->address_count ?? '',
            $item->max_addresses ?? '',
            $item->violation_action ?? '',
            $item->violation_count ?? '',
            $item->last_mac_address ?? '',
            $item->sticky_enable ?? '',
        ]);
    }
}
