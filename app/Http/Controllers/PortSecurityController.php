<?php

namespace App\Http\Controllers;

use App\Models\PortSecurity;
use App\Models\UserPref;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortSecurityController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'page' => 'integer',
            'per_page' => 'integer',
            ...PortSecurity::filterValidationRules(),
        ]);

        $perPage = $request->integer('per_page', 50);
        $savedFilter = UserPref::getPref($request->user(), 'filters.port-security') ?: [];

        return view('port-security.index', [
            'pagetitle' => __('Port Security'),
            'portSecurity' => static::getFilteredQuery($request, $savedFilter)
                ->paginate($perPage)
                ->appends($request->query()),
            'filterFields' => PortSecurity::filterFieldDefinitions(),
            'filter' => array_merge($savedFilter, $request->array('filter')),
            'perPage' => $perPage,
            'paginationOptions' => [50, 100, 250],
            'showDevice' => true,
        ]);
    }

    /**
     * @param  array<string, array<string, mixed>>  $savedFilter
     * @return Builder<PortSecurity>
     */
    public static function getFilteredQuery(Request $request, array $savedFilter = [], ?int $deviceId = null): Builder
    {
        $filters = array_merge($savedFilter, $request->array('filter'));

        return PortSecurity::query()
            ->hasAccess(Auth::user())
            ->with(['device', 'port'])
            ->when($deviceId, fn (Builder $q) => $q->where('port_security.device_id', $deviceId))
            ->when($filters, fn (Builder $q) => $q->applyFilters($filters))
            ->leftJoin('ports', 'port_security.port_id', '=', 'ports.port_id')
            ->orderBy('ports.ifIndex')
            ->select('port_security.*');
    }

    /**
     * @param  array<string, array<string, mixed>>  $savedFilter
     * @return LengthAwarePaginator<PortSecurity>
     */
    public static function paginateForDevice(Request $request, array $savedFilter, int $deviceId, int|string $perPage): LengthAwarePaginator
    {
        $total = PortSecurity::query()
            ->where('device_id', $deviceId)
            ->hasAccess(Auth::user())
            ->count();

        $limit = $perPage === 'all' ? max($total, 1) : (int) $perPage;

        return self::getFilteredQuery($request, $savedFilter, $deviceId)
            ->paginate($limit)
            ->appends($request->query());
    }
}
