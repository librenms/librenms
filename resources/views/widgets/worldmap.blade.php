<div id="leaflet-map-{{ $id }}"
     style="width: {{ $dimensions['x'] }}px; height: {{ $dimensions['y'] }}px;"
     data-reload="false"
></div>

<script type="application/javascript">
    $(function () {
        var map;
        var status = {{ Js::from($status) }};
        var group = {{ (int) $group }};

        function init_marker_cluster() {
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

        function populate_markers() {
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

        $('#leaflet-map-{{ $id }}').on('refresh', function (event) {
            populate_markers();
        })
        $('#leaflet-map-{{ $id }}').on('destroy', function (event) {
            map.off();
            map.remove();
            delete map;
        })

        loadjs('js/leaflet.js', function () {
            loadjs('js/leaflet.markercluster.js', function () {
                loadjs('js/leaflet.awesome-markers.min.js', function () {
                    map = L.map('leaflet-map-{{ $id }}', {zoomSnap: 0.1}).setView(['{{ $init_lat }}', '{{ $init_lng }}'], '{{ sprintf('%01.1f', $init_zoom) }}');
                    L.tileLayer('//{{ $title_url }}/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    init_marker_cluster();
                    populate_markers();

                    map.scrollWheelZoom.disable();
                    $(document).ready(function () {
                        $("#leaflet-map-{{ $id }}").on("click", function (event) {
                            map.scrollWheelZoom.enable();
                        }).on("mouseleave", function (event) {
                            map.scrollWheelZoom.disable();
                        });
                    });

                });
            });
        });
    })
</script>
