{{-- every port a host was seen on, closest first --}}
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
                            <span class="tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400">{{ $description }}</span>
                        @endif
                    </td>
                    <td class="tw:whitespace-nowrap">{{ $location->vlan->vlan_vlan ?? '-' }}</td>
                    <td class="tw:whitespace-nowrap">@include('search.partials.macs-on-port')</td>
                    <td class="tw:whitespace-nowrap tw:text-gray-500 tw:dark:text-dark-white-400">{{ $location->updatedAt?->diffForHumans() ?? '-' }}</td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-table>
</div>
