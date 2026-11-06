{{-- which host this is, the title of the results --}}
<div class="tw:flex tw:min-w-0 tw:flex-wrap tw:items-center tw:gap-2" x-data="searchHost({
    @if($nameUrl) nameUrl: @js($nameUrl), @endif
    hostname: @js($name),
    ip: @js(($host->ips[0] ?? null)?->compressed()),
    addDeviceUrl: @js(route('device.add')),
    messages: @js([
        'loading' => __('search.host.dns_loading'),
        'failed' => __('search.host.dns_failed'),
        'missing' => __('search.host.no_reverse_dns'),
        'match' => __('search.host.dns_match_help'),
        'mismatch' => __('search.host.dns_mismatch_help'),
    ]),
})">
    <span class="tw:inline-flex tw:w-6 tw:shrink-0 tw:items-center tw:justify-center">
        @if($device)
            <span class="tw:text-gray-500 tw:dark:text-dark-white-400 tw:cursor-help"
                  role="img" tabindex="0" title="{{ __('search.host.monitored') }}" aria-label="{{ __('search.host.monitored') }}">
                <i class="fa fa-server" aria-hidden="true"></i>
            </span>
        @else
            @can('create', App\Models\Device::class)
                <a class="btn btn-default btn-xs tw:shrink-0" title="{{ __('search.host.add_device') }}" aria-label="{{ __('search.host.add_device') }}"
                   href="{{ route('device.add', ['hostname' => $name ?? ($host->ips[0] ?? null)?->compressed()]) }}"
                   :href="addDeviceUrl"><i class="fa fa-plus" aria-hidden="true"></i></a>
            @endcan
        @endif
    </span>
    <h2 class="tw:my-0! tw:text-lg! tw:font-semibold! tw:wrap-anywhere"
        @if(! $name && ! $device && $nameUrl)
            x-text="confirmedName ?? @js($host->title())"
        @elseif(! $name && ! $device && ! $host->ips)
            x-text="formatMac(@js($host->macs[0]->hex))"
        @endif>
        @if($device)
            <a href="{{ route('device', ['device' => $device->device_id]) }}" x-data="deviceLink({device_id: {{ $device->device_id }}})">{{ $name ?? $device->display }}</a>
        @else
            {{ $name ?? $host->title() }}
        @endif
    </h2>
    @if($nameUrl)
        <span class="tw:inline-flex tw:w-6 tw:shrink-0 tw:items-center tw:justify-center tw:text-gray-500 tw:dark:text-dark-white-400 tw:cursor-help"
              role="img" tabindex="0" :aria-busy="dnsLoading" :title="dnsDescription" :aria-label="dnsDescription">
            <i class="fa" :class="dnsIcon" aria-hidden="true"></i>
        </span>
    @endif
    @if($host->severalMacs)
        <span class="label label-default tw:cursor-help" title="{{ __('search.host.several_macs_help') }}">{{ trans_choice('search.macs_count', count($host->macs)) }}</span>
    @endif
</div>
