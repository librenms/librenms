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
        <tr>
            <td>@include('search.partials.ip', ['ip' => $entry->$addressColumn])</td>
            <td>@include('search.partials.mac', ['mac' => $entry->mac_address])</td>
            <td>@include('search.partials.port', ['port' => $entry->port ?? null])</td>
            <td>@include('search.partials.owners', ['owners' => $owners->get($entry->mac_address), 'seenOn' => $entry->port_id])</td>
            <td>
                @if($location = $locations->get($entry->mac_address)?->first())
                    @include('search.partials.fdb-location', ['fdb' => $location])
                @endif
            </td>
            <td>{{ $entry->context_name }}</td>
            @if($addressColumn == 'ipv6_address')
                <td class="tw:whitespace-nowrap" title="{{ $entry->updated_at }}">{{ $entry->updated_at?->diffForHumans() }}</td>
            @endif
        </tr>
    @endforeach
    </tbody>
</table>
