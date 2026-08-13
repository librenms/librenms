<x-device.overview.panel :title="$title" icon="fa fa-id-card">
    <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
        @foreach($rows as $row)
            <div class="tw:grid tw:grid-cols-3 tw:items-center tw:gap-2.5 tw:px-2 tw:py-1 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                <div class="tw:font-medium">{{ $row['label'] }}</div>
                <div class="tw:col-span-2 tw:min-w-0 tw:break-words">{{ $row['value'] }}</div>
            </div>
        @endforeach
        @if($device->inserted)
            <div class="tw:grid tw:grid-cols-3 tw:gap-2.5 tw:px-2 tw:py-1 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                <div class="tw:font-medium">{{ __('Device Added') }}</div>
                <div class="tw:col-span-2" title="{{ $device->inserted }}">{{ $device->inserted->diffForHumans() }}</div>
            </div>
        @endif
        @if($device->last_discovered)
            <div class="tw:grid tw:grid-cols-3 tw:gap-2.5 tw:px-2 tw:py-1 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                <div class="tw:font-medium">{{ __('Last Discovered') }}</div>
                <div class="tw:col-span-2" title="{{ $device->last_discovered }}">{{ $device->last_discovered->diffForHumans() }}</div>
            </div>
        @endif
        @if($device->location)
            <div class="tw:grid tw:grid-cols-3 tw:gap-2.5 tw:px-2 tw:py-1 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                <div class="tw:font-medium">{{ __('Location') }}</div>
                <div class="tw:col-span-2">{{ $device->location->display() }}</div>
            </div>
            @if($showMap)
                <div class="tw:grid tw:grid-cols-3 tw:items-center tw:gap-2.5 tw:px-2 tw:py-1 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300" id="coordinates-row" data-toggle="collapse" data-target="#toggle-map">
                    <div class="tw:font-medium">{{ __('Lat / Lng') }}</div>
                    <div class="tw:col-span-2 tw:flex tw:items-center tw:justify-between tw:gap-2">
                        <span id="coordinates-text">{{ $device->location->lat }}, {{ $device->location->lng }}</span>
                        <div class="btn-group" role="group" aria-label="Map actions">
                            <button type="button" id="toggle-map-button" class="btn btn-primary btn-xs" data-toggle="collapse" data-target="#toggle-map">
                                <i class="fa {{ $mapOpen ? 'fa-map-o' : 'fa-map' }}" style="color:white" aria-hidden="true"></i>
                                <span>{{ $mapOpen ? __('Hide') : __('View') }}</span>
                            </button>
                            <a id="map-it-button" href="https://maps.google.com/?q={{ $device->location->lat }},{{ $device->location->lng }}" target="_blank" class="btn btn-success btn-xs" role="button">
                                <i class="fa fa-map-marker" style="color:white" aria-hidden="true"></i> {{ __('Map') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div id="toggle-map" class="collapse {{ $mapOpen ? 'in' : '' }}">
                    <x-geo-map
                        id="location-map"
                        width="100%"
                        height="280px"
                        :lat="$device->location->lat"
                        :lng="$device->location->lng"
                        :init="false"
                        fullscreen
                        marker
                        :show-devices="$showDevices"
                        :show-dependencies="$showDependencies"
                        :editable-location-id="$canUpdateLocation ? $device->location->id : null"
                    />
                </div>
                <script>
                    $("#toggle-map").on("shown.bs.collapse", function () {
                        if (typeof init_geo_map_location_map === 'function') {
                            init_geo_map_location_map();
                        }
                        if (window.maps && window.maps['location-map']) {
                            window.maps['location-map'].invalidateSize();
                        }
                        $("#toggle-map-button").find(".fa").removeClass("fa-map").addClass("fa-map-o");
                        $("#toggle-map-button span").text("{{ __('Hide') }}");
                    }).on("hidden.bs.collapse", function () {
                        $("#toggle-map-button").find(".fa").removeClass("fa-map-o").addClass("fa-map");
                        $("#toggle-map-button span").text("{{ __('View') }}");
                    });

                    @if($mapOpen)
                        $("#toggle-map").trigger("shown.bs.collapse");
                    @endif
                </script>
            @endif
        @endif
    </div>
</x-device.overview.panel>
