@props([
    'id' => 'geo-map',
    'init' => true,
    'width' => '200px',
    'height' => '100px',
    'lat' => null,
    'lng' => null,
    'zoom' => null,
    'layer' => null,
    'readonly' => false,
    'config' => [],
])

@php
    $config['readonly'] = $readonly;
    $config['lat'] = $lat ?? $config['lat'] ?? 40;
    $config['lng'] = $lng ?? $config['lng'] ?? 40;
    $config['zoom'] = $zoom ?? $config['zoom'] ?? 3;
    $config['layer'] = $layer ?? $config['layer'] ?? null;
    $config['engine'] ??= \LibreNMS\Config::get('geoloc.engine');
    $config['api_key'] ??= \LibreNMS\Config::get('geoloc.api_key');
    $config['tile_url'] ??= \LibreNMS\Config::get('leaflet.tile_url', '{s}.tile.openstreetmap.org');
@endphp

<div id="{{ $id }}" style="width: {{ $width }};height: {{ $height }}" {{ $attributes }}></div>

@if($init)
<script>
    loadjs('js/leaflet.js', function () {
        init_map(@json($id), @json($config))
    })
</script>
@endif
