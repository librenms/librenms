{{-- the answer for one host: where it is and what it is, each value in the same place on every search --}}
<div class="tw:grid tw:gap-4">
    <div class="tw:grid tw:grid-cols-1 tw:gap-4 tw:sm:grid-cols-2 tw:lg:grid-cols-[1.5fr_1fr_1fr_1fr]">
        {{-- where it is --}}
        @if($host->interface || ($host->device && ! $titleDevice))
            <x-device.overview.panel :title="$host->interface ? __('search.host.interface') : __('search.host.device')" :icon="$host->interface ? 'fa fa-network-wired' : 'fa fa-server'" class="tw:mb-0! tw:min-w-0 tw:sm:col-span-2 tw:lg:col-span-1">
                <div class="tw:p-3 tw:min-w-0 tw:flex tw:gap-2">
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
                                <span class="tw:block tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400">{{ $description }}</span>
                            @endif
                        @else
                            {!! \LibreNMS\Util\Url::modernDeviceLink($host->device) !!}
                        @endif
                    </div>
                </div>
            </x-device.overview.panel>
        @else
            <x-device.overview.panel :title="$host->locationState() === 'trunk' ? __('search.host.seen_on') : __('search.host.switch_port')" icon="fa fa-ethernet" class="tw:mb-0! tw:min-w-0 tw:sm:col-span-2 tw:lg:col-span-1">
                <div class="tw:p-3 tw:min-w-0">
                    @if($host->severalMacs)
                        @foreach($host->macs as $mac)
                            <div @class(['tw:mb-2 tw:last:mb-0', 'tw:opacity-50' => $mac->stale])>
                                @include('search.partials.location', ['location' => $host->bestLocation($mac->hex), 'bar' => $mac->stale ? 'label-default' : 'label-info', 'macLabel' => $mac->hex])
                            </div>
                        @endforeach
                    @elseif($host->hasNoClearPort())
                        @foreach(collect($host->currentLocations())->take(5) as $location)
                            <div class="tw:mb-2 tw:last:mb-0">
                                @include('search.partials.location', ['location' => $location, 'bar' => 'label-warning'])
                            </div>
                        @endforeach
                        @if(count($host->currentLocations()) > 5)
                            <a href="{{ $locationsUrl }}" class="tw:text-sm">{{ __('search.host.more', ['count' => count($host->currentLocations()) - 5]) }}</a>
                        @endif
                    @else
                        @include('search.partials.location', ['location' => $host->bestLocation(), 'bar' => $host->locationState() === 'direct' ? 'label-success' : ($host->locationState() === 'shared' ? 'label-default' : 'label-warning')])
                    @endif
                </div>
            </x-device.overview.panel>
        @endif

        {{-- what it is --}}
        <x-device.overview.panel :title="count($host->macs) > 1 ? __('search.host.macs') : __('search.host.mac')" icon="fa fa-fingerprint" class="tw:mb-0! tw:min-w-0">
            <div class="tw:p-3 tw:min-w-0">
                @forelse($host->macs as $mac)
                    <div @class(['tw:mb-2 tw:last:mb-0', 'tw:opacity-50' => $mac->stale])>
                        @include('search.partials.mac', ['mac' => $mac->hex, 'primary' => true, 'copy' => true])
                    </div>
                @empty
                    <div class="tw:text-gray-500 tw:dark:text-dark-white-400">-</div>
                @endforelse
            </div>
        </x-device.overview.panel>

        <x-device.overview.panel :title="count($host->ips) > 1 ? __('search.host.ips') : __('search.host.ip')" icon="fa fa-location-dot" class="tw:mb-0! tw:min-w-0">
            <div class="tw:p-3 tw:min-w-0">
                @forelse(collect($host->ips)->take(5) as $ip)
                    <div class="tw:mb-2 tw:last:mb-0 tw:flex tw:items-start">
                        <span class="tw:min-w-0 tw:wrap-anywhere">@include('search.partials.ip', ['ip' => $ip->compressed(), 'primary' => true, 'wrap' => true])</span>
                        @include('search.partials.copy', ['js' => \Illuminate\Support\Js::from($ip->compressed())])
                    </div>
                @empty
                    <div class="tw:text-gray-500 tw:dark:text-dark-white-400">-</div>
                @endforelse
                @if(count($host->ips) > 5)
                    <span class="tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('search.host.more', ['count' => count($host->ips) - 5]) }}</span>
                @endif
            </div>
        </x-device.overview.panel>

        <x-device.overview.panel :title="__('search.host.same_subnet')" icon="fa fa-sitemap" class="tw:mb-0! tw:min-w-0 tw:sm:col-span-2 tw:lg:col-span-1">
            <div class="tw:p-3 tw:min-w-0">
                @include('search.partials.subnet', ['subnet' => $subnet, 'limit' => 3, 'itemClass' => 'tw:mb-2 tw:last:mb-0'])
                @if($subnet->count() === 0)
                    <div class="tw:text-gray-500 tw:dark:text-dark-white-400">-</div>
                @endif
                @if($subnet->count() > 3)
                    @include('search.partials.subnet-more', ['subnet' => $subnet, 'limit' => 3])
                @endif
            </div>
        </x-device.overview.panel>
    </div>

    @if($host->showsAlsoLearned() || $host->neighborInterfaces())
        <div class="tw:grid tw:grid-cols-1 tw:gap-4 {{ $host->showsAlsoLearned() && $host->neighborInterfaces() ? 'tw:sm:grid-cols-2' : '' }}">
            @if($host->showsAlsoLearned())
                <x-device.overview.panel :title="__('search.host.also_learned')" icon="fa fa-plug" class="tw:mb-0! tw:min-w-0">
                    <div class="tw:p-3 tw:min-w-0">
                        @foreach(collect($host->alsoLearnedOn())->take(5) as $location)
                            <div class="tw:mb-1 tw:last:mb-0">
                                @include('search.partials.port', ['port' => $location->port])
                                @if($location->isUplink())
                                    <span class="tw:text-amber-600 tw:dark:text-amber-300 tw:font-bold" title="{{ __('search.host.uplink') }}">↑</span>
                                @endif
                                <span class="tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400">{{ trans_choice('search.macs_count', $location->macCount) }} · {{ $location->updatedAt?->diffForHumans() }}</span>
                            </div>
                        @endforeach
                        @if(count($host->alsoLearnedOn()) > 5)
                            <a href="{{ $locationsUrl }}" class="tw:text-sm">{{ __('search.host.more', ['count' => count($host->alsoLearnedOn()) - 5]) }}</a>
                        @endif
                    </div>
                </x-device.overview.panel>
            @endif
            @if($host->neighborInterfaces())
                <div class="tw:grid tw:min-w-0" title="{{ __('search.host.neighbor_of_help') }}">
                    <x-device.overview.panel :title="__('search.host.neighbor_of')" icon="fa fa-circle-nodes" class="tw:mb-0! tw:min-w-0">
                        <div class="tw:p-3 tw:min-w-0">
                            @foreach(collect($host->neighborInterfaces())->take(6) as $entries)
                                <div class="tw:mb-1 tw:last:mb-0">
                                    @include('search.partials.port', ['port' => $entries[0]->port])
                                    @foreach(collect($entries)->take(4) as $entry)
                                        <span @class(['tw:whitespace-nowrap tw:ml-1', 'tw:opacity-50' => $host->isStale($entry->mac)])>
                                            <span class="label label-default" title="{{ __('search.host.neighbor_table', ['table' => $entry->source]) }}">{{ $entry->source }}</span>
                                            @if($host->severalMacs)
                                                <span class="tw:font-mono tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400" x-text="formatMac(@js($entry->mac))"></span>
                                            @elseif(count($host->ips) > 1)
                                                <span class="tw:font-mono tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400">{{ $entry->ip->compressed() }}</span>
                                            @endif
                                        </span>
                                    @endforeach
                                    @if(count($entries) > 4)
                                        <span class="tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('search.host.more', ['count' => count($entries) - 4]) }}</span>
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
