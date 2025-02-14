@extends('layouts.librenmsv1')

@section('title', __('Geographical Map'))

@section('content')

@if($map_engine != 'leaflet')

<div class="container-fluid">
<div class="row">
<div class="col-md-12">
Only leaflet map engine is currently supported
</div>
</div>
</div>

@else

@if($group_name)
<div class="container-fluid">
<div class="row" id="controls-row">
<div class="col-md-12">

&nbsp;
<big><b>{{ $group_name }}</b></big>

</div>
</div>
</div>
@endif

<div class="container" id="fullscreen-map"></div>

@endif

@endsection

@section('css')
<style>
html, body, #fullscreen-map {
   height: 100%;
   width: 100%;
   padding-bottom: 0;
   margin-bottom: 0;
}
</style>
@endsection

@section('javascript')
@if($map_engine == "leaflet")
<script src="js/leaflet.js"></script>
<script src="js/L.Control.Locate.min.js"></script>
<script src="js/leaflet.markercluster.js"></script>
<script src="js/leaflet.awesome-markers.min.js"></script>
@endif

@endsection

@section('scripts')
<script type="text/javascript">
    function checkMapSize() {
        var mapheight = $(window).height() - $("#fullscreen-map").offset().top;
        if(mapheight < 200) {
            mapheight = 200;
        }
        $("#fullscreen-map").height(mapheight);
    };

    window.addEventListener('resize', checkMapSize);
    checkMapSize();

    var device_map = init_map("fullscreen-map", {engine: "{{$map_provider}}", api_key: "{{$map_api_key}}", "tile_url": "{{$tile_url}}", lat: {{$init_lat}}, lng: {{$init_lng}}, zoom: {{$init_zoom}}});

    var device_marker_cluster = L.markerClusterGroup({
        maxClusterRadius: {{$group_radius}},
        iconCreateFunction: function (cluster) {
            var markers = cluster.getAllChildMarkers();
            var n = 0;
            color = "green"
            newClass = "Cluster marker-cluster marker-cluster-small leaflet-zoom-animated leaflet-clickable";
            for (var i = 0; i < markers.length; i++) {
                if (markers[i].options.icon.options.markerColor == "blue" && color != "red") {
                    color = "blue";
                }
                if (markers[i].options.icon.options.markerColor == "red") {
                    color = "red";
                }
            }
            return L.divIcon({ html: cluster.getChildCount(), className: color+newClass, iconSize: L.point(40, 40) });
        },
    });
    device_map.addLayer(device_marker_cluster);

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

    var device_markers = {};
    var link_markers = {};

    function checkParentLink(device, parent) {
        line_id = "dev." + device["id"] + "." + parent["id"];
        if(line_id in link_markers) {
            link_markers[line_id].setLatLngs([new L.LatLng(device["lat"],device["lng"]), new L.LatLng(parent["lat"],parent["lng"])]);
        } else {
            var line = new L.Polyline([new L.LatLng(device["lat"],device["lng"]), new L.LatLng(parent["lat"],parent["lng"])], {
                color: "blue",
                weight: 2,
                opacity: 0.8,
                smoothFactor: 1
            });
            link_markers[line_id] = line;
            device_marker_cluster.addLayer(line);
        }
        return line_id;
    }

    function refreshMap() {
        var devices = {};
        var links = {};
        var device_group = {{ $group_id ? $group_id : 'null' }};
@if($show_netmap && $netmap_source == 'depends')
        $.post( '{{ route('maps.getdevices') }}', {disabled: 0, location_valid: 1, disabled_alerts: {{$netmap_include_disabled_alerts}}, group: device_group, link_type: '{{$netmap_source}}'})
@else
        $.post( '{{ route('maps.getdevices') }}', {disabled: 0, location_valid: 1, disabled_alerts: {{$netmap_include_disabled_alerts}}, group: device_group})
@endif
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
                    if(device_id in device_markers) {
                        device_markers[device_id].setLatLng(new L.LatLng(device["lat"], device["lng"]));
                        device_markers[device_id].setZIndexOffset(z_offset);
                        device_markers[device_id].setIcon(icon);

                        // If we have requested parent data, update lines
                        $.each( device["parents"], function( parent_idx, parent_id ) {
                            if (parent_id in data && (data[parent_id]["lat"] != device["lat"] || data[parent_id]["lng"] != device["lng"])) {
                                var line_id = checkParentLink(device, data[parent_id]);
                                links[line_id] = true;
                            }
                        });
                    } else {
                        var marker = L.marker(new L.LatLng(device["lat"],device["lng"]), {title: device["sname"], icon: icon, zIndexOffset: z_offset});
                        marker.bindPopup("<a href=\"" + device["url"] + "\"><img src=\"" + device["icon"] + "\" width=\"32\" height=\"32\" alt=\"\"> " + device["sname"] + "</a>");
                        device_marker_cluster.addLayer(marker);
                        device_markers[device_id] = marker;

                        // If we have requested parent data, add lines
                        $.each( device["parents"], function( parent_idx, parent_id ) {
                            if (parent_id in data && (data[parent_id]["lat"] != device["lat"] || data[parent_id]["lng"] != device["lng"])) {
                                var line_id = checkParentLink(device, data[parent_id]);
                                links[line_id] = true;
                            }
                        });
                    }
                    devices[device_id] = true;
                })
                $("#countdown").css("border", "1px solid green");

                // Remove any devices that have disappeared
                $.each( device_markers, function( device_id, marker ) {
                    if(! (device_id in devices)) {
                        device_marker_cluster.removeLayer(marker);
                    }
                });
@if($show_netmap && $netmap_source == 'depends')
                // Remove any links that have disappeared
                $.each( link_markers, function( link_id, line ) {
                    if(! (link_id in links)) {
                        device_marker_cluster.removeLayer(line);
                    }
                });
@endif
            })
            .fail(function() {
                $("#countdown").css("border", "1px solid red");
            });
@if($show_netmap && $netmap_source == 'xdp')
        $.post( '{{ route('maps.getgeolinks') }}', {link_type: '{{$netmap_source}}', group: device_group})
            .done(function( data ) {
                $.each( data, function( link_id, link) {
                    if(link_id in link_markers) {
                        link_markers[link_id].setStyle({color: link['color'], weight: link['width']}).setLatLngs([new L.LatLng(link['local_lat'], link['local_lng']), new L.LatLng(link['remote_lat'], link['remote_lng'])]);
                        links[link_id] = true;
                    } else {
                        var line = new L.Polyline([new L.LatLng(link['local_lat'], link['local_lng']), new L.LatLng(link['remote_lat'], link['remote_lng'])], { color: link['color'], weight: link['width'], opacity: 0.8, smoothFactor: 1});
                        device_marker_cluster.addLayer(line);
                        link_markers[link_id] = line;
                        links[link_id] = true;
                   }
                })
                $("#countdown").css("border", "1px solid green");

                // Remove any links that have disappeared
                $.each( link_markers, function( link_id, line ) {
                    if(! (link_id in links)) {
                        device_marker_cluster.removeLayer(line);
                    }
                });
            })
            .fail(function() {
                $("#countdown").css("border", "1px solid red");
            });
@endif
    }

    device_map.scrollWheelZoom.disable();
    $(document).ready(function(){
        $("#fullscreen-map").on("click", function(event) {
            device_map.scrollWheelZoom.enable();
        });
        $("#fullscreen-map").on("mouseleave", function(event) {
            device_map.scrollWheelZoom.disable();
        });

        // initial load
        refreshMap();
    });
</script>
<x-refresh-timer :refresh="$page_refresh" callback="refreshMap"></x-refresh-timer>
@endsection

