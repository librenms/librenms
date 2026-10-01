@extends('layouts.librenmsv1')

@section('title', __('map.custom.title.view', ['name' => $name]))

@section('content')
<div id="alert-row" class="tw:absolute tw:top-16 tw:left-1/2 tw:-translate-x-1/2 tw:z-50 tw:w-full tw:max-w-md tw:px-4">
  <div class="alert alert-warning tw:shadow-lg tw:mb-0" role="alert" id="alert">{{ __('map.custom.view.loading') }}</div>
</div>

<div id="map-container" class="tw:relative tw:w-full {{ request()->input('bare') == 'yes' ? 'tw:h-screen' : 'tw:h-[calc(100vh-62px)]' }} tw:min-h-[400px] tw:overflow-hidden">
  <div id="custom-map" class="tw:absolute tw:inset-0 tw:w-full tw:h-full tw:z-20"></div>
  <x-geo-map id="custom-map-bg-geo-map"
     class="tw:absolute tw:top-0 tw:left-0 tw:z-10 tw:origin-top-left"
     :init="$background_type == 'map'"
     :width="$map_conf['width']"
     :height="$map_conf['height']"
     :config="$background_config"
     readonly
  />
</div>
@endsection

@section('javascript')
<script type="text/javascript" src="{{ asset('js/vis-network.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/vis-data.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/leaflet.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/L.Control.Locate.min.js') }}"></script>
@endsection

@section('scripts')
@include('map.custom-js')
<script type="text/javascript">
    var mapViewer = custommap.initViewer({
        elementId: 'custom-map',
        containerId: 'map-container',
        mapId: {{ $map_id }},
        dataUrl: '{{ route('maps.custom.data', ['map' => $map_id]) }}',
        editUrl: '{{ route('maps.custom.edit', ['map' => $map_id]) }}',
        showUrlTemplate: '{{ route('maps.custom.show', ['map' => '?']) }}',
        bgType: {{ Js::from($background_type) }},
        bgData: {{ Js::from($background_config) }},
        reverseArrows: {{ $reverse_arrows ? 'true' : 'false' }},
        screenshot: {{ $screenshot ? 'true' : 'false' }},
        legend: @json($legend),
        networkOptions: {{ Js::from($map_conf) }},
        baseUrl: '{{ $base_url }}',
        enableKeyboard: true,
        autoRefresh: {{ (int) $page_refresh }},
        alertId: 'alert',
        alertRowId: 'alert-row'
    });
</script>
@endsection
