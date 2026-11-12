<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Location;
use App\Models\PollerGroup;
use App\Models\Secret;
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
                'display',
                'hardware',
                'os',
                'uptime',
                'location',
            ]),
        ]);

        $bare = $request->input('bare') === 'yes';
        $hideFilter = $request->input('searchbar') === 'hide';
        $perPage = $request->integer('per_page', 50);

        [$view, $graph] = $this->parseLegacyUrls((string) $view, (string) $graph, $request);

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
            'filterFields' => $this->filterFields($request),
            'graphTemplate' => $graphTemplate,
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, Device>|Collection<int, Device>
     */
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

    /**
     * @return array<array{key: string, label: string, type: string, endpoint?: string, options?: string[]|array<string, string>, params?: array<string, string>}>
     */
    private function filterFields(Request $request): array
    {
        $fields = [
            [
                'key' => 'search',
                'label' => __('Search'),
                'type' => 'text',
                'search' => true,
            ],
            [
                'key' => 'state',
                'label' => __('device.status'),
                'type' => 'select',
                'options' => [
                    'up' => __('device.status_up'),
                    'down' => __('device.status_down'),
                ],
            ],
            [
                'key' => 'os',
                'label' => __('device.os'),
                'type' => 'select',
                'endpoint' => route('ajax.select.device-field'),
                'params' => [
                    'field' => 'os',
                ],
            ],
            [
                'key' => 'version',
                'label' => __('Version'),
                'type' => 'select',
                'endpoint' => route('ajax.select.device-field'),
                'params' => [
                    'field' => 'version',
                ],
            ],
            [
                'key' => 'hardware',
                'label' => __('Platform'),
                'type' => 'select',
                'endpoint' => route('ajax.select.device-field'),
                'params' => [
                    'field' => 'hardware',
                ],
            ],
            [
                'key' => 'features',
                'label' => __('Featureset'),
                'type' => 'select',
                'endpoint' => route('ajax.select.device-field'),
                'params' => [
                    'field' => 'features',
                ],
            ],
        ];

        if ($request->user()->can('viewAny', Location::class)) {
            $fields[] = [
                'key' => 'location_id',
                'label' => __('Location'),
                'type' => 'select',
                'endpoint' => route('ajax.select.location'),
            ];
        }

        $fields[] = [
            'key' => 'type',
            'label' => __('device.device_type'),
            'type' => 'select',
            'endpoint' => route('ajax.select.device-field'),
            'params' => [
                'field' => 'type',
            ],
        ];

        if ($request->user()->can('viewAny', DeviceGroup::class)) {
            $fields[] = [
                'key' => 'groups.id',
                'label' => __('device.device_group'),
                'type' => 'select',
                'endpoint' => route('ajax.select.device-group'),
            ];
        }

        if ($request->user()->can('viewAny', Secret::class)) {
            $fields[] = [
                'key' => 'secrets.secret_id',
                'label' => __('Secret'),
                'type' => 'select',
                'endpoint' => route('ajax.select.secret'),
            ];
        }

        if ($request->user()->can('viewAny', PollerGroup::class)) {
            $fields[] = [
                'key' => 'poller_group',
                'label' => __('device.edit.poller_group'),
                'type' => 'select',
                'endpoint' => route('ajax.select.poller-group'),
            ];
        }

        $fields[] = [
            'key' => 'disabled',
            'label' => __('Disabled'),
            'type' => 'boolean',
        ];
        $fields[] = [
            'key' => 'ignore',
            'label' => __('Ignored'),
            'type' => 'boolean',
        ];
        $fields[] = [
            'key' => 'disable_notify',
            'label' => __('device.alerts_disabled'),
            'type' => 'boolean',
        ];

        return $fields;
    }

    /**
     * @return array{string, string}
     */
    private function parseLegacyUrls(string $view, string $graph, Request $request): array
    {
        $legacy = Url::parseLegacyPath($request->path());

        // handle legacy format
        $legacyFormat = $legacy->getString('format');
        if (! in_array($view, ['detail', 'basic', 'graph'])) {
            if (str_starts_with($legacyFormat, 'graph_')) {
                $view = 'graph';
                $graph = substr($legacyFormat, 6);
            } elseif ($legacyFormat === 'list_basic') {
                $view = 'basic';
            } else {
                $view = 'detail';
            }
        }

        if ($view === 'graph') {
            $graph = $graph ?: 'bits';
        } else {
            $graph = '';
        }

        // handle legacy filters
        $filters = [];
        $fields = [
            'type',
            'state',
            'disable_notify',
            'disabled',
            'ignore',
            'poller_group',
        ];

        foreach ($fields as $field) {
            if ($legacy->has($field)) {
                $v = $legacy->get($field);
                $filters[$field] = ['eq' => is_numeric($v) ? (int) $v : $v];
            }
        }

        if ($legacy->has('group')) {
            $v = $legacy->get('group');
            $filters['groups.id'] = $v === 'none' ? ['is_empty' => 1] : ['eq' => (int) $v];
        }

        if ($legacy->has('location')) {
            $v = $legacy->get('location');
            $locationId = is_numeric($v) ? (int) $v : Location::where('location', urldecode((string) $v))->value('id');
            if ($locationId) {
                $filters['location_id'] = ['eq' => $locationId];
            }
        }

        if (! empty($filters)) {
            $request->merge(['filter' => array_merge($request->input('filter', []), $filters)]);
        }

        // handle graph date time selector (but formats don't match 100%)
        $to = $legacy->get('to');
        $request->mergeIfMissing([
            'from' => preg_replace('/(^-?\d+[a-z])[a-z]*/i', '$1', $legacy->getString('from')) ?: null, // try to fix bad formats
            'to' => $to == 'now' ? null : $to,
        ]);

        return [$view, $graph];
    }
}
