<?php

use App\Models\Location;

$pagetitle[] = 'Locations';

print_optionbar_start();

echo '<span style="font-weight: bold;">Locations</span> &#187; ';

$menu_options = [
    'basic' => 'Basic',
    'traffic' => 'Traffic',
];

if (!$vars['view']) {
    $vars['view'] = 'basic';
}

$sep = '';
foreach ($menu_options as $option => $text) {
    echo $sep;
    if ($vars['view'] == $option) {
        echo "<span class='pagemenu-selected'>";
    }

    echo '<a href="locations/view=' . $option . '/">' . $text . '</a>';
    if ($vars['view'] == $option) {
        echo '</span>';
    }

    $sep = ' | ';
}

unset($sep);

print_optionbar_end();

$maps_api = \LibreNMS\Config::get('geoloc.api_key');
$maps_engine = $maps_api ? \LibreNMS\Config::get('geoloc.engine') : '';


echo '<script src="js/leaflet.js"></script>';
if ($maps_engine == 'google' && $maps_api) {
    echo "<script src='https://maps.googleapis.com/maps/api/js?key=$maps_api' async defer></script>";
    echo "<script src='https://unpkg.com/leaflet.gridlayer.googlemutant@latest/Leaflet.GoogleMutant.js'></script>";
}

//foreach (Location::hasAccess(Auth::user())->get() as $location) {
//    /** @var Location $location */
//    $num = $location->devices()->count();
//    $net = $location->devices()->where('type', 'network')->count();
//    $srv = $location->devices()->where('type', 'server')->count();
//    $fwl = $location->devices()->where('type', 'firewall')->count();
//    $hostalerts = $location->devices()->isDown()->count();
//
//    if ($hostalerts) {
//        $alert = '<i class="fa fa-flag" style="color:red" aria-hidden="true"></i>';
//    } else {
//        $alert = '';
//    }
//
//    $gps = $location->hasCoordinates() ? $location->lat . ',&nbsp;' . $location->lng : 'N/A';
//
//    if ($location != '') {
//        echo '      <tr class="locations">
//            <td class="interface" width="300"><a class="list-bold" href="devices/location=' . $location->id . '/">' . display($location->location) . '</a></td>
//            <td>' . $gps . '</td>
//            <td><button type="button" class="btn btn-default" data-toggle="modal" data-target="#edit-location" data-id="' . $location->id . '" data-location="' . $location->location . '" data-lat="' . $location->lat . '" data-lng="' . $location->lng . '">Edit</button></td>
//            <td>' . $alert . '</td>
//            <td>' . $num . '</td>
//            <td>' . $net . '</td>
//            <td>' . $srv . '</td>
//            <td>' . $fwl . '</td>
//            </tr>
//            ';
//
//        if ($vars['view'] == 'traffic') {
//            echo '<tr></tr><tr class="locations"><td colspan=8>';
//
//            $graph_array['type'] = 'location_bits';
//            $graph_array['height'] = '100';
//            $graph_array['width'] = '220';
//            $graph_array['to'] = $config['time']['now'];
//            $graph_array['legend'] = 'no';
//            $graph_array['id'] = $location->id;
//
//            include 'includes/print-graphrow.inc.php';
//
//            echo '</tr></td>';
//        }
//
//        $done = 'yes';
//    }//end if
//}//end foreach
?>

<div class="panel panel-default">
    <div class="panel-heading">Locations</div>
    <div class="panel-body">
        <div class="table-responsive">
            <table id="locations" class="table table-hover table-condensed table-striped">
                <thead>
                <tr>
                    <th data-column-id="location" data-order="desc">Location</th>
                    <th data-column-id="coordinates">Coordinates</th>
                    <th data-column-id="alert">Alert</th>
                    <th data-column-id="devices">Devices</th>
                    <th data-column-id="network">Network</th>
                    <th data-column-id="servers">Servers</th>
                    <th data-column-id="firewalls">Firewalls</th>
                    <?php echo Auth::user()->isAdmin() ? '<th data-column-id="actions">Actions</th>' : ''?>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="edit-location" tabindex="-1" role="dialog" aria-labelledby="edit-location-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="edit-location-title">Edit Location :: <span></span></h4>
            </div>
            <div class="modal-body">
                <div id="location-edit-map" style="width: 568px; height: 400px;"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-location">Save changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    var locationMap = null;
    var locationMarker = null;
    var locationId = 0;

    var locations_grid = $("#locations").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        url: "ajax/table/location"
    });

    function init_map() {
        locationMap = L.map('location-edit-map');
        locationMarker = L.marker([10, 10]);
        locationMarker.addTo(locationMap);

        <?php if ($maps_engine == 'google') { ?>
        var roads = L.gridLayer.googleMutant({
            type: 'roadmap'	// valid values are 'roadmap', 'satellite', 'terrain' and 'hybrid'
        });
        var satellite = L.gridLayer.googleMutant({
            type: 'satellite'
        });

        var baseMaps = {
            "Streets": roads,
            "Satellite": satellite
        };
        roads.addTo(locationMap);

        <?php } else { ?>

        var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        });
        // var mbx = L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/{id}/tiles/{z}/{x}/{y}?access_token={token}', {
        //     maxZoom: 18,
        //     attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
        //         '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
        //         'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
        //     id: 'satellite-streets-v9',
        //     token: ''
        // });

        //
        // var esri = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        //     attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
        // });

        var baseMaps = {
            "OpenStreetMap": osm
            // "MapBox": mbx,
            // "Esri": esri
        };
        osm.addTo(locationMap);
        <?php } ?>

        L.control.layers(baseMaps, null, {position: 'bottomleft'}).addTo(locationMap);

        // move marker on drag
        locationMap.on('drag', function () {
            locationMarker.setLatLng(locationMap.getCenter());
        });
        // center map on zoom
        locationMap.on('zoom', function () {
            locationMap.setView(locationMarker.getLatLng());
        });
    }

    var modal = $('#edit-location');

    modal.on('show.bs.modal', function (e) {
        $('#edit-location-title>span').text($(e.relatedTarget).data('location'))
    });

    modal.on('shown.bs.modal', function (e) {
        var $btn = $(e.relatedTarget);
        var location = new L.LatLng($btn.data('lat'), $btn.data('lng'));
        locationId = $btn.data('id');
        console.log(locationId);

        if (locationMap === null) {
            init_map();
        }

        // move the map (which will trigger a move event and update the marker
        locationMarker.setLatLng(location);
        locationMap.setView(location, 15);
    });

    $('#save-location').click(function () {
        var loc = locationMarker.getLatLng();
        $.ajax({
            method: 'PATCH',
            url: "ajax/location/" + locationId,
            data: {lat: loc.lat, lng: loc.lng}
        }).success(function () {
            modal.modal('hide');
            locations_grid.bootgrid('reload');
            toastr.success('Location updated');
        }).error(function (e) {
            var msg = 'Failed to update location: ' + e.statusText;
            var data = e.responseJSON;
            if (data) {
                if (data.hasOwnProperty('lat')) {
                    msg = data.lat.join(' ') + '<br />';
                }
                if (data.hasOwnProperty('lng')) {
                    if (!data.hasOwnProperty('lat')) {
                        msg = '';
                    }

                    msg += data.lng.join(' ')
                }
            }

            toastr.error(msg)
        });
    });

    function delete_location(locationId) {
        $.ajax({
            method: 'DELETE',
            url: "ajax/location/" + locationId
        }).success(function () {
            locations_grid.bootgrid('reload');
            toastr.success('Location deleted');
        }).error(function (e) {
            var data = e.responseJSON;
            if (data && data.hasOwnProperty('id')) {
                toastr.error(data.id.join(' '));
            } else {
                toastr.error('Failed to delete location: ' + e.statusText)
            }
        });
    }
</script>
