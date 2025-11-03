<div id="worldmap_widget-{{ $id }}" class="worldmap_widget" data-reload="false"></div>

<script type="application/javascript">
    (function () {
        const map_id = 'worldmap_widget-{{ $id }}';
        const status = {{ Js::from($status) }};
        const device_group = {{ (int) $device_group }};
        const map_config = {{ Js::from($map_config) }};
        const group_radius = {{ (int) $group_radius }};

        function populate_map_markers(map_id, group_radius = 10, status = [0,1], device_group = 0) {
            $.ajax({
                type: "POST",
                url: '{{ route('maps.getdevices') }}',
                dataType: "json",
                data: { location_valid: 1, disabled: 0, disabled_alerts: 0, statuses: status, group: device_group },
                success: function (data) {
                    var markers = Object.values(data).map((device) => {
                        var deviceMarker = L.AwesomeMarkers.icon({
                            icon: device.typeIcon,
                            markerColor: device.status ? 'green' : (device.maintenance == 1 ? 'blue' : 'red'),
                            prefix: 'fa',
                            iconColor: 'white'
                        });

                        var markerData = {
                            title: device.sname,
                            icon: deviceMarker,
                        };

                        if (device.status) { // up
                            markerData.zIndexOffset = 0;
                        } else if (device.maintenance == 1) { // down + maintenance
                            markerData.zIndexOffset = 10000;
                        } else { // down
                            markerData.zIndexOffset = 5000;
                        }

                        var marker = L.marker(new L.LatLng(device.lat, device.lng), markerData);
                        marker.bindPopup(`<a href="${device.url}">${device.sname}</a>`);
                        return marker;
                    });

                    var map = get_map(map_id);
                    if (! map.markerCluster) {
                        map.markerCluster = L.markerClusterGroup({
                            maxClusterRadius: group_radius,
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

                    map.markerCluster.clearLayers();
                    map.markerCluster.addLayers(markers);
                },
                error: function(error){
                    toastr.error(error.statusText);
                }
            });
        }

        loadjs('js/leaflet.js', function () {
            loadjs('js/leaflet.markercluster.js', function () {
                loadjs('js/leaflet.awesome-markers.min.js', function () {
                    loadjs('js/L.Control.Locate.min.js', function () {
                        init_map(map_id, map_config).scrollWheelZoom.disable();
                        populate_map_markers(map_id, group_radius, status, device_group);

                        // register listeners
                        $('#' + map_id).on('click', function (event) {
                            get_map(map_id).scrollWheelZoom.enable();
                        }).on('mouseleave', function (event) {
                            get_map(map_id).scrollWheelZoom.disable();
                        }).on('resize', function (event) {
                            get_map(map_id).invalidateSize();
                        }).on('refresh', function (event) {
                            get_map(map_id).invalidateSize();
                            populate_map_markers(map_id, group_radius, status, device_group);
                        }).on('destroy', function (event) {
                            destroy_map(map_id);
                        });
                    });
                });
            });
        });
    })();
</script>
