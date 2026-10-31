{{-- many matches: one row per host, where it is plugged in and what is known about the port --}}
@php($muted = 'tw:text-gray-500 tw:dark:text-dark-white-400')
<div class="panel panel-default">
    <x-table class="tw:mb-0">
        <x-slot:head>
            <tr>
                @foreach(['ip' => __('search.list.host'), 'mac' => __('search.list.mac'), 'port' => __('search.list.switch_port'), 'seen' => __('search.list.last_seen')] as $key => $label)
                    @if($key === 'port')
                        <th>{{ $label }}</th>
                    @else
                        <th aria-sort="{{ $sort !== $key ? 'none' : ($direction === 'asc' ? 'ascending' : 'descending') }}">
                            <button type="button" class="tw:font-bold" @click="setSort('{{ $key }}')">{{ $label }}{{ $sort !== $key ? '' : ($direction === 'asc' ? ' ▲' : ' ▼') }}</button>
                        </th>
                    @endif
                @endforeach
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
                            <span class="{{ $muted }}">-</span>
                        @endif
                        @if($host->severalMacs)
                            <span class="label label-default tw:cursor-help" title="{{ __('search.host.several_macs_help') }}">{{ trans_choice('search.macs_count', count($host->macs)) }}</span>
                        @elseif($host->isNotSeen())
                            <span class="label label-default">{{ __('search.host.not_seen') }}</span>
                        @endif
                        @if(count($host->ips) > 1)
                            <span class="tw:text-sm {{ $muted }}">{{ __('search.host.more', ['count' => count($host->ips) - 1]) }}</span>
                        @endif
                        @if($host->interface)
                            <span class="tw:block tw:text-sm tw:whitespace-normal">@include('search.partials.port', ['port' => $host->interface])</span>
                        @elseif($host->device)
                            <span class="tw:block tw:text-sm">{!! \LibreNMS\Util\Url::modernDeviceLink($host->device) !!}</span>
                        @endif
                    </td>
                    <td class="tw:whitespace-nowrap">
                        @forelse(collect($host->macs)->take(3) as $mac)
                            <div @class(['tw:opacity-50' => $mac->stale])>@include('search.partials.mac', ['mac' => $mac->hex, 'inlineVendor' => true])</div>
                        @empty
                            <span class="{{ $muted }}">-</span>
                        @endforelse
                    </td>
                    <td>
                        @foreach($rows as $row)
                            <div @class(['tw:opacity-50' => $row->stale])>
                                @if($location = $row->location)
                                    @include('search.partials.port', ['port' => $location->port])
                                    @if($description = $location->portDescription())
                                        <span class="tw:text-sm {{ $muted }}">{{ $description }}</span>
                                    @endif
                                    <span @class([
                                        'label tw:cursor-help',
                                        'label-warning' => $location->state() === 'trunk',
                                        'label-success' => $location->state() === 'direct',
                                        'label-default' => $location->state() === 'shared',
                                    ]) title="{{ $location->isUplink() ? __('search.host.uplink') : __('search.list.macs_on_port') }}">{{ trans_choice('search.macs_count', $location->macCount) }}{{ $location->isUplink() ? ' ↑' : '' }}</span>
                                    @if($location->vlan)
                                        <span class="tw:text-sm tw:whitespace-nowrap {{ $muted }}">{{ __('search.list.vlan') }} {{ $location->vlan->vlan_vlan }}</span>
                                    @endif
                                @elseif($neighbor = $host->viaPort())
                                    <span class="{{ $muted }}" title="{{ __('search.list.via_help') }}">{{ __('search.list.via') }}</span>
                                    @include('search.partials.port', ['port' => $neighbor])
                                @else
                                    <span class="{{ $muted }}">-</span>
                                @endif
                            </div>
                        @endforeach
                    </td>
                    <td class="tw:whitespace-nowrap">
                        @foreach($rows as $row)
                            <div @class([$muted => ! $row->seen || $row->seen->gt(now()->subDay()), 'tw:text-amber-600 tw:dark:text-amber-300' => $row->seen && $row->seen->lte(now()->subDay())])>{{ $row->seen?->diffForHumans() ?? '-' }}</div>
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
