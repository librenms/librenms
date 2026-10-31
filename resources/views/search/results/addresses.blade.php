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
            <td>@include('search.partials.ip', ['ip' => $isIpv4 ? $address->ipv4_address : $address->ipv6_address, 'prefix' => $isIpv4 ? $address->ipv4_prefixlen : $address->ipv6_prefixlen, 'primary' => true])</td>
            <td>
                @if($network)
                    <a href="{{ route('search', ['q' => $network]) }}" class="tw:font-mono tw:text-gray-500 tw:dark:text-dark-white-400" title="{{ __('Search this network') }}">{{ $network }}</a>
                @endif
            </td>
            <td>@include('search.partials.port', ['port' => $address->port, 'dimDevice' => $address->port?->device_id === ($previous ?? null)])</td>
            <td class="tw:text-gray-500 tw:dark:text-dark-white-400">{{ $address->port && $address->port->ifAlias != $address->port->getLabel() ? $address->port->ifAlias : '' }}</td>
            <td class="tw:text-gray-500 tw:dark:text-dark-white-400">{{ $address->context_name }}</td>
        </tr>
        @php($previous = $address->port?->device_id)
    @endforeach
    </tbody>
</table>
