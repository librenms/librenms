window.maps = {};

var oldH;
var oldW;
$(document).ready(function() {
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

function updateTimezone(tz, staticTz)
{
    $.post(timezoneUrl,
        {
            timezone: tz,
            static: staticTz
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
    $.post(resolutionUrl,
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

// Fix select2 search focus bug
$(document).on('select2:open', (e) => {
    const selectId = e.target.id

    $(".select2-search__field[aria-controls='select2-" + selectId + "-results']").each(function (key, value){
        value.focus();
    })
})

$(document).ready(function () {
    setInterval(function () {
        $('.bootgrid-table').each(function() {
            $(this).bootgrid('reload');
        });
    }, 300000);
});

function bootgridWithExport(grid, options) {
    const exportUrl = grid.data('export-url');
    if (!exportUrl) {
        return grid.bootgrid(options);
    }

    const gridId = grid.attr('id');
    const exportButton = $(
        '<div class="btn-group mr-2 bootgrid-export-button">' +
        '<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" disabled>' +
        '<i class="fa fa-download"></i> <span class="caret"></span>' +
        '</button>' +
        '<ul class="dropdown-menu">' +
        '<li><a class="export-link" data-export-type="visible"><i class="fa-solid fa-fw fa-file-csv"></i> Export page</a></li>' +
        '<li><a class="export-link" data-export-type="all"><i class="fa-solid fa-fw fa-file-csv"></i> Export all results</a></li>' +
        '</ul>' +
        '</div>'
    ).attr('id', gridId + '-export-button');

    let requestParams;
    let responseParams;
    let exportParams;

    grid.on('loaded.rs.jquery.bootgrid', function () {
        // Bootgrid also fires loaded after a failed request, without a response snapshot.
        exportParams = responseParams;
        responseParams = undefined;
        exportButton.children('button').prop('disabled', !exportParams);
    });

    grid.bootgrid({
        ...options,
        requestHandler(request) {
            const params = options.requestHandler ? options.requestHandler.call(this, request) : request;
            requestParams = new URLSearchParams($.param(params));
            requestParams.delete('_token');
            return params;
        },
        responseHandler(response) {
            const result = options.responseHandler ? options.responseHandler.call(this, response) : response;
            responseParams = requestParams;
            responseParams.set('current', result.current);
            return result;
        },
    });

    exportButton.find('.export-link').attr('href', exportUrl).on('click', function (event) {
        event.preventDefault();
        if (!exportParams) {
            return;
        }

        const params = new URLSearchParams(exportParams);
        if ($(this).data('export-type') === 'all') {
            params.delete('current');
            params.delete('rowCount');
        }

        const url = new URL(exportUrl, window.location.href);
        if (params.size) {
            url.search += (url.search ? '&' : '') + params.toString();
        }
        window.open(url.href, '_blank');
    });

    $(document.getElementById(gridId + '-header')).find('.actions').prepend(exportButton);

    return grid;
}

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

function build_tile_url(url) {
    url ||= '{s}.tile.openstreetmap.org';

    // protocol
    if (!/^([a-z][a-z0-9+.-]*:)?\/\//i.test(url)) {
        url = '//' + url;
    }

    // check for templates
    if (url.includes('{z}') && url.includes('{x}') && url.includes('{y}')) {
        return url;
    }

    // separate path and suffix
    const path = url.split(/[?#]/)[0];

    return path.replace(/\/+$/, '') + '/{z}/{x}/{y}.png' + url.slice(path.length);
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
        L.tileLayer(build_tile_url(config.tile_url), {
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

function setCustomMapBackground(id, type, data, network) {
    var container = document.getElementById(id);
    if (!container) {
        return;
    }

    if (network) {
        container._visNetwork = network;
    }
    var currentNetwork = container._visNetwork || (id === 'custom-map' && typeof window.network !== 'undefined' ? window.network : null);

    container._customMapBg = container._customMapBg || {};
    container._customMapBg.type = type;
    container._customMapBg.data = data;
    container._customMapBg.imageLoaded = false;
    container._customMapBg.image = null;

    $(`#${id} .vis-network canvas`)
        .css('background-image', '')
        .css('background-color', '');

    const mapBackgroundId = `${id}-bg-geo-map`;
    const altMapBackgroundId = `custom-map-bg-geo-map-${id.replace('custom-map-', '')}`;
    const mapBgElem = document.getElementById(mapBackgroundId) || document.getElementById(altMapBackgroundId);

    if (type === 'image' && data && data.image_url) {
        let img = new Image();
        img.onload = function () {
            if (container._customMapBg && container._customMapBg.type === 'image' && container._customMapBg.image === img) {
                container._customMapBg.imageLoaded = true;
                if (container._visNetwork) {
                    container._visNetwork.redraw();
                } else if (id === 'custom-map' && typeof window.network !== 'undefined') {
                    window.network.redraw();
                }
            }
        };
        img.src = data.image_url;
        container._customMapBg.image = img;
        if (img.complete && img.naturalWidth !== 0) {
            container._customMapBg.imageLoaded = true;
        }
    }

    if (type === 'map' && mapBgElem) {
        $(mapBgElem).show();
        let config = data || {};
        config['readonly'] = true;
        init_map(mapBgElem.id, config)
            .setView(L.latLng(data.lat, data.lng), data.zoom);
    } else {
        if (mapBgElem) {
            destroy_map(mapBgElem.id);
            $(mapBgElem).hide();
            mapBgElem.style.transform = 'none';
        }
    }

    if (currentNetwork) {
        attachCustomMapBackgroundHooks(container, currentNetwork, id);
        currentNetwork.redraw();
    }
}

function attachCustomMapBackgroundHooks(container, network, id) {
    if (!network || network._customMapBgHookAttached) {
        return;
    }
    network._customMapBgHookAttached = true;

    network.on('beforeDrawing', function (ctx) {
        var bg = container._customMapBg;
        if (!bg) {
            return;
        }

        var visDiv = $(container).children('.vis-network')[0];
        var clientW = visDiv ? $(visDiv).width() : $(container).width();
        var clientH = visDiv ? $(visDiv).height() : $(container).height();
        var mapWidth = container._mapWidth || clientW;
        var mapHeight = container._mapHeight || clientH;

        if (bg.type === 'color' && bg.data && bg.data.color) {
            ctx.fillStyle = bg.data.color;
            ctx.fillRect(0, 0, mapWidth, mapHeight);
        } else if (bg.type === 'image' && bg.imageLoaded && bg.image) {
            ctx.drawImage(bg.image, 0, 0, mapWidth, mapHeight);
        }

        if (bg.type === 'map') {
            var bgGeoMap = document.getElementById(`${id}-bg-geo-map`);
            if (bgGeoMap && bgGeoMap.style.zIndex !== '3') {
                var topLeft = network.canvasToDOM({x: 0, y: 0});
                var scale = network.getScale();
                bgGeoMap.style.transformOrigin = '0 0';
                bgGeoMap.style.transform = 'translate(' + topLeft.x + 'px, ' + topLeft.y + 'px) scale(' + scale + ')';
            }
        }
    });
}

function update_location(id, latlng, callback) {
    $.ajax({
        method: 'PATCH',
        url: route('location.update', {location: id}),
        data: {lat: latlng.lat, lng: latlng.lng}
    }).done(function () {
        toastr.success('Location updated');
        callback?.(true);
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
        callback?.(false);
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
    const $select = $(selector);

    // allow function to be assigned to pass data
    const data_function = $.isFunction(data) ? data : function (params) {
        return Object.assign({}, data, {
            term: params.term,
            page: params.page || 1
        });
    };

    const init = {
        theme: "bootstrap",
        dropdownAutoWidth: true,
        width: "auto",
        placeholder: placeholder,
        allowClear: true,
        containerCssClass: ":all:",
        ajax: {
            url: route('ajax.select.' + type),
            delay: 150,
            data: data_function,
            error: function (jqXHR) {
                if (jqXHR.status === 403 && jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    $select.data('select2-last-error', jqXHR.responseJSON.message);
                }
            }
        },
        language: {
            errorLoading: function () {
                var message = $select.data('select2-last-error') || "The results could not be loaded.";

                $select.removeData('select2-last-error');

                return message;
            }
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

function applySiteStyle(newStyle) {
    // translate device to actual style
    if (newStyle === 'device') {
        newStyle = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    document.documentElement.classList.toggle('dark', newStyle === 'dark');

    if (window.siteStyle !== newStyle) {
        window.siteStyle = newStyle;
        $.post(siteStyleUrl, { style: newStyle });
        document.querySelectorAll('img.graph-image').forEach(img => {
            img.src = img.src.replace(/&style=\w+/g, '') + '&style=' + newStyle;
        });
    }
}

// prevent dropdown menus from overflowing the viewport
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.dropdown-submenu:not(:has(.dropdown-submenu))').forEach(function (submenuParent) {
        const submenu = submenuParent.querySelector('.dropdown-menu');
        if (!submenu) return;

        submenuParent.addEventListener('mouseenter', function () {
            const rect = submenu.getBoundingClientRect();
            const availableHeight = window.innerHeight - rect.top - 10;

            if (rect.bottom > window.innerHeight) {
                submenu.style.maxHeight = availableHeight + 'px';
                submenu.style.overflowY = 'auto';
            }
        });

        submenuParent.addEventListener('mouseleave', function () {
            submenu.style.maxHeight = '';
        });
    });
});
