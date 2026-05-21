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

  /* --- ADDED: styling for pop-ups !-- */
    #fullscreen-map {
      /* Default theme values */
      --wm-popup-bg: #f2f2f2ee;
      --wm-popup-text: #1d1d1d;

      --wm-popup-font-size: 14px;
      --wm-popup-font-weight: bold;

      --wm-popup-padding: 6px;
      --wm-tooltip-font-size: 14px;

      --wm-marker-popup-max-width: calc(100vw - 24px);
      --wm-marker-popup-min-height: min(1260px, calc(100vw - 24px));

      --wm-link-popup-min-width: 300px;

      --wm-graph-gap: 8px;
      --wm-graph-side-padding: 2px;
      --wm-graph-title-size: 14px;
      --wm-graph-title-margin: 0 0 6px 0;
    }

    #fullscreen-map[data-theme="dark"] {
      --wm-popup-bg: #1d1d1ddd;
      --wm-popup-text: #d3d3d3;
    }

    #fullscreen-map[data-theme="light"] {
      --wm-popup-bg: #f2f2f2ee;
      --wm-popup-text: #1d1d1d;
    }

    /* Popup content */
    .leaflet-popup.marker-style .leaflet-popup-content,
    .leaflet-popup.marker-style .leaflet-popup-content-wrapper,
    .leaflet-popup.link-style .leaflet-popup-content,
    .leaflet-popup.link-style .leaflet-popup-content-wrapper {
      background: var(--wm-popup-bg) !important;
      color: var(--wm-popup-text) !important;
      font-size: var(--wm-popup-font-size);
      font-weight: var(--wm-popup-font-weight);
      padding: var(--wm-popup-padding);
    }

    /* Popup arrow fill */
    .leaflet-popup.marker-style .leaflet-popup-tip,
    .leaflet-popup.link-style .leaflet-popup-tip {
      background: var(--wm-popup-bg) !important;
    }

    /* Popup close button */
    .leaflet-popup.marker-style .leaflet-popup-close-button,
    .leaflet-popup.link-style .leaflet-popup-close-button {
      color: var(--wm-popup-text) !important;
    }

    /* Device popup dimensions */
    .leaflet-popup.marker-style .leaflet-popup-content-wrapper {
      max-width: var(--wm-marker-popup-max-width) !important;
      max-height: calc(100dvh - 32px);
      box-sizing: border-box;
      overflow: hidden;
    }
    .leaflet-popup.marker-style .leaflet-popup-content {
      margin: 8px !important;
      width: min(1260px, calc(100vw - 48px)) !important;
      max-width: calc(100vw - 48px) !important;
      max-height: calc(100dvh - 64px);
      overflow-y: auto;
      overflow-x: hidden;
      box-sizing: border-box;
      /* -webkit-overflow-scrolling: touch; */
    }
    .leaflet-popup.marker-style .device-popup {
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
    }
    .leaflet-popup.marker-style .device-popup__title {
      text-align: center;
      margin-bottom: 10px;
    }
    /* Device marker graph grid */
    .leaflet-popup.marker-style .device-popup__graphs {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 8px;
    }
    .leaflet-popup.marker-style .device-popup__graph {
      min-width: 0;
    }
    .leaflet-popup.marker-style .device-popup__graph-title {
      text-align: center;
      margin-bottom: 6px;
      font-size: 14px;
    }
    .leaflet-popup.marker-style .device-popup__graph-link {
      display: block;
      padding-inline: 2px;
    }
    .leaflet-popup.marker-style img.graph-image {
      display: block;
      width: 100%;
      height: auto;
      aspect-ratio: 20 / 9;
    }
    /* 2 columns */
    @media (max-width: 1100px) {
      .leaflet-popup.marker-style .device-popup__graphs {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }
    /* 1 column */
    @media (max-width: 750px) {
      .leaflet-popup.marker-style .device-popup__graphs {
        grid-template-columns: 1fr;
      }
    }

    /* Link popup */
    .leaflet-popup.link-style .leaflet-popup-content,
    .leaflet-popup.link-style .leaflet-popup-content-wrapper {
      min-width: var(--wm-link-popup-min-width);
    }

    /* Link tooltip */
    .leaflet-tooltip.leaflet-link-tooltip {
      background: var(--wm-popup-bg) !important;
      color: var(--wm-popup-text) !important;
      font-size: var(--wm-tooltip-font-size) !important;
      border: var(--wm-popup-bg);
      box-shadow: 0 1px 3px rgba(0,0,0,0.4);
    }
    .leaflet-tooltip-left:before {
      border-left-color: var(--wm-popup-bg);
    }
    .leaflet-tooltip-right:before {
      border-right-color: var(--wm-popup-bg);
    }

    /* Overlapping links dropdown */
    .overlap-summary {
      cursor: pointer;
      list-style: none;
      display: flex;
      align-items: center;
      gap: 6px;
      font-weight: normal;
    }
    .overlap-summary::before {
      content: "▶";
      font-size: 0.85em;
      line-height: 1;
    }
    details[open] > .overlap-summary::before {
      content: "▼";
    }
    .overlap-members-list {
      max-height: 7.5em;
      overflow-y: auto;
      overflow-x: hidden;
      padding-right: 4px;
    }
    .link-overlap-count {
      pointer-events: none;
      opacity: 0.85;
    }
  /* !-- */
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
    // --- ADDED !--
    device_map.setMaxZoom(19);
    const fullScreenMapElem = document.getElementById('fullscreen-map');
    if (fullScreenMapElem) {
        fullScreenMapElem.dataset.theme = String(window.siteStyle).toLowerCase().includes('dark') ? 'dark' : 'light';
    }
    // !--

    var device_marker_cluster = L.markerClusterGroup({
        maxClusterRadius: {{$group_radius}},
        iconCreateFunction: function (cluster) {
            var markers = cluster.getAllChildMarkers();
            // --- DELETE: unused var
            var color = "green" // --- UPDATED
            const newClass = "Cluster marker-cluster marker-cluster-small leaflet-zoom-animated leaflet-clickable"; // --- UPDATED
            for (var i = 0; i < markers.length; i++) {
                // --- UPDATED !--
                const deviceData = markers[i].deviceData;
                if (!deviceData) {
                    continue;
                }
                if (!deviceData.status && deviceData.maintenance != 1) {
                    color = "red";
                    break;
                }
                if (deviceData.maintenance == 1) {
                    color = "blue";
                }
                // !--
            }
            return L.divIcon({ html: cluster.getChildCount(), className: color+newClass, iconSize: L.point(40, 40) });
        },
    });
    device_map.addLayer(device_marker_cluster);

    // --- ADDED !--
    const link_layer = L.layerGroup();
    device_map.addLayer(link_layer);
    // !--

    var device_markers = Object.create(null); // --- UPDATED
    var link_markers = Object.create(null); // --- UPDATED
    var link_overlap_labels = Object.create(null); // --- ADDED

    // --- ADDED: pane for overlapping link labels, network link line width scaling with zoom level, URL and graph builders, device icon cache !--
    device_map.createPane('linkLabelsPane');
    device_map.getPane('linkLabelsPane').style.zIndex = 450;
    device_map.getPane('linkLabelsPane').style.pointerEvents = 'none';

    const __lnmsLinkMinWeight = 2;
    const __lnmsLinkMaxWeight = 3.5;
    const __lnmsLinkOpacity   = 0.3; // lower vals = more transparent
    function __lnmsClamp(num, min, max) {
        return Math.max(min, Math.min(max, num));
    }
    function __lnmsSetLinkBase(line, rawWeight) {
        if (!line || !line.options) return;
        line.options.__lnmsBaseWeight = rawWeight ?? line.options.weight ?? 1;
        line.options.__lnmsBaseZoom = device_map.getZoom();
    }
    function __lnmsRescaleLink(line) {
        if (!line || typeof line.setStyle !== 'function' || !line.options) return;
        if (line.options.__lnmsBaseWeight === undefined) {
            __lnmsSetLinkBase(line);
        }
        const scale = device_map.getZoomScale(
            device_map.getZoom(),
            line.options.__lnmsBaseZoom
        );
        const newWeight = __lnmsClamp(
            line.options.__lnmsBaseWeight * Math.pow(scale, 0.5),
            __lnmsLinkMinWeight,
            __lnmsLinkMaxWeight
        );
        line.setStyle({ weight: newWeight });
    }
    function __lnmsRescaleAllLinks() {
        for (const id in link_markers) {
            if (Object.prototype.hasOwnProperty.call(link_markers, id)) {
                __lnmsRescaleLink(link_markers[id]);
            }
        }
    }
    device_map.on('zoomend', __lnmsRescaleAllLinks);

    function getMidpoint(latlngs) {
        const a = latlngs[0];
        const b = latlngs[1];
        return L.latLng(
            (a.lat + b.lat) / 2,
            (a.lng + b.lng) / 2
        );
    }
    function updateOverlapLabel(link_id, latlngs, count, bgColor) {
        if (count <= 1) {
            if (link_overlap_labels[link_id]) {
                device_map.removeLayer(link_overlap_labels[link_id]);
                delete link_overlap_labels[link_id];
            }
            return;
        }
        const midpoint = getMidpoint(latlngs);
        if (link_overlap_labels[link_id]) {
            link_overlap_labels[link_id]
                .setLatLng(midpoint)
                .setIcon(makeOverlapIcon(count, bgColor));
        } else {
            const marker = L.marker(midpoint, {
                pane: 'linkLabelsPane',
                icon: makeOverlapIcon(count, bgColor),
                interactive: false,
            });
            marker.addTo(device_map);
            link_overlap_labels[link_id] = marker;
        }
    }

    function hexToRgba(hex, alpha = 0.35) {
        if (!hex) {
            return `rgba(0, 0, 0, ${alpha})`;
        }
        let value = String(hex).trim();
        if (value.startsWith('rgba(') || value.startsWith('rgb(')) {
            return value;
        }
        value = value.replace('#', '');
        if (value.length === 3) {
            value = value.split('').map(ch => ch + ch).join('');
        }
        // ~ source: https://www.frontendgeek.com/blogs/how-to-convert-hex-to-rgb-in-javascript#gsc.tab=0
        const r = parseInt(value.substring(0, 2), 16);
        const g = parseInt(value.substring(2, 4), 16);
        const b = parseInt(value.substring(4, 6), 16);
        // ~
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }
    function getFontColor(hex) {
        if (!hex) {
            return `#030303c9`;
        }
        var rgb = hexToRgba(hex).match(/\d+/g); // source: https://stackoverflow.com/questions/3751877/how-to-extract-r-g-b-a-values-from-css-color
        const r = rgb[0];
        const g = rgb[1];
        const b = rgb[2];
        // ~ source: https://www.codegenes.net/blog/how-to-decide-font-color-in-white-or-black-depending-on-background-color/#practical-implementation-code-examples
        const linearize = (s) => {
            s /= 255;
            return s <= 0.03928 ? s / 12.92 : Math.pow((s + 0.055) / 1.055, 2.4);
        };
        const luminance = 0.2126 * linearize(r) + 0.7152 * linearize(g) + 0.0722 * linearize(b);
        const contrastBlack = (luminance + 0.05) / 0.05;
        const contrastWhite = 1.05 / (luminance + 0.05);
        return contrastBlack > contrastWhite ? 'black' : 'white';
        // ~
    }

    function makeOverlapIcon(count, bgColor) {
        const bg = hexToRgba(bgColor, 0.35);
        return L.divIcon({
            className: 'link-overlap-count',
            html: `<div style="
                background:${bg};
                color: ${getFontColor(bgColor)};
                line-height: 18px;
                text-align: center;
                border-radius: 50%;
                font-size: 10px;
            ">${count}</div>`,
            iconSize: [18, 18],
            iconAnchor: [11, 11],
        });
    }

    function html_chars(str) {
        return String(str).replaceAll('&', '&amp;').replaceAll("'", '&apos;')
    }

    function getSpeedColor(bps) {
        const gbps = bps / 1000000000;
        if (gbps >= 100) return '#00C49F'; // 100G green
        if (gbps >= 40)  return '#7641E8'; // 40G purple
        if (gbps >= 25)  return '#3261C7'; // 25G blue
        if (gbps >= 10)  return '#E66A2E'; // 10G orange
        return '#222222';                  // <10G black
    }

    function formatBps(bps) { 
        const n = Number(bps || 0);
        let color = '';

        const gbps = n / 1000000000;
        const mbps = n / 1000000;
        let text = '';

        if (gbps < 10) {
            if (document.getElementById('fullscreen-map')?.dataset.theme === 'dark') {
                color = '#FFFFF';
            }
            else {
                color = '#222222';
            }
        }
        else {
            color = getSpeedColor(n);
        }

        if (gbps >= 1) {
            text = `${gbps % 1 === 0 ? gbps.toFixed(0) : gbps.toFixed(1)}G`;
        } else {
            text = `${mbps % 1 === 0 ? mbps.toFixed(0) : mbps.toFixed(1)}M`;
        }

        return `<span style="color:${color}; font-weight:600;">${text}</span>`;
    }

    function formatStatus(isUp) {
        const text = isUp ? 'up' : 'down';
        const color = isUp ? '#00A65A' : '#e40606';
        return `<span style="color:${color}; font-weight:600;">${text}</span>`;
    }

    function createDeviceHREF(device_id) {
        return `device/${device_id}`;
    }
    function createGraphHREF(device_id, port_id) {
        return `device/device=${device_id}/tab=port/port=${port_id}/`;
    }

    function createGraphURL(device_id=null, port_id=null, type="port_bits", time="-1d", legend="no", width=300, height=150) {
        const refresh = 0;
        if (device_id === null) {
            return `graph.php?from=${time}&id=${port_id}&type=${type}&legend=${legend}&absolute_size=0&width=${width}&height=${height}&refreshnum=${refresh}`;
        }
        return `graph.php?from=${time}&device=${device_id}&type=${type}&legend=${legend}&absolute_size=0&width=${width}&height=${height}&refreshnum=${refresh}`;
    }

    const arrow = "\u{2194}";
    function createLinkTitle(link) {
        if (link['local_name'] && link['remote_name']) {
            if (link['local_device_id'] && link['remote_device_id']) {
                return `<a href="${createDeviceHREF(link['local_device_id'])}" target="_blank" rel="noopener">${html_chars(link['local_name'])}</a> ${arrow} <a href="${createDeviceHREF(link['remote_device_id'])}" target="_blank" rel="noopener">${html_chars(link['remote_name'])}</a>`;
            }
            return `<p>${link['local_name']} ${arrow} ${link['remote_name']}</p>`;
        }
        return `<p>${link['local_lat']},${link['local_lng']} ${arrow} ${link['remote_lat']},${link['remote_lng']}</p>`;
    }

    function createLinkHTML(link) {
        let html = '';

        const members = Array.isArray(link.members) && link.members.length ? link.members : [link];
        const primary = members[0];

        if (primary['local_device_id'] && primary['local_port_id']) {
            html = `<div style="text-align:center;">${createLinkTitle(primary)}<br><a href="${createGraphHREF(primary['local_device_id'], primary['local_port_id'])}" target="_blank" rel="noopener"><img class="graph-image" src="${createGraphURL(null, primary['local_port_id'],"port_bits","-1d","yes")}"></a></div>`;
        }
        else if (primary['remote_device_id'] && primary['remote_port_id']) {
            html = `<div style="text-align:center;">${createLinkTitle(primary)}<br><a href="${createGraphHREF(primary['remote_device_id'], primary['remote_port_id'])}" target="_blank" rel="noopener"><img class="graph-image" src="${createGraphURL(null, primary['remote_port_id'],"port_bits","-1d","yes")}"></a></div>`;
        }
        else {
            html = `<div style="text-align:center;">${createLinkTitle(primary)}</div>`;
        }

        if (members.length <= 1) {
            return html;
        }

        let otherMembers = members.slice(1).map(member => {
            return `
                <div style="margin-bottom:8px;">
                    ${createLinkTitle(member)}<br>
                    <small style="font-weight:normal;">
                        &nbsp; <a href="graphs/to=0/id=${member['local_port_id']}/type=port_bits/from=0/" target="_blank" rel="noopener">traffic</a>
                        &nbsp;|&nbsp; <a href=${createGraphHREF(member['local_device_id'], member['local_port_id'])} target="_blank" rel="noopener">${formatBps(member.speed)}</a>
                        &nbsp;|&nbsp; <a href=${createGraphHREF(member['local_device_id'], member['local_port_id'])} target="_blank" rel="noopener">${formatStatus(member.up)}</a>
                    </small>
                </div>
            `;
        }).join('');

        return `
            ${html}
            <details style="margin-top:10px;">
                <summary class="overlap-summary">
                    Other overlapping links (${members.length - 1})
                </summary>
                <div class="overlap-members-list" style="margin-top:10px;">
                    ${otherMembers}
                </div>
            </details>
        `;
    }

    function createMarkerHTML(device, device_id) { //"<a href=\"" +  + "\"><img src=\"" + device["icon"] + "\" width=\"32\" height=\"32\" alt=\"\"> " + device["sname"] + "</a>"
        return `<div class="device-popup">
            <div class="device-popup__title">
                <a style="font-size:16px;"
                   href="${device["url"]}"
                   target="_blank"
                   rel="noopener">
                   ${html_chars(device["sname"] ?? device_id)}
                </a>
            </div>

            <div class="device-popup__graphs">

                <div class="device-popup__graph">
                    <div class="device-popup__graph-title">Device Traffic</div>
                    <a class="device-popup__graph-link"
                       href="graphs/device=${device_id}/type=device_bits/legend=no/"
                       target="_blank" rel="noopener">
                        <img class="graph-image"
                             src="${createGraphURL(device_id, null, "device_bits", "-1d", "no", 400, 180)}">
                    </a>
                </div>

                <div class="device-popup__graph">
                    <div class="device-popup__graph-title">Processor Usage</div>
                    <a class="device-popup__graph-link"
                       href="device/device=${device_id}/tab=health/metric=processor/"
                       target="_blank" rel="noopener">
                        <img class="graph-image"
                             src="${createGraphURL(device_id, null, "device_processor", "-1d", "no", 400, 180)}">
                    </a>
                </div>

                <div class="device-popup__graph">
                    <div class="device-popup__graph-title">Memory Usage</div>
                    <a class="device-popup__graph-link"
                       href="device/device=${device_id}/tab=health/metric=mempool/"
                       target="_blank" rel="noopener">
                        <img class="graph-image"
                             src="${createGraphURL(device_id, null, "device_mempool", "-1d", "no", 400, 180)}">
                    </a>
                </div>

            </div>
        </div>`;
    }

    const iconCache = Object.create(null);

    function getDeviceIcon(device) {
        const markerColor = device.status ? 'green' : (device.maintenance == 1 ? 'blue' : 'red');
        const isNetworkWired = device.typeIcon === 'network-wired' && device.icon;
        const key = isNetworkWired
            ? `nwimg|${device.icon}|${markerColor}`
            : `${device.typeIcon}|${markerColor}`;
        if (!iconCache[key]) {
            if (isNetworkWired) {
                iconCache[key] = L.divIcon({
                    className: `awesome-marker awesome-marker-icon-${markerColor}`,
                    html: `
                    <div style="
                        position:absolute;
                        top:6px; left:6px; right:6px;
                        width:22px; height:22px;
                        margin:0 auto;
                        border-radius:50%;
                        overflow:hidden;
                        display:flex; align-items:center; justify-content:center;">
                        <img src="${device.icon}" alt="" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                    </div>
                    `,
                    iconSize: [35, 45],
                    iconAnchor: [17, 42],
                    popupAnchor: [1, -32],
                });
            } else {
                iconCache[key] = L.AwesomeMarkers.icon({
                    icon: device.typeIcon,
                    markerColor,
                    prefix: 'fa',
                    iconColor: 'white'
                });
            }
        }
        return iconCache[key];
    }
    // !--

    function checkParentLink(device, parent) {
        const line_id = "dev." + device["id"] + "." + parent["id"]; // --- UPDATED
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
            link_layer.addLayer(line); // --- UPDATED
        }
        return line_id;
    }

    function refreshMap() {
        var devices = Object.create(null); // --- UPDATED
        var links = Object.create(null); // --- UPDATED
        var device_group = {{ $group_id ? $group_id : 'null' }};
@if($show_netmap && $netmap_source == 'depends')
        $.post( '{{ route('maps.getdevices') }}', {disabled: 0, location_valid: 1, disabled_alerts: {{$netmap_include_disabled_alerts}}, group: device_group, link_type: '{{$netmap_source}}'})
@else
        $.post( '{{ route('maps.getdevices') }}', {disabled: 0, location_valid: 1, disabled_alerts: {{$netmap_include_disabled_alerts}}, group: device_group})
@endif
            .done(function( data ) {
                $.each( data, function( device_id, device ) {

                    const icon = getDeviceIcon(device); // --- UPDATED

                    var z_offset = 0;
                    if (device["status"] == 0) {
                        if (device["maintenance"] != 0) {
                            z_offset = 5000;
                        } else {
                            z_offset = 10000;
                        }
                    }
                    if(device_id in device_markers) {
                        device_markers[device_id].setLatLng(new L.LatLng(device["lat"], device["lng"]));
                        device_markers[device_id].setZIndexOffset(z_offset);
                        device_markers[device_id].deviceData = device; // --- ADDED
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
                        marker.deviceData = device; // --- ADDED
                        const marker_html = createMarkerHTML(device, device_id); // --- ADDED
                        marker.bindPopup(marker_html, { className: 'marker-style' }); // --- UPDATED
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
                        delete device_markers[device_id]; // --- ADDED
                    }
                });
                // --- ADDED !--
                if (typeof device_marker_cluster.refreshClusters === 'function') {
                    device_marker_cluster.refreshClusters();
                }
                // !--
@if($show_netmap && $netmap_source == 'depends')
                // Remove any links that have disappeared
                $.each( link_markers, function( link_id, line ) {
                    if(! (link_id in links)) {
                        link_layer.removeLayer(line); // --- UPDATED
                        delete link_markers[link_id]; // --- ADDED
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
                $.each( data, function( link_id, link ) {
                    // --- ADDED: styling - links up/down, tooltips !--
                    const tip = (link['local_name'] && link['remote_name']) ? `${link['local_name']} ${arrow} ${link['remote_name']}` : `${link['local_lat']},${link['local_lng']} ${arrow} ${link['remote_lat']},${link['remote_lng']}`;
                    const isUp = !!link['up'];
                    const downDash  = '6,6'; // dashed line pattern
                    const linkStyle = isUp
                        ? {
                            color: link['color'],
                            weight: link['width'],
                            opacity: __lnmsLinkOpacity,
                            smoothFactor: 1,
                            dashArray: '',
                        }
                        : {
                            color: '#e40606',
                            weight: link['width'],
                            opacity: 0.9,
                            smoothFactor: 1,
                            dashArray: downDash,
                        };
                    const latlng = [
                        new L.LatLng(link['local_lat'],  link['local_lng']),
                        new L.LatLng(link['remote_lat'], link['remote_lng'])
                    ];
                    // !--
                    if(link_id in link_markers) {
                        const currentLink = link_markers[link_id]; // --- ADDED

                        currentLink.setLatLngs(latlng).setStyle(linkStyle); // --- UPDATED

                        // --- ADDED !--
                        if (currentLink.getTooltip()) {
                            currentLink.setTooltipContent(tip);
                        } else {
                            currentLink.bindTooltip(tip, { className: "leaflet-link-tooltip", sticky: true, opacity: 0.9 });
                        }

                        const popup_html = createLinkHTML(link);
                        if (currentLink.getPopup()) {
                            currentLink.setPopupContent(popup_html);
                        } else {
                            currentLink.bindPopup(popup_html, { className: 'link-style' });
                        }

                        __lnmsSetLinkBase(currentLink, link['width']);
                        __lnmsRescaleLink(currentLink);

                        if (!isUp) {
                            currentLink.bringToFront();
                        } else {
                            currentLink.bringToBack();
                        }

                        const labelColor = isUp ? link['color'] : '#e40606';
                        const overlapCount = Array.isArray(link.members) ? link.members.length : 1;
                        const sameLocation = link['local_lat'] === link['remote_lat'] && link['local_lng'] === link['remote_lng'];
                        if (!sameLocation) {
                            updateOverlapLabel(link_id, latlng, overlapCount, labelColor);
                        } else {
                            updateOverlapLabel(link_id, latlng, 1, labelColor);
                        }
                        // !--

                        links[link_id] = true;
                    } else {
                        var line = new L.Polyline(latlng, linkStyle); // --- UPDATED

                        link_markers[link_id] = line; // --- MOVED before scaling

                        // --- ADDED !--
                        line.bindTooltip(tip, { className: "leaflet-link-tooltip", sticky: true, opacity: 0.9 });

                        const popup_html = createLinkHTML(link);
                        line.bindPopup(popup_html, { className: 'link-style' });

                        __lnmsSetLinkBase(line, link['width']);
                        __lnmsRescaleLink(line);
                        // !--

                        link_layer.addLayer(line); // --- UPDATED

                        // --- ADDED !--
                        if (!isUp) {
                            line.bringToFront();
                        } else {
                            line.bringToBack();
                        }

                        const labelColor = isUp ? link['color'] : '#e40606';
                        const overlapCount = Array.isArray(link.members) ? link.members.length : 1;
                        const sameLocation = link['local_lat'] === link['remote_lat'] && link['local_lng'] === link['remote_lng'];
                        if (!sameLocation) {
                            updateOverlapLabel(link_id, latlng, overlapCount, labelColor);
                        } else {
                            updateOverlapLabel(link_id, latlng, 1, labelColor);
                        }
                        // !--

                        links[link_id] = true;
                   }
                })
                $("#countdown").css("border", "1px solid green");

                // Remove any links that have disappeared
                $.each( link_markers, function( link_id, line ) {
                    if(! (link_id in links)) {
                        link_layer.removeLayer(line); // --- UPDATED
                        delete link_markers[link_id]; // --- ADDED

                        // --- ADDED !--
                        if (link_overlap_labels[link_id]) {
                            device_map.removeLayer(link_overlap_labels[link_id]);
                            delete link_overlap_labels[link_id];
                        }
                        // !--
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