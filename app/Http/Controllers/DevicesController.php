<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DevicesController extends Controller
{
    public function index(Request $request, ?string $view = null, ?string $graph = null): View
    {
        $request->validate([
            'format' => 'in:list_basic,list_detail,graph_bits,graph_processor,graph_ucd_load,graph_mempool,graph_uptime,graph_storage,graph_diskio,graph_poller_perf,graph_icmp_perf,graph_temperature', // legacy
            'bare' => ['nullable', 'in:yes'],
            'searchbar' => ['nullable', 'in:hide'],
            'per_page' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer'],
            'to' => ['nullable', 'date_or_relative'],
            'from' => ['nullable', 'date_or_relative'],
            ...Device::filterValidationRules(),
            'sort' => Rule::in([
                'status',
                'device_id',
                'maintenance',
                'hostname',
                'hardware',
                'os',
                'uptime',
                'location',
            ]),
        ]);

        $view ??= $request->input('format', 'list_detail');
        $bare = $request->input('bare') === 'yes';
        $hideFilter = $request->input('searchbar') === 'hide';
        $perPage = $request->integer('per_page', 50); // from bootgrid rowCount

        if (str_starts_with((string) $view, 'list_')) {
            $subformat = substr((string) $view, 5);
            $view = 'list';
        } elseif (str_starts_with((string) $view, 'graph_')) {
            $graph = substr((string) $view, 6);
            $view = 'graph';
            $subformat = $graph;
        } else {
            // default
            $subformat = 'detail';
            $view = 'list';
        }

        $detailed = $subformat === 'detail';

        $graphTemplate = [
            'height' => 110,
            'width' => session('widescreen') ? 270 : 315,
            'id' => 0,
            'type' => 'device_' . $graph,
            'from' => $request->input('from', '-24hour'), // from devices.inc.php
            'legend' => 'no',
            'title' => 'yes',
        ];
        if ($request->input('to')) {
            $graphTemplate['to'] = $request->input('to');
        } else {
            $graphTemplate['to'] = 'now';
        }

        return view('device.index', [
            'view' => $view,
            'subformat' => $subformat,
            'detailed' => $detailed,
            'devices' => $this->getDevices($view, $perPage),
            'perPage' => $perPage,
            'paginationOptions' => [50, 100, 250, -1],
            'nav' => [
                'detail' => ['text' => 'Detail', 'link' => route('devices', ['format' => 'list_detail', ...$request->query()])],
                'basic' => ['text' => 'Basic', 'link' => route('devices', ['format' => 'list_basic', ...$request->query()])],
            ],
            'graphNav' => [
                'bits' => ['text' => 'Bits', 'link' => route('devices', ['format' => 'graph_bits', ...$request->query()])],
                'processor' => ['text' => 'CPU', 'link' => route('devices', ['format' => 'graph_processor', ...$request->query()])],
                'ucd_load' => ['text' => 'Load', 'link' => route('devices', ['format' => 'graph_ucd_load', ...$request->query()])],
                'mempool' => ['text' => 'Memory', 'link' => route('devices', ['format' => 'graph_mempool', ...$request->query()])],
                'uptime' => ['text' => 'Uptime', 'link' => route('devices', ['format' => 'graph_uptime', ...$request->query()])],
                'storage' => ['text' => 'Storage', 'link' => route('devices', ['format' => 'graph_storage', ...$request->query()])],
                'diskio' => ['text' => 'Disk I/O', 'link' => route('devices', ['format' => 'graph_diskio', ...$request->query()])],
                'poller_perf' => ['text' => 'Poller', 'link' => route('devices', ['format' => 'graph_poller_perf', ...$request->query()])],
                'icmp_perf' => ['text' => 'Ping', 'link' => route('devices', ['format' => 'graph_icmp_perf', ...$request->query()])],
                'temperature' => ['text' => 'Temperature', 'link' => route('devices', ['format' => 'graph_temperature', ...$request->query()])],
            ],
            'bare' => $bare,
            'bareLink' => $bare ? $request->fullUrlWithoutQuery('bare') : $request->fullUrlWithQuery(['bare' => 'yes']),
            'filter' => $request->array('filter'),
            'hideFilterLink' => $hideFilter ? $request->fullUrlWithoutQuery('searchbar') : $request->fullUrlWithQuery(['searchbar' => 'hide']),
            'hideFilter' => $hideFilter,
            'filterFields' => $this->filterFields(),
            'graphTemplate' => $graphTemplate,
            'group' => $request->input('filter.group.eq'),
        ]);
    }

    private function getDevices(string $view, int $perPage): ?LengthAwarePaginator
    {
        if ($view !== 'graph') {
            return null;
        }

        $devicesQuery = Device::hasAccess(request()->user())
            ->select(['devices.*'])
            ->with('location')
            ->when(request()->array('filter'), fn (Builder $query, $filters) => $query->applyFilters($filters));

        $devicesQuery->orderBy('hostname');

        return $devicesQuery->paginate($perPage);
    }

    private function filterFields(): array
    {
        return [
            [
                'key' => 'search',
                'label' => 'Search',
                'type' => 'text',
            ],
            [
                'key' => 'state',
                'label' => 'Status',
                'type' => 'select',
                'options' => [
                    'up' => 'Up',
                    'down' => 'Down',
                ],
            ],
            [
                'key' => 'os',
                'label' => 'OS',
                'type' => 'select',
                'endpoint' => route('ajax.select.device-field'),
                'params' => [
                    'field' => 'os',
                ],
            ],
            [
                'key' => 'version',
                'label' => 'Version',
                'type' => 'select',
                'endpoint' => route('ajax.select.device-field'),
                'params' => [
                    'field' => 'version',
                ],
            ],
            [
                'key' => 'hardware',
                'label' => 'Platform',
                'type' => 'select',
                'endpoint' => route('ajax.select.device-field'),
                'params' => [
                    'field' => 'hardware',
                ],
            ],
            [
                'key' => 'features',
                'label' => 'Featureset',
                'type' => 'select',
                'endpoint' => route('ajax.select.device-field'),
                'params' => [
                    'field' => 'features',
                ],
            ],
            [
                'key' => 'location_id',
                'label' => 'Location',
                'type' => 'select',
                'endpoint' => route('ajax.select.location'),
            ],
            [
                'key' => 'type',
                'label' => 'Device Type',
                'type' => 'select',
                'endpoint' => route('ajax.select.device-field'),
                'params' => [
                    'field' => 'type',
                ],
            ],
            [
                'key' => 'groups.id',
                'label' => 'Group',
                'type' => 'select',
                'endpoint' => route('ajax.select.device-group'),
            ],
            [
                'key' => 'poller_group',
                'label' => 'Poller Group',
                'type' => 'select',
                'endpoint' => route('ajax.select.poller-group'),
            ],
            [
                'key' => 'disabled',
                'label' => 'Disabled',
                'type' => 'boolean',
            ],
            [
                'key' => 'ignore',
                'label' => 'Ignored',
                'type' => 'boolean',
            ],
            [
                'key' => 'disable_notify',
                'label' => 'Alerts Disabled',
                'type' => 'boolean',
            ],
        ];
    }
}
