{{-- the answer for one host: where it is and what it is, each value in the same place on every search --}}
@php
    $panelClass = 'tw:mb-0! tw:min-w-0';
    $bodyClass = 'tw:p-3 tw:min-w-0';
    $muted = 'tw:text-gray-500 tw:dark:text-dark-white-400';
    $state = $host->locationState();
    // with no clear port every place it was seen is listed as an equal in the first panel
    $listedAsEquals = $state === 'trunk' && ! $host->severalMacs && ! $host->interface;
    $others = collect($host->interface ? $host->currentLocations() : $host->otherLocations());
    $neighbors = collect($host->neighborInterfaces());
    $showOthers = $others->isNotEmpty() && ! $host->severalMacs && ! $listedAsEquals;
@endphp
<div class="tw:grid tw:gap-4">
    <div class="tw:grid tw:grid-cols-1 tw:gap-4 tw:sm:grid-cols-2 tw:lg:grid-cols-[1.5fr_1fr_1fr_1fr]">
        {{-- where it is --}}
        @if($host->interface || ($host->device && ! $titleDevice))
            <x-device.overview.panel :title="$host->interface ? __('search.host.interface') : __('search.host.device')" :icon="$host->interface ? 'fa fa-network-wired' : 'fa fa-server'" class="{{ $panelClass }} tw:sm:col-span-2 tw:lg:col-span-1">
                <div class="{{ $bodyClass }} tw:flex tw:gap-2">
                    <span class="alert-status label-info tw:h-auto! tw:self-stretch tw:shrink-0"></span>
                    <div class="tw:min-w-0">
                        @if($host->interface)
                            <div class="tw:font-semibold">
                                @if($titleDevice)
                                    {!! \LibreNMS\Util\Url::modernPortLink($host->interface) !!}
                                @else
                                    @include('search.partials.port', ['port' => $host->interface])
                                @endif
                            </div>
                            @if($description = $host->interfaceDescription())
                                <span class="tw:block tw:text-sm {{ $muted }}">{{ $description }}</span>
                            @endif
                        @else
                            {!! \LibreNMS\Util\Url::modernDeviceLink($host->device) !!}
                        @endif
                    </div>
                </div>
            </x-device.overview.panel>
        @else
            <x-device.overview.panel :title="$state === 'trunk' ? __('search.host.seen_on') : __('search.host.switch_port')" icon="fa fa-ethernet" class="{{ $panelClass }} tw:sm:col-span-2 tw:lg:col-span-1">
                <div class="{{ $bodyClass }}">
                    @if($host->severalMacs)
                        @foreach($host->macs as $mac)
                            <div @class(['tw:mb-2 tw:last:mb-0', 'tw:opacity-50' => $mac->stale])>
                                @include('search.partials.location', ['location' => $host->bestLocation($mac->hex), 'bar' => $mac->stale ? 'label-default' : 'label-info', 'macLabel' => $mac->hex])
                            </div>
                        @endforeach
                    @elseif($listedAsEquals)
                        @foreach(collect($host->currentLocations())->take(5) as $location)
                            <div class="tw:mb-2 tw:last:mb-0">
                                @include('search.partials.location', ['location' => $location, 'bar' => 'label-warning'])
                            </div>
                        @endforeach
                        @if(count($host->currentLocations()) > 5)
                            <a href="{{ $locationsUrl }}" class="tw:text-sm">{{ __('search.host.more', ['count' => count($host->currentLocations()) - 5]) }}</a>
                        @endif
                    @else
                        @include('search.partials.location', ['location' => $host->bestLocation(), 'bar' => $state === 'direct' ? 'label-success' : ($state === 'shared' ? 'label-default' : 'label-warning')])
                    @endif
                </div>
            </x-device.overview.panel>
        @endif

        {{-- what it is --}}
        <x-device.overview.panel :title="count($host->macs) > 1 ? __('search.host.macs') : __('search.host.mac')" icon="fa fa-fingerprint" class="{{ $panelClass }}">
            <div class="{{ $bodyClass }}">
                @forelse($host->macs as $mac)
                    <div @class(['tw:mb-2 tw:last:mb-0', 'tw:opacity-50' => $mac->stale])>
                        @include('search.partials.mac', ['mac' => $mac->hex, 'primary' => true, 'copy' => true])
                    </div>
                @empty
                    <div class="{{ $muted }}">-</div>
                @endforelse
            </div>
        </x-device.overview.panel>

        <x-device.overview.panel :title="count($host->ips) > 1 ? __('search.host.ips') : __('search.host.ip')" icon="fa fa-location-dot" class="{{ $panelClass }}">
            <div class="{{ $bodyClass }}">
                @forelse(collect($host->ips)->take(5) as $ip)
                    <div class="tw:mb-2 tw:last:mb-0 tw:flex tw:items-start">
                        <span class="tw:min-w-0 tw:wrap-anywhere">@include('search.partials.ip', ['ip' => $ip->compressed(), 'primary' => true, 'wrap' => true])</span>
                        @include('search.partials.copy', ['js' => \Illuminate\Support\Js::from($ip->compressed())])
                    </div>
                @empty
                    <div class="{{ $muted }}">-</div>
                @endforelse
                @if(count($host->ips) > 5)
                    <span class="tw:text-sm {{ $muted }}">{{ __('search.host.more', ['count' => count($host->ips) - 5]) }}</span>
                @endif
            </div>
        </x-device.overview.panel>

        <x-device.overview.panel :title="__('search.host.same_subnet')" icon="fa fa-sitemap" class="{{ $panelClass }} tw:sm:col-span-2 tw:lg:col-span-1">
            <div class="{{ $bodyClass }}">
                @include('search.partials.subnet', ['subnet' => $subnet, 'limit' => 3, 'itemClass' => 'tw:mb-2 tw:last:mb-0'])
                @if($subnet->count() === 0)
                    <div class="{{ $muted }}">-</div>
                @endif
                @if($subnet->count() > 3)
                    @include('search.partials.subnet-more', ['subnet' => $subnet, 'limit' => 3])
                @endif
            </div>
        </x-device.overview.panel>
    </div>

    @if($showOthers || $neighbors->isNotEmpty())
        <div class="tw:grid tw:grid-cols-1 tw:gap-4 {{ $showOthers && $neighbors->isNotEmpty() ? 'tw:sm:grid-cols-2' : '' }}">
            @if($showOthers)
                <x-device.overview.panel :title="__('search.host.also_learned')" icon="fa fa-plug" class="{{ $panelClass }}">
                    <div class="{{ $bodyClass }}">
                        @foreach($others->take(5) as $location)
                            <div class="tw:mb-1 tw:last:mb-0">
                                @include('search.partials.port', ['port' => $location->port])
                                @if($location->isUplink())
                                    <span class="tw:text-amber-600 tw:dark:text-amber-300 tw:font-bold" title="{{ __('search.host.uplink') }}">↑</span>
                                @endif
                                <span class="tw:text-sm {{ $muted }}">{{ trans_choice('search.macs_count', $location->macCount) }} · {{ $location->updatedAt?->diffForHumans() }}</span>
                            </div>
                        @endforeach
                        @if($others->count() > 5)
                            <a href="{{ $locationsUrl }}" class="tw:text-sm">{{ __('search.host.more', ['count' => $others->count() - 5]) }}</a>
                        @endif
                    </div>
                </x-device.overview.panel>
            @endif
            @if($neighbors->isNotEmpty())
                <div class="tw:grid tw:min-w-0" title="{{ __('search.host.neighbor_of_help') }}">
                    <x-device.overview.panel :title="__('search.host.neighbor_of')" icon="fa fa-circle-nodes" class="{{ $panelClass }}">
                        <div class="{{ $bodyClass }}">
                            @foreach($neighbors->take(6) as $entries)
                                <div class="tw:mb-1 tw:last:mb-0">
                                    @include('search.partials.port', ['port' => $entries[0]->port])
                                    @foreach(collect($entries)->take(4) as $entry)
                                        <span @class(['tw:whitespace-nowrap tw:ml-1', 'tw:opacity-50' => $host->isStale($entry->mac)])>
                                            <span class="label label-default" title="{{ __('search.host.neighbor_table', ['table' => $entry->source]) }}">{{ $entry->source }}</span>
                                            @if($host->severalMacs)
                                                <span class="tw:font-mono tw:text-sm {{ $muted }}" x-text="formatMac(@js($entry->mac))"></span>
                                            @elseif(count($host->ips) > 1)
                                                <span class="tw:font-mono tw:text-sm {{ $muted }}">{{ $entry->ip->compressed() }}</span>
                                            @endif
                                        </span>
                                    @endforeach
                                    @if(count($entries) > 4)
                                        <span class="tw:text-sm {{ $muted }}">{{ __('search.host.more', ['count' => count($entries) - 4]) }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </x-device.overview.panel>
                </div>
            @endif
        </div>
    @endif
</div>
