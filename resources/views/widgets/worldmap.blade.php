<div id="leaflet-map-{{ $id }}" style="width: {{ $dimensions['x'] }}px; height: {{ $dimensions['y'] }}px;"></div>

<script type="application/javascript">
    loadjs('js/leaflet.js', function() {
    loadjs('js/leaflet.markercluster.js', function () {
    loadjs('js/leaflet.awesome-markers.min.js', function () {
        var map = L.map('leaflet-map-{{ $id }}', { zoomSnap: 0.1 } ).setView(['{{ $init_lat }}', '{{ $init_lng }}'], '{{ sprintf('%01.1f', $init_zoom) }}');
        L.tileLayer('//{{ $title_url }}/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var markers = L.markerClusterGroup({
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
                return L.divIcon({ html: cluster.getChildCount(), className: color+newClass, iconSize: L.point(40, 40) });
            }
        });
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

        $.get( '{{ route('maps.getdevices') }}', {location_valid: 1, disabled: 0, disabled_alerts: 0, status: '{{$status}}'.split(","), group: {{$device_group}}})
            .done(function( data ) {
                $.each( data, function( device_id, device ) {
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
                    var marker = L.marker(new L.LatLng(device["lat"],device["lng"]), {title: device["sname"], icon: icon, zIndexOffset: z_offset});
                    marker.bindPopup("<a href=\"" + device["url"] + "\"><img src=\"" + device["icon"] + "\" width=\"32\" height=\"32\" alt=\"\"> " + device["sname"] + "</a>");
                    markers.addLayer(marker);
                })
            });

        map.addLayer(markers);
        map.scrollWheelZoom.disable();
        $(document).ready(function() {
            $("#leaflet-map-{{ $id }}").on("click", function (event) {
                map.scrollWheelZoom.enable();
            }).on("mouseleave", function (event) {
                map.scrollWheelZoom.disable();
            });
        });

    });});});
</script>
