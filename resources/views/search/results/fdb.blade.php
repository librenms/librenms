<table class="table table-condensed table-hover tw:mb-0">
    <thead>
    <tr>
        <th>{{ __('search.columns.mac') }}</th>
        <th>{{ __('search.columns.addresses') }}</th>
        <th>{{ __('search.columns.switch_port') }}</th>
        <th>{{ __('search.columns.description') }}</th>
        <th>{{ __('search.columns.owner') }}</th>
        <th>{{ __('search.columns.first_seen') }}</th>
        <th>{{ __('search.columns.last_seen') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($results as $fdb)
        <tr>
            <td>@include('search.partials.mac', ['mac' => $fdb->mac_address])</td>
            <td>
                @foreach($addresses->get($fdb->mac_address, collect())->take(4) as $ip)
                    <div>@include('search.partials.ip', ['ip' => $ip])</div>
                @endforeach
                @if($addresses->get($fdb->mac_address, collect())->count() > 4)
                    <div class="tw:text-xs tw:text-gray-500">{{ __('search.locate.more', ['count' => $addresses->get($fdb->mac_address)->count() - 4]) }}</div>
                @endif
            </td>
            <td>@include('search.partials.fdb-location', ['fdb' => $fdb])</td>
            <td>{{ $fdb->port?->ifAlias }}</td>
            <td>@include('search.partials.owners', ['owners' => $owners->get($fdb->mac_address), 'seenOn' => $fdb->port_id])</td>
            <td class="tw:whitespace-nowrap">{{ $fdb->created_at?->toDateString() }}</td>
            <td class="tw:whitespace-nowrap" title="{{ $fdb->updated_at }}">{{ $fdb->updated_at?->diffForHumans() }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
