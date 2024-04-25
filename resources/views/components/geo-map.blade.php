@props([
    'id' => 'geo-map',
    'init' => true,
    'width' => '200px',
    'height' => '100px',
    'lat' => 40,
    'lng' => -20,
    'zoom' => 3,
    'readonly' => false,
    'config' => [
        'engine' => \LibreNMS\Config::get('geoloc.engine'),
        'api_key' => \LibreNMS\Config::get('geoloc.api_key'),
        'tile_url' => \LibreNMS\Config::get('leaflet.tile_url', '{s}.tile.openstreetmap.org'),
    ]
])

@php
    $config['readonly'] = $readonly;
@endphp

<div id="{{ $id }}" style="width: {{ $width }};height: {{ $height }}" {{ $attributes }}></div>

@if($init)
<script>
    loadjs('js/leaflet.js', function () {
        init_map(@json($id), @json($config))
    })
</script>
@endif
