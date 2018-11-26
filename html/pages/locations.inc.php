<?php

$pagetitle[] = 'Locations';

$maps_api = \LibreNMS\Config::get('geoloc.api_key');
$maps_engine = $maps_api ? \LibreNMS\Config::get('geoloc.engine') : '';

echo '<script src="js/leaflet.js"></script>';
echo '<link rel="stylesheet" href="js/L.Control.Locate.min.css"/>';
echo '<script src="js/L.Control.Locate.min.js"></script>';

if ($maps_engine == 'google') {
    echo "<script src='https://maps.googleapis.com/maps/api/js?key=$maps_api' async defer></script>";
    echo "<script src='js/Leaflet.GoogleMutant.js'></script>";
}

?>

<script id="location-graph-template" type="text/x-handlebars-template">
    <tr class="bg-fixer-{{id}}"></tr>
    <tr id="location-graph-{{id}}" class="location_graphs"><td colspan=8>
    <?php
        \Librenms\Config::set('enable_lazy_load', false);
        $return_data = true;
        $graph_array = [
            'type' => 'location_bits',
            'height' => '100',
            'width' => '220',
            'legend' => 'no',
            'id' => '{{id}}',
        ];
        include 'includes/print-graphrow.inc.php';
        foreach ($graph_data as $graph) {
            echo "<div class='col-md-3'>";
            echo str_replace('%7B%7Bid%7D%7D', '{{id}}', $graph); // restore handlebars
            echo "</div>";
        }
    ?>
    </td></tr>
</script>


<div class="panel panel-default">
    <div class="panel-heading"><h4 class="panel-title">Locations</h4></div>
    <div class="panel-body">
        <div class="table-responsive">
            <table id="locations" class="table table-hover table-condensed table-striped">
                <thead>
                <tr>
                    <th data-column-id="location" data-order="desc">Location</th>
                    <th data-column-id="coordinates" data-sortable="false">Coordinates</th>
                    <th data-column-id="alert" data-sortable="false">Alert</th>
                    <th data-column-id="devices" data-sortable="false">Devices</th>
                    <th data-column-id="network" data-sortable="false">Network</th>
                    <th data-column-id="servers" data-sortable="false">Servers</th>
                    <th data-column-id="firewalls" data-sortable="false">Firewalls</th>
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
        rowCount: [25, 50, 100, -1],
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
        if (location.protocol === 'https:') {
            // can't request location permission without https
            L.control.locate().addTo(locationMap);
        }

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

    var locationsGraphTemplate = Handlebars.compile(document.getElementById("location-graph-template").innerHTML);
    function toggle_location_graphs(locationId, source) {
        var $btn = $(source);
        var $row = $btn.closest('tr');
        if ($btn.hasClass('active')) {
            // hide
            $btn.removeClass('active');
            $('#location-graph-' + locationId).hide();
            $('#bg-fix-' + locationId).hide();
            // $row.next('.bg-fix').remove();
            // $('#location-graph-' + locationId).detach().appendTo($('#graph-holder')).hide();
        } else {
            // show
            $btn.addClass('active');
            $existing = $('#location-graph-' + locationId);
            if ($existing.length) {
                $existing.show();
                $('#bg-fix-' + locationId).show();
            } else {
                var html = locationsGraphTemplate({id: locationId});
                console.log(html);
                $(html).insertAfter($row);
            }
            // $('#location-graph-' + locationId).detach().insertAfter($row).show().before('<tr class="bg-fix"></tr>');
        }
    }
</script>
