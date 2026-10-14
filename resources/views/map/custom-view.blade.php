@extends('layouts.librenmsv1')

@section('title', __('map.custom.title.view', ['name' => $name]))

@section('content')
<div id="alert-row" class="tw:absolute tw:top-16 tw:left-1/2 tw:-translate-x-1/2 tw:z-50 tw:w-full tw:max-w-md tw:px-4">
  <div class="alert alert-warning tw:shadow-lg tw:mb-0" role="alert" id="alert">{{ __('map.custom.view.loading') }}</div>
</div>

<div id="map-container" class="tw:relative tw:w-full {{ request()->input('bare') == 'yes' ? 'tw:h-screen' : 'tw:h-[calc(100vh-62px)]' }} tw:min-h-[400px] tw:overflow-hidden" x-data="customViewData{{ $map_id }}()">
  <x-modal show="showHelp" maxWidth="sm">
    <x-slot name="heading">
      <h4 class="tw:m-0 tw:text-base tw:font-semibold tw:flex tw:items-center tw:gap-2">
        <i class="fa fa-keyboard-o tw:text-blue-500" aria-hidden="true"></i>
        {{ __('Help & Shortcuts') }}
      </h4>
    </x-slot>
    <div>
      <h5 class="tw:font-semibold tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400 tw:mb-2">
        {{ __('Pan and Zoom') }}
      </h5>
      <div class="tw:grid tw:grid-cols-1 tw:gap-2">
        <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Move map') }}</span>
          <div class="tw:flex tw:items-center tw:gap-1.5">
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">↑</kbd>
            <span class="tw:text-gray-400 tw:dark:text-dark-white-400">/</span>
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">↓</kbd>
            <span class="tw:text-gray-400 tw:dark:text-dark-white-400">/</span>
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">←</kbd>
            <span class="tw:text-gray-400 tw:dark:text-dark-white-400">/</span>
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">→</kbd>
          </div>
        </div>
        <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Fit to Window') }}</span>
          <div class="tw:flex tw:items-center tw:gap-1.5">
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">F</kbd>
            <span class="tw:text-gray-400 tw:dark:text-dark-white-400">/</span>
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">0</kbd>
            <span class="tw:text-gray-400 tw:dark:text-dark-white-400">/</span>
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">Home</kbd>
          </div>
        </div>
        <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Fill Window') }}</span>
          <div class="tw:flex tw:items-center tw:gap-1.5">
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">L</kbd>
          </div>
        </div>
        <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Original Size in Editor') }}</span>
          <div class="tw:flex tw:items-center tw:gap-1.5">
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">1</kbd>
          </div>
        </div>
        <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Zoom In') }}</span>
          <div class="tw:flex tw:items-center tw:gap-1.5">
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">+</kbd>
          </div>
        </div>
        <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Zoom Out') }}</span>
          <div class="tw:flex tw:items-center tw:gap-1.5">
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">-</kbd>
          </div>
        </div>
      </div>
      <h5 class="tw:font-semibold tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400 tw:mb-2">
        {{ __('General') }}
      </h5>
      <div class="tw:grid tw:grid-cols-1 tw:gap-2">
        <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Open this help') }}</span>
          <div class="tw:flex tw:items-center tw:gap-1.5">
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">?</kbd>
          </div>
        </div>
        <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Close help or other menu') }}</span>
          <div class="tw:flex tw:items-center tw:gap-1.5">
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">Esc</kbd>
          </div>
        </div>
      </div>
    </div>
  </x-modal>
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

    document.addEventListener('alpine:init', () => {
        Alpine.data('customViewData{{ $map_id }}', () => ({
            showHelp: false,

            toggleHelp() {
                this.showHelp = !this.showHelp;
            }
        }));
    });
</script>
@endsection
