<?php

namespace App\Http\Controllers;

use App\Models\Vminfo;
use App\View\Components\VminfoGraphs;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VminfoController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Vminfo::class);

        $request->validate([
            ...Vminfo::filterValidationRules(),
            ...VminfoGraphs::validationRules(),
        ]);

        $graph = $request->input('graph');

        return view('vminfo', [
            'filterFields' => Vminfo::filterFieldDefinitions(),
            'filter' => $request->array('filter'),
            'graph' => $graph,
            'nav' => VminfoGraphs::nav($request->query(), fn (array $query): string => route('vminfo.index', $query)),
            'vms' => $graph ? Vminfo::hasAccess($request->user())
                ->recordsMetrics()
                ->applyFilters($request->array('filter'))
                ->with('device')
                ->orderBy('vmwVmDisplayName')
                ->paginate($request->integer('per_page', VminfoGraphs::PER_PAGE_OPTIONS[0]))
                ->withQueryString() : null,
        ]);
    }
}
