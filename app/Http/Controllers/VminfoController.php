<?php

namespace App\Http\Controllers;

use App\Models\Vminfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class VminfoController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Vminfo::class);

        $request->validate([
            'page' => 'integer',
            'perPage' => ['regex:/^(\d+|all)$/'],
            ...Vminfo::filterValidationRules(),
        ]);

        $perPage = $request->input('perPage', 50);

        return view('vminfo.index', [
            'vms' => self::paginate($request, null, $perPage),
            'filterFields' => Vminfo::filterFieldDefinitions(),
            'filter' => $request->array('filter'),
            'perPage' => $perPage,
        ]);
    }

    /**
     * @return Builder<Vminfo>
     */
    public static function getFilteredQuery(Request $request, ?int $deviceId = null): Builder
    {
        return Vminfo::hasAccess($request->user())
            ->with(['device', 'parentDevice'])
            ->when($deviceId, fn (Builder $q) => $q->where('vminfo.device_id', $deviceId))
            ->when($request->array('filter'), fn (Builder $q, $filters) => $q->applyFilters($filters))
            ->orderBy('vmwVmDisplayName')
            ->select('vminfo.*');
    }

    /**
     * @return LengthAwarePaginator<int, Vminfo>
     */
    public static function paginate(Request $request, ?int $deviceId, int|string $perPage): LengthAwarePaginator
    {
        $query = self::getFilteredQuery($request, $deviceId);

        $limit = $perPage === 'all'
            ? $query->toBase()->getCountForPagination()
            : (int) $perPage;

        return $query->paginate(max($limit, 1))->appends($request->query());
    }
}
