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
    'fullscreen' => false,
    'marker' => false,
    'showDevices' => false,
    'showDependencies' => false,
    'editableLocationId' => null,
    'config' => [],
])

@php
    $config['readonly'] = $readonly && ! $fullscreen && ! $editableLocationId && ! $showDevices;
    $config['lat'] = $lat ?? $config['lat'] ?? 40;
    $config['lng'] = $lng ?? $config['lng'] ?? 40;
    $config['zoom'] = $zoom ?? $config['zoom'] ?? (($config['lat'] == 0 && $config['lng'] == 0) ? 2 : 17);
    $config['layer'] = $layer ?? $config['layer'] ?? null;
    $config['engine'] ??= \App\Facades\LibrenmsConfig::get('geoloc.engine');
    $config['api_key'] ??= \App\Facades\LibrenmsConfig::get('geoloc.api_key');
    $config['tile_url'] ??= \App\Facades\LibrenmsConfig::get('leaflet.tile_url', '{s}.tile.openstreetmap.org');
    $groupRadius = \App\Facades\LibrenmsConfig::get('leaflet.group_radius', 80);
    $devicesUrl = route('maps.getdevices');
@endphp

<div id="{{ $id }}" style="width: {{ $width }};height: {{ $height }}" {{ $attributes }}></div>

<script>
    function init_geo_map_{{ Str::slug($id, '_') }}() {
        loadjs('js/leaflet.js', function () {
            loadjs('js/L.Control.Locate.min.js', function () {
                let runInit = function () {
                    let mapId = @json($id);
                    if (get_map(mapId)) {
                        return;
                    }
                    let config = @json($config);
                    let leafletMap = init_map(mapId, config);

                    if (@json($fullscreen) && ! L.Control.Fullscreen) {
                        L.Control.Fullscreen = L.Control.extend({
                            onAdd: function(map) {
                                var fsdiv = L.DomUtil.create("div");
                                var a = document.createElement("a");
                                a.title = "Go to fullscreen map";
                                a.href = "#";
                                a.style.textDecoration = "none";
                                a.classList.add("fa-solid", "fa-expand", "fa-2xl");
                                a.onclick = function() {
                                    var zoom = leafletMap.getZoom();
                                    var coords = leafletMap.getCenter();
                                    window.location.href = "fullscreenmap?lat=" + coords.lat + "&lng=" + coords.lng + "&zoom=" + zoom;
                                    return false;
                                };
                                fsdiv.appendChild(a);
                                return fsdiv;
                            }
                        });

                        L.control.fullscreen = function(opts) {
                            return new L.Control.Fullscreen(opts);
                        }
                    }

                    if (@json($fullscreen) && ! leafletMap.fullscreenControl) {
                        leafletMap.fullscreenControl = L.control.fullscreen({ position: "topright" }).addTo(leafletMap);
                    }

                    let targetLocation = new L.LatLng(config.lat, config.lng);
                    leafletMap.setView(targetLocation, config.zoom);

                    if (@json($marker || (bool) $editableLocationId)) {
                        let deviceMarker = L.marker(targetLocation, { zIndexOffset: 20000 }).addTo(leafletMap);
                        if (@json((bool) $editableLocationId)) {
                            deviceMarker.dragging.enable();
                            deviceMarker.on("dragend", function () {
                                var new_location = deviceMarker.getLatLng();
                                if (confirm("Update location to " + new_location + "? This will update this location for all devices!")) {
                                    update_location(@json($editableLocationId), new_location, function(success) {
                                        if (success) {
                                            targetLocation = new_location;
                                            $("#coordinates-text").text(new_location.lat.toFixed(5) + ", " + new_location.lng.toFixed(5));
                                            $("#map-it-button").attr("href", "https://maps.google.com/?q=" + new_location.lat + "," + new_location.lng);
                                        } else {
                                            deviceMarker.setLatLng(targetLocation);
                                        }
                                    });
                                } else {
                                    deviceMarker.setLatLng(targetLocation);
                                }
                            });
                        }
                    }

                    if (@json($showDevices)) {
                        let markerCluster = L.markerClusterGroup({
                            maxClusterRadius: @json($groupRadius),
                            iconCreateFunction: function (cluster) {
                                var markers = cluster.getAllChildMarkers();
                                var color = "green";
                                var newClass = "Cluster marker-cluster marker-cluster-small leaflet-zoom-animated leaflet-clickable";
                                for (var i = 0; i < markers.length; i++) {
                                    if (markers[i].options.icon.options.markerColor == "blue" && color != "red") {
                                        color = "blue";
                                    }
                                    if (markers[i].options.icon.options.markerColor == "red") {
                                        color = "red";
                                    }
                                }
                                return L.divIcon({ html: cluster.getChildCount(), className: color + newClass, iconSize: L.point(40, 40) });
                            },
                        });

                        let redMarker = L.AwesomeMarkers.icon({ icon: 'server', markerColor: 'red', prefix: 'fa', iconColor: 'white' });
                        let blueMarker = L.AwesomeMarkers.icon({ icon: 'server', markerColor: 'blue', prefix: 'fa', iconColor: 'white' });
                        let greenMarker = L.AwesomeMarkers.icon({ icon: 'server', markerColor: 'green', prefix: 'fa', iconColor: 'white' });

                        $.post(@json($devicesUrl), {disabled_alerts: 0, disabled: 0, location_valid: 1, link_type: "depends"})
                            .done(function(data) {
                                $.each(data, function(device_id, device) {
                                    var icon = greenMarker;
                                    var z_offset = 0;
                                    if (device["status"] == 0) {
                                        if (device["maintenance"] != 0) {
                                            icon = blueMarker;
                                            z_offset = 5000;
                                        } else {
                                            icon = redMarker;
                                            z_offset = 10000;
                                        }
                                    }
                                    var marker = L.marker(new L.LatLng(device["lat"], device["lng"]), {title: device["sname"], icon: icon, zIndexOffset: z_offset});
                                    marker.bindPopup("<a href=\"" + device["url"] + "\"><img src=\"" + device["icon"] + "\" width=\"32\" height=\"32\" alt=\"\"> " + device["sname"] + "</a>");
                                    markerCluster.addLayer(marker);

                                    if (@json($showDependencies)) {
                                        $.each(device["parents"], function(parent_idx, parent_id) {
                                            if (parent_id in data && (data[parent_id]["lat"] != device["lat"] || data[parent_id]["lng"] != device["lng"])) {
                                                var line = new L.Polyline([new L.LatLng(device["lat"], device["lng"]), new L.LatLng(data[parent_id]["lat"], data[parent_id]["lng"])], {
                                                    color: "blue",
                                                    weight: 2,
                                                    opacity: 0.8,
                                                    smoothFactor: 1
                                                });
                                                markerCluster.addLayer(line);
                                            }
                                        });
                                    }
                                });
                            });
                        leafletMap.addLayer(markerCluster);
                    }
                };

                if (@json($showDevices)) {
                    loadjs('js/leaflet.markercluster.js', function () {
                        loadjs('js/leaflet.awesome-markers.min.js', runInit);
                    });
                } else {
                    runInit();
                }
            });
        });
    }

    @if($init)
        init_geo_map_{{ Str::slug($id, '_') }}();
    @endif
</script>
