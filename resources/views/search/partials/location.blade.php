{{-- a forwarding table entry: the port and the facts that say how close it is to the host, $bar is the status colour, $macLabel says which MAC when there are several --}}
<div class="tw:flex tw:gap-2">
    <span class="alert-status {{ $bar }} tw:h-auto! tw:self-stretch tw:shrink-0"></span>
    <div class="tw:min-w-0">
        @if($macLabel ?? null)
            <div class="tw:font-mono tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400" x-text="formatMac(@js($macLabel))"></div>
        @endif
        @if($location)
            <div class="tw:font-semibold">
                @include('search.partials.port', ['port' => $location->port])
                @if($location->isUplink())
                    <span class="tw:text-amber-600 tw:dark:text-amber-300 tw:font-bold" title="{{ __('search.host.uplink') }}">↑</span>
                @endif
            </div>
            <span class="tw:block tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400">
                {{ collect([
                    $location->vlan ? __('search.list.vlan') . ' ' . $location->vlan->vlan_vlan : null,
                    trans_choice('search.macs_count', $location->macCount),
                    $location->portDescription(),
                    $location->updatedAt?->diffForHumans(),
                ])->filter()->implode(' · ') }}
            </span>
        @else
            <span class="tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('search.host.no_forwarding_entry') }}</span>
        @endif
    </div>
</div>
