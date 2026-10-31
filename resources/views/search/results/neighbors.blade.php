{{-- ARP and IPv6 neighbor discovery entries --}}
<table class="table table-condensed table-hover tw:mb-0">
    <thead>
    <tr>
        <th>{{ __('search.columns.address') }}</th>
        <th>{{ __('search.columns.mac') }}</th>
        <th>{{ __('search.columns.seen_on') }}</th>
        <th>{{ __('search.columns.owner') }}</th>
        <th>{{ __('search.columns.switch_port') }}</th>
        <th>{{ __('search.columns.context') }}</th>
        @if($addressColumn == 'ipv6_address')
            <th>{{ __('search.columns.last_seen') }}</th>
        @endif
    </tr>
    </thead>
    <tbody>
    @foreach($results as $entry)
        @php($location = $locations->get($entry->mac_address)?->first())
        @php($isAccessPort = $location && ! $location->port?->links_count && (int) $location->mac_count === 1)
        <tr>
            <td @class(['tw:border-l-2! tw:border-l-green-500/70!' => $isAccessPort])>@include('search.partials.ip', ['ip' => $entry->$addressColumn, 'primary' => true])</td>
            <td>@include('search.partials.mac', ['mac' => $entry->mac_address])</td>
            <td>@include('search.partials.port', ['port' => $entry->port ?? null, 'dimDevice' => $entry->device_id === ($previous ?? null)])</td>
            <td>@include('search.partials.owners', ['owners' => $owners->get($entry->mac_address), 'seenOn' => $entry->port_id, 'dimDeviceId' => $previousOwner ?? null])</td>
            <td>
                @if($location)
                    @include('search.partials.fdb-location', ['fdb' => $location])
                @endif
            </td>
            <td class="tw:text-gray-500 tw:dark:text-dark-white-400">{{ $entry->context_name }}</td>
            @if($addressColumn == 'ipv6_address')
                <td class="tw:whitespace-nowrap tw:text-gray-500 tw:dark:text-dark-white-400" title="{{ $entry->updated_at }}">{{ $entry->updated_at?->diffForHumans() }}</td>
            @endif
        </tr>
        @php($previous = $entry->device_id)
        @php($previousOwner = $owners->get($entry->mac_address)?->first()?->device_id)
    @endforeach
    </tbody>
</table>
