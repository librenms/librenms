<?php

namespace App\Http\Controllers;

use App\Models\PortSecurity;
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
            'perPage' => 'integer',
            ...PortSecurity::filterValidationRules(),
        ]);

        $perPage = $request->integer('perPage', 50);

        return view('port-security.index', [
            'pagetitle' => __('Port Security'),
            'portSecurity' => static::getFilteredQuery($request)
                ->paginate($perPage)
                ->appends($request->query()),
            'filterFields' => PortSecurity::filterFieldDefinitions(),
            'filter' => $request->array('filter'),
            'perPage' => $perPage,
            'paginationOptions' => [50, 100, 250],
            'showDevice' => true,
        ]);
    }

    /**
     * @return Builder<PortSecurity>
     */
    public static function getFilteredQuery(Request $request, ?int $deviceId = null): Builder
    {
        return PortSecurity::query()
            ->hasAccess(Auth::user())
            ->with(['device', 'port'])
            ->when($deviceId, fn (Builder $q) => $q->where('port_security.device_id', $deviceId))
            ->when($request->array('filter'), fn (Builder $q, $filters) => $q->applyFilters($filters))
            ->leftJoin('ports', 'port_security.port_id', '=', 'ports.port_id')
            ->orderBy('ports.ifIndex')
            ->select('port_security.*');
    }

    /**
     * @return LengthAwarePaginator<int, PortSecurity>
     */
    public static function paginateForDevice(int $deviceId, int|string $perPage): LengthAwarePaginator
    {
        $request = request();
        $query = self::getFilteredQuery($request, $deviceId);

        $limit = $perPage === 'all'
            ? $query->toBase()->getCountForPagination()
            : (int) $perPage;

        return $query->paginate($limit)->appends($request->query());
    }
}
