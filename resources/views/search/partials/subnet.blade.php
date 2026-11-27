{{-- the host's gateways, then the other interfaces on its subnet, $limit in total --}}
@foreach($subnet->gateways->take($limit) as $gateway)
    <div class="{{ $itemClass }}" title="{{ $gateway->devices ? trans_choice('search.host.gateway_help', $gateway->devices) : __('search.host.gateway_router_help') }}">
        <span class="label label-info">{{ __('search.host.gateway') }}</span>
        @if($gateway->port)
            @include('search.partials.port', ['port' => $gateway->port])
        @endif
        <span @class(['tw:wrap-anywhere', 'tw:block tw:text-sm' => $gateway->port])>@include('search.partials.ip', ['ip' => $gateway->ip->compressed(), 'wrap' => true])</span>
    </div>
@endforeach
@foreach($subnet->peers->take(max(0, $limit - $subnet->gateways->count())) as $address)
    <div class="{{ $itemClass }}">
        @include('search.partials.port', ['port' => $address->port])
        <span class="tw:block tw:font-mono tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400">{{ $address->cidr() }}</span>
    </div>
@endforeach
