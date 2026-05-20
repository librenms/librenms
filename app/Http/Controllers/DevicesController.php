<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use LibreNMS\Util\Url;

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

        $bare = $request->input('bare') === 'yes';
        $hideFilter = $request->input('searchbar') === 'hide';
        $perPage = $request->integer('per_page', 50);

        $legacyFormat = (string) $request->string('format');
        if ($legacyFormat) {
            if (str_starts_with($legacyFormat, 'graph_')) {
                $view ??= 'graph';
                $graph ??= substr($legacyFormat, 6);
            } else {
                $view ??= match ($legacyFormat) {
                    'list_basic' => 'basic',
                    default => 'detail',
                };
                $graph ??= '';
            }
        }

        $graphTemplate = [
            'height' => 110,
            'width' => session('widescreen') ? 270 : 315,
            'id' => 0,
            'type' => 'device_' . $graph,
            'from' => $request->input('from', '-1d'),
            'legend' => 'no',
            'title' => 'yes',
        ];
        if ($request->input('to')) {
            $graphTemplate['to'] = $request->input('to');
        }

        $devices = $this->getDevices($view, $perPage);

        return view('device.index', [
            'view' => $view,
            'graph' => $graph,
            'detailed' => $view === 'detail',
            'devices' => $devices,
            'deviceGraphs' => $devices->map(function (Device $device) use ($graphTemplate) {
                $graph = array_merge($graphTemplate, ['device' => $device->device_id]);
                return [
                    'link' => Url::graphPageUrl($graph['type'], Arr::except($graph, ['height', 'width', 'legend', 'title'])),
                    'graphTag' => Url::lazyGraphTag($graph, 'tw:w-full tw:h-auto'),
                    'deviceLinkOptions' => ['device_id' => $device->device_id, 'params' => ['type' => $graph['type']]],
                ];
            }),
            'perPage' => $perPage,
            'paginationOptions' => [50, 100, 250, -1],
            'nav' => [
                'detail' => ['text' => 'Detail', 'link' => route('devices', ['view' => 'detail', ...$request->query()])],
                'basic' => ['text' => 'Basic', 'link' => route('devices', ['view' => 'basic', ...$request->query()])],
            ],
            'graphNav' => [
                'bits' => ['text' => 'Bits', 'link' => route('devices', ['view' => 'graph', 'graph' => 'bits', ...$request->query()])],
                'processor' => ['text' => 'CPU', 'link' => route('devices', ['view' => 'graph', 'graph' => 'processor', ...$request->query()])],
                'ucd_load' => ['text' => 'Load', 'link' => route('devices', ['view' => 'graph', 'graph' => 'ucd_load', ...$request->query()])],
                'mempool' => ['text' => 'Memory', 'link' => route('devices', ['view' => 'graph', 'graph' => 'mempool', ...$request->query()])],
                'uptime' => ['text' => 'Uptime', 'link' => route('devices', ['view' => 'graph', 'graph' => 'uptime', ...$request->query()])],
                'storage' => ['text' => 'Storage', 'link' => route('devices', ['view' => 'graph', 'graph' => 'storage', ...$request->query()])],
                'diskio' => ['text' => 'Disk I/O', 'link' => route('devices', ['view' => 'graph', 'graph' => 'diskio', ...$request->query()])],
                'poller_perf' => ['text' => 'Poller', 'link' => route('devices', ['view' => 'graph', 'graph' => 'poller_perf', ...$request->query()])],
                'icmp_perf' => ['text' => 'Ping', 'link' => route('devices', ['view' => 'graph', 'graph' => 'icmp_perf', ...$request->query()])],
                'temperature' => ['text' => 'Temperature', 'link' => route('devices', ['view' => 'graph', 'graph' => 'temperature', ...$request->query()])],
            ],
            'bare' => $bare,
            'bareLink' => $bare ? $request->fullUrlWithoutQuery('bare') : $request->fullUrlWithQuery(['bare' => 'yes']),
            'filter' => $request->array('filter'),
            'hideFilterLink' => $hideFilter ? $request->fullUrlWithoutQuery('searchbar') : $request->fullUrlWithQuery(['searchbar' => 'hide']),
            'hideFilter' => $hideFilter,
            'filterFields' => $this->filterFields(),
            'graphTemplate' => $graphTemplate,
            'group' => $request->input('filter.groups\.id.eq'),
        ]);
    }

    private function getDevices(?string $view, int $perPage): LengthAwarePaginator|Collection
    {
        if ($view !== 'graph') {
            return new Collection;
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
