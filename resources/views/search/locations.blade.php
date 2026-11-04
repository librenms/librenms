{{-- every port a host was seen on, closest first --}}
@php($muted = 'tw:text-gray-500 tw:dark:text-dark-white-400')
<a href="{{ $summaryUrl }}" class="tw:inline-block tw:mb-2 tw:text-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> {{ __('search.host.back_to_summary') }}</a>
<div class="panel panel-default">
    <x-table class="tw:mb-0">
        <x-slot:head>
            <tr>
                <th>{{ __('search.list.switch_port') }}</th>
                <th>{{ __('search.list.vlan') }}</th>
                <th>{{ __('search.export.macs_on_port') }}</th>
                <th>{{ __('search.list.last_seen') }}</th>
            </tr>
        </x-slot:head>
        <x-slot:body>
            @foreach($host->currentLocations() as $location)
                <tr>
                    <td>
                        @include('search.partials.port', ['port' => $location->port])
                        @if($description = $location->portDescription())
                            <span class="tw:text-sm {{ $muted }}">{{ $description }}</span>
                        @endif
                    </td>
                    <td class="tw:whitespace-nowrap">{{ $location->vlan->vlan_vlan ?? '-' }}</td>
                    <td class="tw:whitespace-nowrap">
                        <span @class([
                            'label tw:cursor-help',
                            'label-warning' => $location->state() === 'trunk',
                            'label-success' => $location->state() === 'direct',
                            'label-default' => $location->state() === 'shared',
                        ]) title="{{ $location->isUplink() ? __('search.host.uplink') : __('search.list.macs_on_port') }}">{{ trans_choice('search.macs_count', $location->macCount) }}{{ $location->isUplink() ? ' ↑' : '' }}</span>
                    </td>
                    <td class="tw:whitespace-nowrap {{ $muted }}">{{ $location->updatedAt?->diffForHumans() ?? '-' }}</td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-table>
</div>
