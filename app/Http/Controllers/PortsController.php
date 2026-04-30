<?php

namespace App\Http\Controllers;

use App\Models\Port;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PortsController extends Controller
{
    public function index(Request $request, ?string $view = null, ?string $graph = null)
    {
        $request->validate([
            // TODO
        ]);

        $errors = $request->input('errors');
        $view ??= $request->input('view', 'basic');
        if (str_starts_with($view, 'list_')) {
            $view = substr($view, 5);
        } elseif (str_starts_with($view, 'graph_')) {
            $view = 'graph';
            $graph = substr($view, 6);
        }

        $bare = $request->input('bare') === 'yes';
        $hideFilter = $request->input('searchbar') === 'hide';
        $perPage = $request->integer('per_page', 48);

        return view('port.index', [
            'view' => $view,
            'graph' => $graph,
            'show_detail' => $view === 'detail' ? 'true' : 'false',
            'show_errors' => $view === 'detail' || $errors ? 'true' : 'false',
            'ports' => $this->getPorts($view, $perPage),
            'perPage' => $perPage,
            'paginationOptions' => [12, 24, 48, 128, 568, 4096],
            'nav' => [
                'basic' => ['text' => 'Basic', 'link' => route('ports', $request->query())],
                'detail' => ['text' => 'Detail', 'link' => route('ports', ['view' => 'detail', ...$request->query()])],
            ],
            'graphNav' => [
                'bits' => ['text' => 'Bits', 'link' => route('ports', ['view' => 'graph', 'graph' => 'bits', ...$request->query()])],
                'upkts' => ['text' => 'Unicast Packets', 'link' => route('ports', ['view' => 'graph', 'graph' => 'upkts', ...$request->query()])],
                'nupkts' => ['text' => 'Non-Unicast Packets', 'link' => route('ports', ['view' => 'graph', 'graph' => 'nupkts', ...$request->query()])],
                'errors' => ['text' => 'Errors', 'link' => route('ports', ['view' => 'graph', 'graph' => 'errors', ...$request->query()])],
            ],
            'bare' => $bare,
            'bareLink' => $bare ? $request->fullUrlWithoutQuery('bare') : $request->fullUrlWithQuery(['bare' => 'yes']),
            'filter' => $request->array('filter'),
            'hideFilterLink' => $hideFilter ? $request->fullUrlWithoutQuery('searchbar') : $request->fullUrlWithQuery(['searchbar' => 'hide']),
            'hideFilter' => $hideFilter,
            'filterFields' => $this->filterFields(),
            'graphTemplate' => [
                'height' => 100,
                'width' => session('widescreen') ? 357 : 315,
                'id' => 0,
                'type' => 'port_' . $graph,
                'from' => '-1d',
                'legend' => 'no',
                'title' => 'yes',
            ],
        ]);
    }

    public function purge(Request $request): JsonResponse
    {
        $request->validate([
            'purge' => ['required', 'regex:/^(\d+|all)$/']
        ]);

        $purge = $request->input('purge');
        if ($purge === 'all') {
            Port::hasAccess($request->user())->with(['device' => function ($query): void {
                $query->select('device_id', 'hostname');
            }])->isDeleted()->chunkById(100, function ($ports): void {
                foreach ($ports as $port) {
                    $port->delete();
                }
            });

            return response()->json(['message' => 'Successfully purged all deleted ports']);
        }

        try {
            Port::hasAccess($request->user())->where('port_id', $purge)->firstOrFail()->delete();
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Port ID ' . ((int) $purge) . ' not found! Could not purge port.'], 422);
        }

        return response()->json(['message' => 'Successfully purged port ID ' . ((int) $purge)]);
    }


    private function filterFields(): array
    {
        return [
            [
                'key' => 'device_id',
                'label' => __('Device'),
                'type' => 'select',
                'endpoint' => route('ajax.select.device'),
            ],
            [
                'key' => 'device.location_id',
                'label' => __('Location'),
                'type' => 'select',
                'endpoint' => route('ajax.select.location'),
            ],
            [
                'key' => 'search',
                'label' => 'Description',
                'type' => 'text',
            ],
            [
                'key' => 'state',
                'label' => 'Oper Status',
                'type' => 'select',
                'options' => [
                    'up',
                    'down',
                    'shutdown'
                ],
            ],
            [
                'key' => 'ifSpeed',
                'label' => 'Speed',
                'type' => 'select',
                'endpoint' => route('ajax.select.port-field'),
                'params' => [
                    'field' => 'ifSpeed',
                ],
            ],
            [
                'key' => 'ifType',
                'label' => 'Media',
                'type' => 'select',
                'endpoint' => route('ajax.select.port-field'),
                'params' => [
                    'field' => 'ifType',
                ],
            ],
            [
                'key' => 'ifDuplex',
                'label' => 'Duplex',
                'type' => 'select',
                'options' => [
                    'fullDuplex' => 'Full',
                    'halfDuplex' => 'Half',
                    'unknown' => 'unknown',
                ],
            ],
            [
                'key' => 'port_type',
                'label' => 'Port Type',
                'type' => 'select',
                'endpoint' => route('ajax.select.port-field'),
                'params' => [
                    'field' => 'port_descr_type',
                ],
            ],
            [
                'key' => 'ignore',
                'label' => 'Ignored',
                'type' => 'boolean',
            ],
            [
                'key' => 'disabled',
                'label' => 'Disabled',
                'type' => 'boolean',
            ],
            [
                'key' => 'deleted',
                'label' => 'Deleted',
                'type' => 'boolean',
            ],
        ];
    }

    private function getPorts(string $view, int $perPage): ?LengthAwarePaginator
    {
        if ($view !== 'graph') {
            return null;
        }

        $portsQuery = Port::hasAccess(request()->user())
            ->with(['device'])
            ->isValid()
            ->whereHas('device') // a device is required for graphs to work
            ->when(request()->array('filter'), fn(Builder $query, $filters) => $query->applyFilters($filters));

        $portsQuery = match ($vars['sort'] ?? '') {
            'traffic' => $portsQuery->orderByRaw('ifInOctets_rate + ifOutOctets_rate desc'),
            'traffic_in' => $portsQuery->orderBy('ifInOctets_rate', 'desc'),
            'traffic_out' => $portsQuery->orderBy('ifOutOctets_rate', 'desc'),
            'packets' => $portsQuery->orderByRaw('ifInUcastPkts_rate + ifOutUcastPkts_rate desc'),
            'packets_in' => $portsQuery->orderBy('ifInUcastPkts_rate', 'desc'),
            'packets_out' => $portsQuery->orderBy('ifOutUcastPkts_rate', 'desc'),
            'errors' => $portsQuery->orderByRaw('ifInErrors_rate + ifOutErrors_rate desc'),
            'speed' => $portsQuery->orderBy('ifSpeed', 'desc'),
            'port' => $portsQuery->orderBy('ifDescr'),
            'media' => $portsQuery->orderBy('ifType'),
            'descr' => $portsQuery->orderBy('ifAlias'),
            default => $portsQuery->leftJoin('devices', 'ports.device_id', 'devices.device_id')
                ->orderBy('hostname')
                ->orderBy('ifIndex'),
        };

        return $portsQuery->paginate($perPage, ['ports.*']);
    }
}
