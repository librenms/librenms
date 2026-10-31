<table class="table table-condensed table-hover tw:mb-0">
    <thead>
    <tr>
        <th>{{ __('search.columns.address') }}</th>
        <th>{{ __('search.columns.network') }}</th>
        <th>{{ __('search.columns.device') }} / {{ __('search.columns.interface') }}</th>
        <th>{{ __('search.columns.description') }}</th>
        <th>{{ __('search.columns.context') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($results as $address)
        @php($isIpv4 = $address instanceof \App\Models\Ipv4Address)
        @php($network = $isIpv4 ? $address->network?->ipv4_network : $address->network?->ipv6_network)
        <tr>
            <td>@include('search.partials.ip', ['ip' => $isIpv4 ? $address->ipv4_address : $address->ipv6_address, 'prefix' => $isIpv4 ? $address->ipv4_prefixlen : $address->ipv6_prefixlen])</td>
            <td>
                @if($network)
                    <a href="{{ route('search', ['q' => $network]) }}" class="tw:font-mono" title="{{ __('Search this network') }}">{{ $network }}</a>
                @endif
            </td>
            <td>@include('search.partials.port', ['port' => $address->port])</td>
            <td>{{ $address->port && $address->port->ifAlias != $address->port->getLabel() ? $address->port->ifAlias : '' }}</td>
            <td>{{ $address->context_name }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
