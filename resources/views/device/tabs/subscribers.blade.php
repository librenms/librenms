@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-panel title="{{ __('Subscribers') }}">
        <div class="tw:flex tw:flex-wrap tw:gap-6 tw:mb-4">
            @foreach($data['system'] as $type => $sessions)
                <div>
                    <div class="tw:text-sm text-muted">{{ $data['type_names'][$type] ?? $type }}</div>
                    <div class="tw:text-2xl tw:font-semibold">{{ number_format($sessions) }}</div>
                </div>
            @endforeach
        </div>
        <x-graph-row :device="$device" type="device_subscribers" columns="responsive" legend="yes" />
    </x-panel>

    @if($data['radius_servers']->isNotEmpty())
        <x-panel title="{{ __('Authentication (RADIUS)') }}">
            <x-slot:table>
                <table class="table table-hover table-condensed table-striped tw:mb-0!">
                    <thead>
                        <tr>
                            <th>{{ __('Server') }}</th>
                            <th>{{ __('State') }}</th>
                            <th class="tw:text-right">{{ __('Requests') }}</th>
                            <th class="tw:text-right">{{ __('Accepts') }}</th>
                            <th class="tw:text-right">{{ __('Rejects') }}</th>
                            <th class="tw:text-right">{{ __('Timeouts') }}</th>
                            <th class="tw:text-right">{{ __('Response time') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($data['radius_servers'] as $server)
                        <tr>
                            <td>{{ $server->address }}:{{ $server->port }}</td>
                            <td>
                                @if($server->state)
                                    <span @class(['label', 'label-success' => $server->state === 'up', 'label-danger' => $server->state !== 'up'])>{{ $server->state }}</span>
                                @endif
                                @if($server->dead_count)
                                    <small class="text-muted">{{ trans_choice('dead :count time|dead :count times', $server->dead_count, ['count' => number_format($server->dead_count)]) }}</small>
                                @endif
                            </td>
                            <td class="tw:text-right">{{ $server->requests !== null ? number_format($server->requests) : '-' }}</td>
                            <td class="tw:text-right">{{ $server->accepts !== null ? number_format($server->accepts) : '-' }}</td>
                            <td class="tw:text-right">
                                @if($server->rejects !== null)
                                    {{ number_format($server->rejects) }}
                                    @if($server->requests)
                                        <small class="text-muted">({{ round($server->rejects / $server->requests * 100) }}%)</small>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="tw:text-right">
                                @if($server->timeouts !== null)
                                    {{ number_format($server->timeouts) }}
                                    @if($server->requests)
                                        <small class="text-muted">({{ round($server->timeouts / $server->requests * 100) }}%)</small>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="tw:text-right">{{ $server->round_trip_time !== null ? $server->round_trip_time . ' ms' : '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </x-slot:table>
            @foreach($data['radius_servers'] as $server)
                <x-graph-row :device="$device" type="device_radius_auth" :title="__('RADIUS :server', ['server' => $server->address . ':' . $server->port])" :vars="['id' => $server->id]" columns="responsive" legend="yes" />
            @endforeach
            <x-graph-row :device="$device" type="device_radius_rtt" :title="__('RADIUS response time')" columns="responsive" legend="yes" />
        </x-panel>
    @endif

    @if($data['vpdn_graphs']->isNotEmpty())
        <x-panel title="{{ __('VPDN (L2TP)') }}">
            @foreach($data['vpdn_graphs'] as $graph)
                <x-graph-row :device="$device" :type="'device_' . $graph" columns="responsive" legend="yes" />
            @endforeach
        </x-panel>
    @endif

    @if($data['port_count'] > 0)
        <x-panel>
            <x-slot:title>
                {{ __('Subscribers per interface') }}
                <small class="text-muted">
                    {{ trans_choice(':count interface|:count interfaces', $data['port_count'], ['count' => number_format($data['port_count'])]) }}@if($data['ports_down']), <span class="text-danger">{{ trans_choice(':count without sessions|:count without sessions', $data['ports_down'], ['count' => number_format($data['ports_down'])]) }}</span>@endif
                </small>
            </x-slot:title>
            <form method="get" class="tw:mb-3 tw:flex tw:gap-2">
                <input type="hidden" name="per_page" value="{{ $data['per_page'] }}">
                <input type="search" name="search" value="{{ $data['search'] }}" class="form-control input-sm tw:max-w-sm" placeholder="{{ __('Search interface or description') }}">
                <button type="submit" class="btn btn-default btn-sm">{{ __('Search') }}</button>
            </form>
            <x-slot:table>
                <table class="table table-hover table-condensed table-striped tw:mb-0!">
                    <thead>
                        <tr>
                            <th>{{ __('Interface') }}</th>
                            <th>{{ __('Description') }}</th>
                            @foreach($data['port_types'] as $type)
                                <th class="tw:text-right">{{ $data['type_names'][$type] ?? $type }}</th>
                            @endforeach
                            <th class="tw:text-right">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($data['ports'] as $row)
                        <tr @class(['danger' => $row['total'] === 0])>
                            <td><a href="{{ \LibreNMS\Util\Url::portUrl($row['port']) }}" class="{{ \LibreNMS\Util\Url::portLinkDisplayClass($row['port']) }}">{{ $row['port']->getLabel() }}</a></td>
                            <td>{{ $row['port']->ifAlias }}</td>
                            @foreach($data['port_types'] as $type)
                                <td class="tw:text-right">{{ isset($row['sessions'][$type]) ? number_format($row['sessions'][$type]) : '' }}</td>
                            @endforeach
                            <td class="tw:text-right tw:font-semibold">{{ number_format($row['total']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($data['port_types']) + 3 }}" class="tw:p-5 tw:text-center"><em>{{ __('No interfaces found.') }}</em></td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
                <x-pagination-footer :paginator="$data['paginator']" :options="\App\Http\Controllers\Device\Tabs\SubscribersController::PER_PAGE_OPTIONS" :per-page="$data['per_page']"/>
            </x-slot:table>
        </x-panel>
    @endif
</x-device.page>
@endsection
