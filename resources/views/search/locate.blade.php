@php($primary = $locations->first(fn ($fdb) => ! $fdb->port?->links_count))
<div class="panel panel-default tw:border-l-4! tw:border-l-blue-500!">
    <div class="panel-heading tw:flex tw:flex-wrap tw:items-center tw:gap-2">
        <i class="fa fa-crosshairs fa-fw fa-lg" aria-hidden="true"></i>
        <strong>{{ __('search.locate.title') }}</strong>
        <span class="tw:font-mono">{{ $hostname ?? $term->value }}</span>
        @foreach($dnsNames as $dnsName)
            <x-search.badge color="blue" title="{{ __('search.locate.dns') }}">{{ $dnsName }}</x-search.badge>
        @endforeach
    </div>
    <div class="panel-body">
        @if($bindings->isEmpty() && $locations->isEmpty() && $devices->isEmpty() && $owners->isEmpty() && $subnets->isEmpty())
            <p class="tw:mb-0 tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('search.locate.not_found') }}</p>
        @endif

        <dl class="tw:grid tw:grid-cols-1 tw:md:grid-cols-[max-content_1fr] tw:gap-x-6 tw:gap-y-2 tw:mb-0">
            @if($ips->isNotEmpty())
                <dt class="tw:text-gray-500 tw:dark:text-dark-white-400 tw:font-normal">{{ $hostname ? __('search.locate.resolved', ['hostname' => $hostname]) : __('search.locate.ips') }}</dt>
                <dd class="tw:flex tw:flex-wrap tw:gap-x-3">
                    @foreach($ips->take(10) as $ip)
                        @include('search.partials.ip', ['ip' => $ip->compressed()])
                    @endforeach
                </dd>
            @endif

            @if($macs->isNotEmpty())
                <dt class="tw:text-gray-500 tw:dark:text-dark-white-400 tw:font-normal">{{ __('search.locate.macs') }}</dt>
                <dd class="tw:flex tw:flex-wrap tw:gap-x-4">
                    @foreach($macs->take(10) as $hex => $mac)
                        <span>@include('search.partials.mac', ['mac' => $hex])</span>
                    @endforeach
                </dd>
            @endif

            @if($devices->isNotEmpty())
                <dt class="tw:text-gray-500 tw:dark:text-dark-white-400 tw:font-normal">{{ __('search.locate.monitored') }}</dt>
                <dd class="tw:flex tw:flex-wrap tw:gap-x-3">
                    @foreach($devices as $device)
                        <span>{!! \LibreNMS\Util\Url::modernDeviceLink($device) !!}</span>
                    @endforeach
                </dd>
            @endif

            @if($owners->isNotEmpty())
                <dt class="tw:text-gray-500 tw:dark:text-dark-white-400 tw:font-normal">{{ __('search.locate.owner') }}</dt>
                <dd>
                    @foreach($owners->take(5) as $owner)
                        <div>@include('search.partials.port', ['port' => $owner])</div>
                    @endforeach
                </dd>
            @endif

            @if($primary)
                <dt class="tw:text-gray-500 tw:dark:text-dark-white-400 tw:font-normal" title="{{ __('search.locate.connected_help') }}">
                    {{ (int) $primary->mac_count === 1 ? __('search.locate.connected') : __('search.locate.closest') }} <i class="fa fa-question-circle" aria-hidden="true"></i>
                </dt>
                <dd class="tw:font-semibold">
                    @include('search.partials.fdb-location', ['fdb' => $primary])
                    <div class="tw:text-xs tw:font-normal tw:text-gray-500 tw:dark:text-dark-white-400">
                        @include('search.partials.mac', ['mac' => $primary->mac_address])
                        · {{ __('search.columns.last_seen') }} {{ $primary->updated_at?->diffForHumans() }}
                    </div>
                </dd>
            @endif

            @php($others = $locations->reject(fn ($fdb) => $fdb === $primary))
            @if($others->isNotEmpty())
                <dt class="tw:text-gray-500 tw:dark:text-dark-white-400 tw:font-normal">{{ __('search.locate.also_seen') }}</dt>
                <dd>
                    @foreach($others->take(5) as $fdb)
                        @include('search.partials.fdb-location', ['fdb' => $fdb])
                    @endforeach
                    @if($others->count() > 5)
                        <div class="tw:text-xs tw:text-gray-500">{{ __('search.locate.more', ['count' => $others->count() - 5]) }}</div>
                    @endif
                </dd>
            @endif

            @if($subnets->isNotEmpty())
                <dt class="tw:text-gray-500 tw:dark:text-dark-white-400 tw:font-normal">{{ __('search.locate.gateways') }}</dt>
                <dd>
                    @foreach($subnets->take(5) as $address)
                        @php($isIpv4 = $address instanceof \App\Models\Ipv4Address)
                        <div class="tw:flex tw:flex-wrap tw:gap-x-2">
                            @include('search.partials.ip', ['ip' => $isIpv4 ? $address->ipv4_address : $address->ipv6_address, 'prefix' => $isIpv4 ? $address->ipv4_prefixlen : $address->ipv6_prefixlen])
                            @include('search.partials.port', ['port' => $address->port])
                        </div>
                    @endforeach
                </dd>
            @endif

            @if($bindings->isNotEmpty())
                <dt class="tw:text-gray-500 tw:dark:text-dark-white-400 tw:font-normal">{{ __('search.locate.bindings') }}</dt>
                <dd>
                    @php($bindings = $bindings->unique(fn ($binding) => $binding['port']?->port_id . $binding['ip']->compressed()))
                    @foreach($bindings->take(5) as $binding)
                        <div class="tw:flex tw:flex-wrap tw:gap-x-2">
                            @include('search.partials.port', ['port' => $binding['port']])
                            <span class="tw:text-gray-500">
                                @include('search.partials.ip', ['ip' => $binding['ip']->compressed()]) → {{ \LibreNMS\Util\Mac::parse($binding['mac'])->readable() }}
                            </span>
                        </div>
                    @endforeach
                    @if($bindings->count() > 5)
                        <div class="tw:text-xs tw:text-gray-500">{{ __('search.locate.more', ['count' => $bindings->count() - 5]) }}</div>
                    @endif
                </dd>
            @endif
        </dl>
    </div>
</div>
