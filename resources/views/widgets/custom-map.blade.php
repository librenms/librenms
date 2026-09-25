<div id="map-container-{{ $id }}" class="tw:relative tw:w-full tw:h-full tw:overflow-hidden" style="width: 100%; height: 100%">
  <div id="custom-map-{{ $id }}" class="tw:absolute tw:inset-0 tw:w-full tw:h-full tw:z-20"></div>
  <x-geo-map id="custom-map-{{ $id }}-bg-geo-map"
    class="tw:absolute tw:top-0 tw:left-0 tw:z-10 tw:origin-top-left"
    :init="$map->background_type == 'map'"
    :width="$map->width"
    :height="$map->height"
    :config="$background_config"
    readonly
  />
</div>

<script type="application/javascript">
    (function () {
        var widgetViewer = custommap.initViewer({
            elementId: 'custom-map-{{ $id }}',
            containerId: 'map-container-{{ $id }}',
            mapId: {{ $map->custom_map_id }},
            dataUrl: '{{ route('maps.custom.data', ['map' => $map->custom_map_id]) }}',
            editUrl: '{{ route('maps.custom.edit', ['map' => $map->custom_map_id]) }}',
            showUrlTemplate: '{{ route('maps.custom.show', ['map' => '?']) }}',
            bgType: {{ Js::from($map->background_type) }},
            bgData: {{ Js::from($background_config) }},
            reverseArrows: {{ $map->reverse_arrows ? 'true' : 'false' }},
            screenshot: {{ $screenshot ? 'true' : 'false' }},
            legend: @json($legend),
            networkOptions: {{ Js::from($map_conf) }},
            baseUrl: '{{ $base_url }}',
            enableKeyboard: false,
            isWidget: true
        });
    })();
</script>
