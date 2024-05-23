<div id="worldmap_widget-{{ $id }}" class="leaflet-map" data-reload="false"></div>

<style>
    .leaflet-map {
        width: 100%;
        height: 100%;
        text-align: left;
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
    }
    .leaflet-map a:hover {
        text-decoration: none;
    }
</style>

<script type="application/javascript">
    (function () {
        var map_id = 'worldmap_widget-{{ $id }}';
        var status = {{ Js::from($status) }};
        var group = {{ (int) $group }};
        var map_config = {{ Js::from($map_config) }};

        function init_marker_cluster(map) {
            if (! map.markerCluster) {
                  map.markerCluster = L.markerClusterGroup({
                    maxClusterRadius: '{{ $group_radius }}',
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
                        return L.divIcon({
                            html: cluster.getChildCount(),
                            className: color + newClass,
                            iconSize: L.point(40, 40)
                        });
                    }
                });

                map.addLayer(map.markerCluster);
            }
        }

        function populate_markers(map) {
            $.ajax({
                type: "GET",
                url: '{{ route('widget.worldmap.data') }}',
                dataType: "json",
                data: { status: status, group: group }, // FIXME add correct values
                success: function (data) {
                    var redMarker = L.AwesomeMarkers.icon({
                        icon: 'server',
                        markerColor: 'red', prefix: 'fa', iconColor: 'white'
                    });
                    var blueMarker = L.AwesomeMarkers.icon({
                        icon: 'server',
                        markerColor: 'blue', prefix: 'fa', iconColor: 'white'
                    });
                    var greenMarker = L.AwesomeMarkers.icon({
                        icon: 'server',
                        markerColor: 'green', prefix: 'fa', iconColor: 'white'
                    });

                    var markers = data.map((device) => {
                        var markerData = {title: device.name};
                        switch (device.status) {
                            case 0: // down
                                markerData.icon = redMarker;
                                markerData.zIndexOffset = 5000;
                                break;
                            case 3: // down + maintenance
                                markerData.icon = blueMarker;
                                markerData.zIndexOffset = 10000;
                                break;
                            default: // up
                                markerData.icon = greenMarker;
                                markerData.zIndexOffset = 0;
                        }

                        var marker = L.marker(new L.LatLng(device.lat, device.lng), markerData);
                        marker.bindPopup(`<a href="${device.url}"><img src="${device.icon}" width="32" height="32" alt=""> ${device.name}</a>`);
                        return marker;
                    });

                    map.markerCluster.clearLayers();
                    map.markerCluster.addLayers(markers);
                },
                error: function(){
                    toastr.error(data.message);
                }
            });
        }

        function register_listeners(map) {
            map.scrollWheelZoom.disable();
            $('#' + map_id).on('click', function (event) {
                map.scrollWheelZoom.enable();
            }).on('mouseleave', function (event) {
                map.scrollWheelZoom.disable();
            }).on('resize', function (event) {
                map.invalidateSize();
            }).on('refresh', function (event) {
                map.invalidateSize();
                populate_markers(map);
            }).on('destroy', function (event) {
                destroy_map(map_id);
            });
        }

        loadjs('js/leaflet.js', function () {
            loadjs('js/leaflet.markercluster.js', function () {
                loadjs('js/leaflet.awesome-markers.min.js', function () {
                    loadjs('js/L.Control.Locate.min.js', function () {
                        var map = init_map(map_id, map_config);
                        init_marker_cluster(map);
                        populate_markers(map);
                        register_listeners(map);
                    });
                });
            });
        });
    })();
</script>
