{{-- many matches: one row per host, where it is plugged in and what is known about the port --}}
@php($dayAgo = now()->subDay())
<div class="panel panel-default tw:rounded-t-none! tw:border-t-0!">
    <x-table class="tw:mb-0">
        <x-slot:head>
            <tr>
                @include('search.partials.sort-header', ['key' => 'ip', 'label' => __('search.list.host')])
                @include('search.partials.sort-header', ['key' => 'mac', 'label' => __('search.list.mac')])
                <th>{{ __('search.list.switch_port') }}</th>
                @include('search.partials.sort-header', ['key' => 'seen', 'label' => __('search.list.last_seen')])
            </tr>
        </x-slot:head>
        <x-slot:body>
            @foreach($hosts as $host)
                @php($rows = $host->rows())
                <tr>
                    <td class="tw:whitespace-nowrap">
                        @if($host->ips)
                            @include('search.partials.ip', ['ip' => $host->ips[0]->compressed(), 'primary' => true])
                        @else
                            <span class="tw:text-gray-500 tw:dark:text-dark-white-400">-</span>
                        @endif
                        @if($host->severalMacs)
                            <span class="label label-default tw:cursor-help" title="{{ __('search.host.several_macs_help') }}">{{ trans_choice('search.macs_count', count($host->macs)) }}</span>
                        @elseif($host->isNotSeen())
                            <span class="label label-default">{{ __('search.host.not_seen') }}</span>
                        @endif
                        @include('search.partials.more', ['hidden' => count($host->ips) - 1])
                        @if($host->interface)
                            <span class="tw:block tw:text-sm tw:whitespace-normal">@include('search.partials.port', ['port' => $host->interface])</span>
                        @elseif($host->device)
                            <span class="tw:block tw:text-sm">{!! \LibreNMS\Util\Url::modernDeviceLink($host->device) !!}</span>
                        @endif
                    </td>
                    <td class="tw:whitespace-nowrap">
                        @forelse(array_slice($host->macs, 0, 3) as $mac)
                            <div @class(['tw:opacity-50' => $mac->stale])>@include('search.partials.mac', ['mac' => $mac->hex, 'inlineVendor' => true])</div>
                        @empty
                            <span class="tw:text-gray-500 tw:dark:text-dark-white-400">-</span>
                        @endforelse
                    </td>
                    <td>
                        @foreach($rows as $row)
                            <div @class(['tw:opacity-50' => $row->stale])>
                                @if($location = $row->location)
                                    @include('search.partials.port', ['port' => $location->port])
                                    @if($description = $location->portDescription())
                                        <span class="tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400">{{ $description }}</span>
                                    @endif
                                    @include('search.partials.macs-on-port')
                                    @if($location->vlan)
                                        <span class="tw:text-sm tw:whitespace-nowrap tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('search.list.vlan') }} {{ $location->vlan }}</span>
                                    @endif
                                @elseif($neighbor = $host->viaPort())
                                    <span class="tw:text-gray-500 tw:dark:text-dark-white-400" title="{{ __('search.list.via_help') }}">{{ __('search.list.via') }}</span>
                                    @include('search.partials.port', ['port' => $neighbor])
                                @else
                                    <span class="tw:text-gray-500 tw:dark:text-dark-white-400">-</span>
                                @endif
                            </div>
                        @endforeach
                    </td>
                    <td class="tw:whitespace-nowrap">
                        @foreach($rows as $row)
                            @php($isOld = $row->seen?->lte($dayAgo))
                            <div @class(['tw:text-amber-600 tw:dark:text-amber-300' => $isOld, 'tw:text-gray-500 tw:dark:text-dark-white-400' => ! $isOld])>{{ $row->seen?->diffForHumans() ?? '-' }}</div>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-table>
    @if($results->hasPages())
        <div class="tw:m-3">{{ $results->links('pagination::tailwind') }}</div>
    @endif
</div>
