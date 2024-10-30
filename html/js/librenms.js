window.maps = {};

function override_config(event, state, tmp_this) {
    event.preventDefault();
    var $this = tmp_this;
    var attrib = $this.data('attrib');
    var device_id = $this.data('device_id');
    $.ajax({
        type: 'POST',
        url: 'ajax_form.php',
        data: { type: 'override-config', device_id: device_id, attrib: attrib, state: state },
        dataType: 'json',
        success: function(data) {
            if (data.status == 'ok') {
                toastr.success(data.message);
            }
            else {
                toastr.error(data.message);
            }
        },
        error: function() {
            toastr.error('Could not set this override');
        }
    });
}

var oldH;
var oldW;
$(document).ready(function() {
    // Device override ajax calls
    $("[name='override_config']").bootstrapSwitch('offColor','danger');
    $('input[name="override_config"]').on('switchChange.bootstrapSwitch',  function(event, state) {
        override_config(event,state,$(this));
    });

    // Device override for text inputs
    $(document).on('blur', 'input[name="override_config_text"]', function(event) {
        event.preventDefault();
        var $this = $(this);
        var attrib = $this.data('attrib');
        var device_id = $this.data('device_id');
        var value = $this.val();
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: { type: 'override-config', device_id: device_id, attrib: attrib, state: value },
            dataType: 'json',
            success: function(data) {
                if (data.status == 'ok') {
                    toastr.success(data.message);
                }
                else {
                    toastr.error(data.message);
                }
            },
            error: function() {
                toastr.error('Could not set this override');
            }
        });
    });

    oldW=$(window).width();
    oldH=$(window).height();
});

function submitCustomRange(frmdata) {
    var reto = /to=([0-9a-zA-Z\-])+/g;
    var refrom = /from=([0-9a-zA-Z\-])+/g;
    var tsto = $("#dtpickerto").data("DateTimePicker").date().unix();
    var tsfrom = $("#dtpickerfrom").data("DateTimePicker").date().unix();

    if (frmdata.selfaction.value.match(reto)) {
        frmdata.selfaction.value = frmdata.selfaction.value.replace(reto, 'to=' + tsto);
    } else {
        frmdata.selfaction.value += '/to=' + tsto;
    }

    if (frmdata.selfaction.value.match(refrom)) {
        frmdata.selfaction.value = frmdata.selfaction.value.replace(refrom, 'from=' + tsfrom);
    } else {
        frmdata.selfaction.value += '/from=' + tsfrom;
    }

    frmdata.action = frmdata.selfaction.value;
    return true;
}

function updateTimezone(tz, static)
{
    $.post(ajax_url + '/set_timezone',
        {
            timezone: tz,
            static: static
        },
        function(data) {
            if(data === tz) {
                location.reload();
            }
        },
        'text'
    );
}

function updateResolution(refresh)
{
    $.post(ajax_url + '/set_resolution',
        {
            width: $(window).width(),
            height:$(window).height()
        },
        function(data) {
            if(refresh == true) {
                location.reload();
            }
        },'json'
    );
}

var rtime;
var timeout = false;
var delta = 500;
var newH;
var newW;

$(window).on('resize', function(){
    rtime = new Date();
    if (timeout === false && !(typeof no_refresh === 'boolean' && no_refresh === true)) {
        timeout = true;
        setTimeout(resizeend, delta);
    }
});

function resizeend() {
    if (new Date() - rtime < delta) {
        setTimeout(resizeend, delta);
    }
    else {
        newH=$(window).height();
        newW=$(window).width();
        timeout = false;
        if(Math.abs(oldW - newW) >= 200)
        {
            refresh = true;
        }
        else {
            refresh = false;
            resizeGraphs();
        }
        updateResolution(refresh);
    }
};

function resizeGraphs() {
    ratioW=newW/oldW;
    ratioH=newH/oldH;

    $('.graphs').each(function (){
        var img = jQuery(this);
        img.attr('width',img.width() * ratioW);
    });
    oldH=newH;
    oldW=newW;
}


$(document).on("click", '.collapse-neighbors', function(event)
{
    var caller = $(this);
    var button = caller.find('.neighbors-button');
    var list = caller.find('.neighbors-interface-list');
    var continued = caller.find('.neighbors-list-continued');

    if(button.hasClass("fa-plus")) {
        button.addClass('fa-minus').removeClass('fa-plus');
    }
    else {
        button.addClass('fa-plus').removeClass('fa-minus');
    }

    list.toggle();
    continued.toggle();
});

$(document).ready(function() {
    var lines = 'on';
    $("#linenumbers").button().on("click", function() {
        if (lines == 'on') {
            $($('.config').find('ol').get().reverse()).each(function(){
                $(this).replaceWith($('<ul>'+$(this).html()+'</ul>'))
                lines = 'off';
                $('#linenumbers').val('Show line numbers');
            });
        }
        else {
            $($('.config').find('ul').get().reverse()).each(function(){
                $(this).replaceWith($('<ol>'+$(this).html()+'</ol>'));
                lines = 'on';
                $('#linenumbers').val('Hide line numbers');
            });
        }
    });
});

// Fix select2 search focus bug
$(document).on('select2:open', (e) => {
    const selectId = e.target.id

    $(".select2-search__field[aria-controls='select2-" + selectId + "-results']").each(function (key, value){
        value.focus();
    })
})

function refresh_oxidized_node(device_hostname){
    $.ajax({
        type: 'POST',
        url: 'ajax_form.php',
        data: {
            type: "refresh-oxidized-node",
            device_hostname: device_hostname
        },
        success: function (data) {
            if(data['status'] == 'ok') {
                toastr.success(data['message']);
            } else {
                toastr.error(data['message']);
            }
        },
        error:function(){
            toastr.error('An error occured while queuing refresh for an oxidized node (hostname: ' + device_hostname + ')');
        }
    });
}

$(document).ready(function () {
    setInterval(function () {
        $('.bootgrid-table').each(function() {
            $(this).bootgrid('reload');
        });
    }, 300000);
});

var jsFilesAdded = [];
var jsLoadingFiles = {};
function loadjs(filename, func){
    if (jsFilesAdded.indexOf(filename) < 0) {
        if (filename in jsLoadingFiles) {
            // store all waiting callbacks
            jsLoadingFiles[filename].push(func);
        } else {
            // first request, load the script store the callback for this request
            jsLoadingFiles[filename] = [func];
            $.getScript(filename, function () {
                // finish loading the script, call all waiting callbacks
                jsFilesAdded.push(filename);
                for (var i = 0; i < jsLoadingFiles[filename].length; i++) {
                    jsLoadingFiles[filename][i]();
                }
                delete jsLoadingFiles[filename];
            });
        }
    } else {
        func();
    }
}

function init_map(id, config = {}) {
    let leaflet = get_map(id)
    if (leaflet) {
        // return existing map
        return leaflet;
    }

    leaflet = L.map(id, {
        preferCanvas: true,
        zoom: config.zoom !== undefined ? config.zoom : 3,
        center: (config.lat !== undefined && config.lng !== undefined) ? [config.lat, config.lng] : [40,-20]
    });
    window.maps[id] = leaflet;
    let baseMaps = {};

    if (config.engine === 'google' && config.api_key) {
        leaflet.setMaxZoom(21);
        loadjs('https://maps.googleapis.com/maps/api/js?key=' + config.api_key, function () {
            loadjs('js/Leaflet.GoogleMutant.js', function () {
                const roads = L.gridLayer.googleMutant({
                    type: 'roadmap'	// valid values are 'roadmap', 'satellite', 'terrain' and 'hybrid'
                });
                const satellite = L.gridLayer.googleMutant({
                    type: 'satellite'
                });

                baseMaps = {
                    "Streets": roads,
                    "Satellite": satellite
                };
                leaflet.layerControl = L.control.layers(baseMaps, null, {position: 'bottomleft'}).addTo(leaflet);
                (config.layer in baseMaps ? baseMaps[config.layer] : roads).addTo(leaflet);
                leaflet.layerControl._container.style.display = (config.readonly ? 'none' : 'block');
            });
        });
    } else if (config.engine === 'bing' && config.api_key) {
        leaflet.setMaxZoom(18);
        loadjs('js/leaflet-bing-layer.min.js', function () {
            const roads = L.tileLayer.bing({
                bingMapsKey: config.api_key,
                imagerySet: 'RoadOnDemand'
            });
            const satellite = L.tileLayer.bing({
                bingMapsKey: config.api_key,
                imagerySet: 'AerialWithLabelsOnDemand'
            });

            baseMaps = {
                "Streets": roads,
                "Satellite": satellite
            };
            leaflet.layerControl = L.control.layers(baseMaps, null, {position: 'bottomleft'}).addTo(leaflet);
            (config.layer in baseMaps ? baseMaps[config.layer] : roads).addTo(leaflet);
            leaflet.layerControl._container.style.display = (config.readonly ? 'none' : 'block');
        });
    } else if (config.engine === 'mapquest' && config.api_key) {
        leaflet.setMaxZoom(20);
        loadjs('https://www.mapquestapi.com/sdk/leaflet/v2.2/mq-map.js?key=' + config.api_key, function () {
            const roads = MQ.mapLayer();
            const satellite = MQ.hybridLayer();

            baseMaps = {
                "Streets": roads,
                "Satellite": satellite
            };
            leaflet.layerControl = L.control.layers(baseMaps, null, {position: 'bottomleft'}).addTo(leaflet);
            (config.layer in baseMaps ? baseMaps[config.layer] : roads).addTo(leaflet);
            leaflet.layerControl._container.style.display = (config.readonly ? 'none' : 'block');
        });
    } else if (config.engine === 'esri') {
        leaflet.setMaxZoom(18);
        // use vector maps if we have an API key
        if (config.api_key) {
            loadjs('js/esri-leaflet.js', function () {
                loadjs('js/esri-leaflet-vector.js', function () {
                    var roads = L.esri.Vector.vectorBasemapLayer("ArcGIS:Streets", {
                        apikey: config.api_key
                    });
                    var topology = L.esri.Vector.vectorBasemapLayer("ArcGIS:Topographic", {
                        apikey: config.api_key
                    });
                    var satellite = L.esri.Vector.vectorBasemapLayer("ArcGIS:Imagery", {
                        apikey: config.api_key
                    });

                    baseMaps = {
                        "Streets": roads,
                        "Topography": topology,
                        "Satellite": satellite
                    };
                    leaflet.layerControl = L.control.layers(baseMaps, null, {position: 'bottomleft'}).addTo(leaflet);
                    (config.layer in baseMaps ? baseMaps[config.layer] : roads).addTo(leaflet);
                    leaflet.layerControl._container.style.display = (config.readonly ? 'none' : 'block');
                });
            });
        } else {
            let attribution = 'Powered by <a href="https://www.esri.com/">Esri</a> | Esri Community Maps Contributors, Maxar, Microsoft, Iowa DNR, © OpenStreetMap, Microsoft, TomTom, Garmin, SafeGraph, GeoTechnologies, Inc, METI/NASA, USGS, EPA, NPS, US Census Bureau, USDA, USFWS';
            var roads = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
                attribution: attribution
            });
            var topology = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x', {
                attribution: attribution
            });
            var satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: attribution
            });

            baseMaps = {
                "Streets": roads,
                "Topography": topology,
                "Satellite": satellite
            };
            leaflet.layerControl = L.control.layers(baseMaps, null, {position: 'bottomleft'}).addTo(leaflet);
            (config.layer in baseMaps ? baseMaps[config.layer] : roads).addTo(leaflet);
            leaflet.layerControl._container.style.display = (config.readonly ? 'none' : 'block');
        }
    } else {
        leaflet.setMaxZoom(20);
        const tile_url = config.tile_url ? config.tile_url : '{s}.tile.openstreetmap.org';
        L.tileLayer('//' + tile_url + '/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(leaflet);
    }

    // disable all interaction
    if (config.readonly === true) {
        disable_map_interaction(leaflet)
    } else if (location.protocol === 'https:') {
        // can't request location permission without https
        leaflet.locateControl = L.control.locate().addTo(leaflet);
    }

    return leaflet;
}

function get_map(id) {
    if (window.maps) {
        return window.maps[id];
    }
}

function destroy_map(id) {
    const leaflet = get_map(id);
    if(id in window.maps) {
        leaflet.off();
        leaflet._container.classList.remove('leaflet-container', 'leaflet-touch', 'leaflet-retina', 'leaflet-fade-anim');
        leaflet.remove();
        delete window.maps[id];
    }
}

function populate_map_markers(map_id, group_radius = 10, status = [0,1], device_group = 0) {
    $.ajax({
        type: "POST",
        url: '/maps/getdevices',
        dataType: "json",
        data: { location_valid: 1, disabled: 0, disabled_alerts: 0, status: status, group: device_group },
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

            var markers = Object.values(data).map((device) => {
                var markerData = {title: device.sname};
                if (device.status) { // up
                    markerData.icon = greenMarker;
                    markerData.zIndexOffset = 0;
                } else if (device.maintenance == 1) { // down + maintenance
                    markerData.icon = blueMarker;
                    markerData.zIndexOffset = 10000;
                } else { // down
                    markerData.icon = redMarker;
                    markerData.zIndexOffset = 5000;
                }

                var marker = L.marker(new L.LatLng(device.lat, device.lng), markerData);
                marker.bindPopup(`<a href="${device.url}"><img src="${device.icon}" width="32" height="32" alt=""> ${device.sname}</a>`);
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

function disable_map_interaction(leaflet) {
    leaflet.zoomControl?.remove();
    delete leaflet.zoomControl;
    leaflet.locateControl?.stop();
    leaflet.locateControl?.remove();
    delete leaflet.locateControl;
    if (leaflet.layerControl) {
        leaflet.layerControl._container.style.display = 'none';
    }
    leaflet.dragging.disable();
    leaflet.touchZoom.disable();
    leaflet.doubleClickZoom.disable();
    leaflet.scrollWheelZoom.disable();
    leaflet.boxZoom.disable();
    leaflet.keyboard.disable();
    leaflet.tap?.disable();
    leaflet._container.style.cursor = 'default';
}

function enable_map_interaction(leaflet) {
    if (! leaflet.zoomControl) {
        leaflet.zoomControl = L.control.zoom().addTo(leaflet);
    }
    if (location.protocol === 'https:' && ! leaflet.locateControl) {
        // can't request location permission without https
        leaflet.locateControl = L.control.locate().addTo(leaflet);
    }
    if (leaflet.layerControl) {
        leaflet.layerControl._container.style.display = 'block';
    }
    leaflet.dragging.enable();
    leaflet.touchZoom.enable();
    leaflet.doubleClickZoom.enable();
    leaflet.scrollWheelZoom.enable();
    leaflet.boxZoom.enable();
    leaflet.keyboard.enable();
    leaflet.tap?.enable();
    leaflet._container.style.cursor = 'pointer';
}

function init_map_marker(leaflet, latlng) {
    let marker = L.marker(latlng);
    marker.addTo(leaflet);
    leaflet.setView(latlng);

    // move marker on drag
    leaflet.on('drag', function () {
        marker.setLatLng(leaflet.getCenter());
    });
    // center map on zoom
    leaflet.on('zoom', function () {
        leaflet.setView(marker.getLatLng());
    });

    return marker;
}

function setCustomMapBackground(id, type, data) {
    let image = '';
    let color = '';

    if(type === 'image') {
        image = `url(${data.image_url})`;
    } else if(type === 'color') {
        color = data.color;
    }
    $(`#${id} .vis-network canvas`)
        .css('background-image', image)
        .css('background-size', 'cover')
        .css('background-color', color);

    const mapBackgroundId = `${id}-bg-geo-map`;
    if (type === 'map') {
        $(`#${id}-bg-geo-map`).show();
        let config = data;
        config['readonly'] = true;
        init_map(mapBackgroundId, config)
            .setView(L.latLng(data.lat, data.lng), data.zoom);
    } else {
        // destroy the map if it exists
        destroy_map(mapBackgroundId)
    }
}

function update_location(id, latlng, callback) {
    $.ajax({
        method: 'PATCH',
        url: ajax_url + '/location/' + id,
        data: {lat: latlng.lat, lng: latlng.lng}
    }).done(function () {
        toastr.success('Location updated');
        typeof callback === 'function' && callback(true);
    }).fail(function (e) {
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

        toastr.error(msg);
        typeof callback === 'function' && callback(false);

    });
}

function http_fallback(link) {
    var url = link.getAttribute('href');
    try {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, false);
        xhr.timeout = 2000;
        xhr.send(null);

        if (xhr.status !== 200) {
            url = url.replace(/^https:\/\//, 'http://');
        }
    } catch (e) {
        url = url.replace(/^https:\/\//, 'http://');
    }

    window.open(url, '_blank');
    return false;
}

function init_select2(selector, type, data, selected, placeholder, config) {
    var $select = $(selector);

    // allow function to be assigned to pass data
    var data_function = function(params) {
        data.term = params.term;
        data.page = params.page || 1;
        return data;
    };
    if ($.isFunction(data)) {
        data_function = data;
    }

    var init = {
        theme: "bootstrap",
        dropdownAutoWidth : true,
        width: "auto",
        placeholder: placeholder,
        allowClear: true,
        ajax: {
            url: ajax_url + '/select/' + type,
            delay: 150,
            data: data_function
        }
    };

    // override init values
    if (typeof config === 'object') {
        var keys = Object.keys(config);
        for (var i = 0; i < keys.length; i++) {
            init[keys[i]] = config[keys[i]];
        }
    }

    $select.select2(init);

    if (selected) {
        if (typeof selected !== 'object') {
            selected = {id: selected, text: selected};
        }

        var newOption = new Option(selected.text, selected.id, true, true);
        $select.append(newOption).trigger('change');
    }
}

function humanize_duration(seconds) {
    // transform xxx seconds into yy years MM months dd days hh hours mm:ss

    var duration = moment.duration(Number(seconds), 's');
    var years = duration.years(),
        months = duration.months(),
        days = duration.days(),
        hrs = duration.hours(),
        mins = duration.minutes(),
        secs = duration.seconds();
    var res = '';

    if (years) {
        res += years + 'y ';
    }
    if (months) {
        res += months + 'm ';
    }
    if (days) {
        res += days + 'd ';
    }
    if (hrs) {
        res += hrs + 'h ';
    }
    res += mins + 'm ' + secs + 's ';

    return res;
}

function popUp(URL)
{
    window.open(URL, '_blank', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=550,height=600');
}

// popup component javascript.  Hopefully temporary.
document.addEventListener("alpine:init", () => {
    Alpine.data("popup", () => ({
        popupShow: false,
        showTimeout: null,
        hideTimeout: null,
        ignoreNextShownEvent: false,
        delay: 300,
        show(timeout) {
            clearTimeout(this.hideTimeout);
            this.showTimeout = setTimeout(() => {
                this.popupShow = true;
                Popper.createPopper(this.$refs.targetRef, this.$refs.popupRef, {
                    padding: 8
                });

                // close other popups, except this one
                this.ignoreNextShownEvent = true;
                this.$dispatch('librenms-popup-shown', this.$el);
            }, timeout);
        },
        hide(timeout) {
            if (this.ignoreNextShownEvent) {
                this.ignoreNextShownEvent = false;
                return;
            }

            clearTimeout(this.showTimeout);
            this.hideTimeout = setTimeout(() => this.popupShow = false, timeout)
        }
    }));
});
