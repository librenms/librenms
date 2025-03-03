<?php

use App\Models\Location;
use LibreNMS\Config;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Util\Clean;
use LibreNMS\Util\IP;
use LibreNMS\Util\Time;

echo "<div class='row'>
      <div class='col-md-12'>
          <div class='panel panel-default panel-condensed device-overview'>
            <div class='panel-heading'>";

if (Config::get('overview_show_sysDescr')) {
    echo '<i class="fa fa-id-card fa-lg icon-theme" aria-hidden="true"></i> <strong>';
    echo Config::get('overview_show_sysDescr', true) ? Clean::html($device['sysDescr'], []) : 'System';
    echo '</strong>';
}

echo '</div><div class="panel-body">';

echo '<script src="js/leaflet.js"></script>';
echo '<script src="js/L.Control.Locate.min.js"></script>';
echo '<script src="js/leaflet.markercluster.js"></script>';
echo '<script src="js/leaflet.awesome-markers.min.js"></script>';

if ($device['os'] == 'ios' || $device['os'] == 'iosxe') {
    \LibreNMS\Util\Rewrite::ciscoHardware($device, false);
}

if ($device['features']) {
    $device['features'] = '(' . $device['features'] . ')';
}

$device['os_text'] = Config::getOsSetting($device['os'], 'text');

echo '<div class="row">
        <div class="col-sm-4">System Name</div>
        <div class="col-sm-8">' . Clean::html($device['sysName'], []) . ' </div>
      </div>';

if (! empty($device['overwrite_ip'])) {
    echo "<div class='row'><div class='col-sm-4'>Assigned IP</div><div class='col-sm-8'>" . htmlentities($device['overwrite_ip']) . '</div></div>';
} elseif (! empty($device['ip'])) {
    echo "<div class='row'><div class='col-sm-4'>Resolved IP</div><div class='col-sm-8'>" . htmlentities($device['ip']) . '</div></div>';
} else {
    try {
        $ip = (string) IP::parse($device['hostname']);
        if ($ip !== format_hostname($device)) {
            echo "<div class='row'><div class='col-sm-4'>IP Address</div><div class='col-sm-8'>" . htmlentities($ip) . '</div></div>';
        }
    } catch (InvalidIpException $e) {
        // don't add an ip line
    }
}

if ($device['purpose']) {
    echo '<div class="row">
        <div class="col-sm-4">Description</div>
        <div class="col-sm-8">' . Clean::html($device['purpose'], []) . '</div>
      </div>';
}

if ($device['hardware']) {
    echo '<div class="row">
        <div class="col-sm-4">Hardware</div>
        <div class="col-sm-8">' . Clean::html($device['hardware'], []) . '</div>
      </div>';
}

echo '<div class="row">
        <div class="col-sm-4 text-nowrap">Operating System</div>
        <div class="col-sm-8">' . Clean::html($device['os_text'] . ' ' . $device['version'] . ' ' . $device['features'], []) . ' </div>
      </div>';

if ($device['serial']) {
    echo '<div class="row">
        <div class="col-sm-4">Serial</div>
        <div class="col-sm-8">' . Clean::html($device['serial'], []) . '</div>
      </div>';
}

if ($device['sysObjectID']) {
    echo '<div class="row">
        <div class="col-sm-4">Object ID</div>
        <div class="col-sm-8">' . Clean::html($device['sysObjectID'], []) . '</div>
      </div>';
}

if ($device['sysContact']) {
    echo '<div class="row">
        <div class="col-sm-4">Contact</div>';
    if (get_dev_attrib($device, 'override_sysContact_bool')) {
        echo '
        <div class="col-sm-8">' . Clean::html(get_dev_attrib($device, 'override_sysContact_string')) . '</div>
      </div>
      <div class="row">
        <div class="col-sm-4">SNMP Contact</div>';
    }

    echo '
        <div class="col-sm-8">' . Clean::html($device['sysContact']) . '</div>
      </div>';
}

if (! empty($device['inserted']) && preg_match('/^0/', $device['inserted']) == 0) {
    $inserted_text = 'Device Added';
    $inserted = (Time::formatInterval(-(time() - strtotime($device['inserted']))));
    echo "<div class='row'><div class='col-sm-4'>$inserted_text</div><div class='col-sm-8' title='$inserted_text on " . $device['inserted'] . "'>$inserted</div></div>";
}

if (! empty($device['last_discovered'])) {
    $last_discovered_text = 'Last Discovered';
    $last_discovered = (empty($device['last_discovered']) ? 'Never' : Time::formatInterval(-(time() - strtotime($device['last_discovered']))));
    echo "<div class='row'><div class='col-sm-4'>$last_discovered_text</div><div class='col-sm-8' title='$last_discovered_text at " . $device['last_discovered'] . "'>$last_discovered</div></div>";
}


if (! $device['status'] && ! $device['last_polled']) {
    $uptime = __('Never polled');
    $uptime_text = 'Uptime';
} elseif ($device['status']) {
    $uptime = Time::formatInterval($device['uptime']);
    $uptime_text = 'Uptime';
} else {
    $uptime = Time::formatInterval(DeviceCache::getPrimary()->downSince()->diffInSeconds());
    $uptime_text = 'Downtime';
}

if ($uptime) {
    echo "<div class='row'><div class='col-sm-4'>$uptime_text</div><div class='col-sm-8'>$uptime</div></div>";
}

if ($device['location_id'] && $location = Location::find($device['location_id'])) {
    $maps_api = Config::get('geoloc.api_key');
    $maps_engine = $maps_api ? Config::get('geoloc.engine') : '';
    $location_valid = ($location && $location->coordinatesValid());
    $location_coords = $location_valid ? $location->lat . ', ' . $location->lng : 'N/A';

    echo '
    <div class="row">
        <div class="col-sm-4">Location</div>
        <div class="col-sm-8">' . Clean::html($location->display(), []) . '</div>
    </div>
    <div class="row" id="coordinates-row" data-toggle="collapse" data-target="#toggle-map">
        <div class="col-sm-4">Lat / Lng</div>
        <div class="col-sm-8"><span id="coordinates-text">' . $location_coords . '</span><div class="pull-right">';

    echo '<button type="button" id="toggle-map-button" class="btn btn-primary btn-xs" data-toggle="collapse" data-target="#toggle-map"><i class="fa fa-map" style="color:white" aria-hidden="true"></i> <span>View</span></button>';
    if ($location_valid) {
        echo ' <a id="map-it-button" href="https://maps.google.com/?q=' . $location->lat . ',' . $location->lng . '" target="_blank" class="btn btn-success btn-xs" role="button"><i class="fa fa-map-marker" style="color:white" aria-hidden="true"></i> Map</a>';
    }
    echo '</div>
        </div>
    </div>
    <div id="toggle-map" class="row collapse"><div id="location-map"></div></div>
    <script>
        var device_map, device_marker_cluster;

        L.Control.Fullscreen = L.Control.extend({
            onAdd: function(map) {
                var fsdiv = L.DomUtil.create("div");
                var a = document.createElement("a");
                a.title = "Go to fullscreen map";
                a.href = "#";
                a.style.textDecoration = "none";
                a.classList.add("fa-solid","fa-expand","fa-2xl");
                a.onclick = function() {
                    var zoom = device_map.getZoom();
                    var coords = device_map.getCenter();
                    window.location.href = "fullscreenmap?lat=" + coords.lat + "&lng=" + coords.lng + "&zoom=" + zoom;
                    return false
                };
                fsdiv.appendChild(a);
                return fsdiv;
            }
        });

        L.control.fullscreen = function(opts) {
            return new L.Control.Fullscreen(opts);
        }

        $("#toggle-map").on("shown.bs.collapse", function () {
             var device_marker, device_location;
             if (device_map == null) {
                device_location = new L.LatLng(' . (float) $location->lat . ', ' . (float) $location->lng . ');
                var config = {
                    "tile_url": "' . Config::get('leaflet.tile_url', '{s}.tile.openstreetmap.org') . '",
                    "engine": "' . $maps_engine . '",
                    "api_key": "' . $maps_api . '"
                };
                device_map = init_map("location-map", config);
                device_marker = L.marker(device_location).addTo(device_map);
                let zoom = (device_location.lat === 0 && device_location.lng === 0) ? 2 : 17;
                device_map.setView(device_location, zoom);
                device_marker.dragging.enable();
                L.control.fullscreen({ position: "topright" }).addTo(device_map);
                ';

    # If we are configured to show all devices on map
    if (Config::get('device_location_map_show_devices')) {
        // Get a list of devices we have access to and add them to the map
        echo'
                device_marker_cluster = L.markerClusterGroup({
                    maxClusterRadius: ' . Config::get('leaflet.group_radius', 80) . ',
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
                var redMarker = L.AwesomeMarkers.icon({
                    icon: \'server\',
                    markerColor: \'red\', prefix: \'fa\', iconColor: \'white\'
                  });
                var blueMarker = L.AwesomeMarkers.icon({
                    icon: \'server\',
                    markerColor: \'blue\', prefix: \'fa\', iconColor: \'white\'
                  });
                var greenMarker = L.AwesomeMarkers.icon({
                    icon: \'server\',
                    markerColor: \'green\', prefix: \'fa\', iconColor: \'white\'
                  });

                $.post( "' . route('maps.getdevices') . '", {disabled_alerts: 0, disabled: 0, location_valid: 1, link_type: "depends"})
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
                        device_marker_cluster.addLayer(marker);
        ';
        # If we are configured to show dependencies
        if (Config::get('device_location_map_show_device_dependencies')) {
            echo'
                        $.each( device["parents"], function( parent_idx, parent_id ) {
                            if (parent_id in data && (data[parent_id]["lat"] != device["lat"] || data[parent_id]["lng"] != device["lng"])) {
                                var line = new L.Polyline([new L.LatLng(device["lat"],device["lng"]), new L.LatLng(data[parent_id]["lat"],data[parent_id]["lng"])], {
                                    color: "blue",
                                    weight: 2,
                                    opacity: 0.8,
                                    smoothFactor: 1
                                });
                                device_marker_cluster.addLayer(line);
                            }
                        });
            ';
        }
        echo'
                      })
                  })
                  .fail(function() {
                      alert( "error fetching devices" );
                  });
                device_map.addLayer(device_marker_cluster);
        ';
    } elseif (Auth::user()->isAdmin()) {
        echo '
                device_marker = L.marker(device_location).addTo(device_map);
                device_marker.dragging.enable();
                device_marker.on("dragend", function () {
                    var new_location = device_marker.getLatLng();
                    if (confirm("Update location to " + new_location + "? This will update this location for all devices!")) {
                        update_location(' . $location->id . ', new_location, function(success) {
                            if (success) {
                                $("#coordinates-text").text(new_location.lat.toFixed(5) + ", " + new_location.lng.toFixed(5));
                                $("#map-it-button").attr("href", "https://maps.google.com/?q=" + new_location.lat + "," + new_location.lng );
                            }
                        });
                    }
                });';
    }
    echo '
        }
            $("#toggle-map-button").find(".fa").removeClass("fa-map").addClass("fa-map-o");
            $("#toggle-map-button span").text("Hide")
        }).on("hidden.bs.collapse", function () {
            $("#toggle-map-button").find(".fa").removeClass("fa-map-o").addClass("fa-map");
            $("#toggle-map-button span").text("View")
        });';
    if (Config::get('device_location_map_open')) {
        echo '$("#toggle-map").collapse("show");';
    }
    echo '</script>
    ';
}
?>
      </div>
    </div>
  </div>
</div>
