<div id="worldmap_widget-{{ $id }}" class="worldmap_widget" data-reload="false"></div>

<style>
/* --- ADDED: styling !-- */
#worldmap_widget-{{ $id }} {
  height: 100%;
  width: 100%;
  padding-bottom: 0;
  margin-bottom: 0;
  container-type: inline-size;


  /* Default theme values */
  --wm-popup-bg: #f2f2f2ee;
  --wm-popup-text: #1d1d1d;

  --wm-popup-font-size: 14px;
  --wm-popup-font-weight: bold;

  --wm-tooltip-font-size: 14px;
  --wm-popup-padding: 6px;
  --wm-link-popup-min-width: 300px;

  --wm-marker-popup-max-width: min(1260px, calc(100% - 24px));
  --wm-marker-popup-min-height: min(1260px, calc(100vw - 24px));

  --wm-graph-gap: 8px;
  --wm-graph-side-padding: 2px;
  --wm-graph-title-size: 14px;
  --wm-graph-title-margin: 0 0 6px 0;
}

#worldmap_widget-{{ $id }}[data-theme="dark"] {
  --wm-popup-bg: #1d1d1ddd;
  --wm-popup-text: #d3d3d3;
}

#worldmap_widget-{{ $id }}[data-theme="light"] {
  --wm-popup-bg: #f2f2f2ee;
  --wm-popup-text: #1d1d1d;
}

/* Popup content */
#worldmap_widget-{{ $id }} .leaflet-popup.marker-style .leaflet-popup-content-wrapper,
#worldmap_widget-{{ $id }} .leaflet-popup.link-style .leaflet-popup-content-wrapper {
  background: var(--wm-popup-bg) !important;
  color: var(--wm-popup-text) !important;
  font-size: var(--wm-popup-font-size);
  font-weight: var(--wm-popup-font-weight);
  padding: var(--wm-popup-padding);
}
#worldmap_widget-{{ $id }} .leaflet-popup.marker-style .leaflet-popup-content,
#worldmap_widget-{{ $id }} .leaflet-popup.link-style .leaflet-popup-content {
  background: transparent !important;
  color: inherit !important;
  margin: 8px !important;
}

/* Popup arrow positioning */
#worldmap_widget-{{ $id }} .leaflet-popup.marker-style .leaflet-popup-tip-container {
  margin-top: -20px;
  margin-left: -8px;
}
#worldmap_widget-{{ $id }} .leaflet-popup.link-style .leaflet-popup-tip-container {
  margin-top: -25px;
  margin-left: -8px;
}

/* Popup arrow fill */
#worldmap_widget-{{ $id }} .leaflet-popup.marker-style .leaflet-popup-tip,
#worldmap_widget-{{ $id }} .leaflet-popup.link-style .leaflet-popup-tip {
  background: var(--wm-popup-bg) !important;
  box-shadow: none !important;
}

/* Popup close button */
#worldmap_widget-{{ $id }} .leaflet-popup.marker-style .leaflet-popup-close-button,
#worldmap_widget-{{ $id }} .leaflet-popup.link-style .leaflet-popup-close-button {
  color: var(--wm-popup-text) !important;
}

/* Device popup dimensions */
#worldmap_widget-{{ $id }} .leaflet-popup.marker-style .leaflet-popup-content-wrapper {
  max-width: calc(100cqi - 16px) !important;
  max-height: calc(100dvh - 32px);
  box-sizing: border-box;
  overflow: hidden;
}
#worldmap_widget-{{ $id }} .leaflet-popup.marker-style .leaflet-popup-content {
  width: min(900px, calc(100cqi - 32px)) !important;
  max-width: calc(100cqi - 32px) !important;
  max-height: calc(100dvh - 64px);
  overflow-y: auto;
  overflow-x: hidden;
  box-sizing: border-box;
  /* -webkit-overflow-scrolling: touch; */
  margin: 8px !important;
}
#worldmap_widget-{{ $id }} .leaflet-popup.marker-style .device-popup {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}
#worldmap_widget-{{ $id }} .leaflet-popup.marker-style .device-popup__title {
  text-align: center;
  margin-bottom: 10px;
}
/* Device marker graph grid */
#worldmap_widget-{{ $id }} .leaflet-popup.marker-style .device-popup__graphs {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
}
#worldmap_widget-{{ $id }} .leaflet-popup.marker-style .device-popup__graph {
  min-width: 0;
}
#worldmap_widget-{{ $id }} .leaflet-popup.marker-style .device-popup__graph-title {
  text-align: center;
  margin-bottom: 6px;
  font-size: 12px;
  font-weight: 600;
}
#worldmap_widget-{{ $id }} .leaflet-popup.marker-style img.graph-image {
  display: block;
  width: 100%;
  height: auto;
  aspect-ratio: 20 / 9;
}
/* 2 columns */
@container (max-width: 900px) {
  #worldmap_widget-{{ $id }} .leaflet-popup.marker-style .device-popup__graphs {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
/* 1 column */
@container (max-width: 620px) {
  #worldmap_widget-{{ $id }} .leaflet-popup.marker-style .device-popup__graphs {
    grid-template-columns: 1fr;
  }
}

/* Link popup */
#worldmap_widget-{{ $id }} .leaflet-popup.link-style .leaflet-popup-content,
#worldmap_widget-{{ $id }} .leaflet-popup.link-style .leaflet-popup-content-wrapper {
  min-width: var(--wm-link-popup-min-width);
  margin-bottom: 12px;
}
#worldmap_widget-{{ $id }} .link-popup__title {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  text-align: center;
  flex-wrap: wrap;
}
#worldmap_widget-{{ $id }} .link-popup__arrow {
  opacity: 0.85;
}
#worldmap_widget-{{ $id }} .overlap-member-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  font-weight: normal;
  margin-top: 4px;
}
#worldmap_widget-{{ $id }} .overlap-member-sep {
  opacity: 0.7;
}

/* Link tooltip */
#worldmap_widget-{{ $id }} .leaflet-tooltip.leaflet-link-tooltip {
  background: var(--wm-popup-bg) !important;
  color: var(--wm-popup-text) !important;
  font-size: var(--wm-tooltip-font-size) !important;
  border: 1px solid var(--wm-popup-bg);
  box-shadow: 0 1px 3px rgba(0,0,0,0.4);
}
#worldmap_widget-{{ $id }} .leaflet-tooltip-left {
  margin-left: -7px;
}
#worldmap_widget-{{ $id }} .leaflet-tooltip-right {
  margin-left: 7px;
}
#worldmap_widget-{{ $id }} .leaflet-tooltip-left:before {
  border-left-color: var(--wm-popup-bg);
}
#worldmap_widget-{{ $id }} .leaflet-tooltip-right:before {
  border-right-color: var(--wm-popup-bg);
}

/* Overlapping links dropdown */
#worldmap_widget-{{ $id }} .overlap-summary {
    cursor: pointer;
    list-style: none;
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: normal;
}
#worldmap_widget-{{ $id }} .overlap-summary::before {
    content: "▶";
    font-size: 0.85em;
    line-height: 1;
}
#worldmap_widget-{{ $id }} details[open] > .overlap-summary::before {
    content: "▼";
}
#worldmap_widget-{{ $id }} .overlap-member {
  margin-bottom: 8px;
}
#worldmap_widget-{{ $id }} .overlap-members-list {
    text-align: left;
    max-height: 7.5em;
    overflow-y: auto;
    overflow-x: hidden;
    padding-right: 4px;
}
#worldmap_widget-{{ $id }} .link-overlap-count {
    pointer-events: none;
    opacity: 0.85;
}
#worldmap_widget-{{ $id }} .overlap-members-list .link-popup__title,
#worldmap_widget-{{ $id }} .overlap-members-list .overlap-member-meta {
  justify-content: flex-start;
  text-align: left;
  font-size: 14px;
}
#worldmap_widget-{{ $id }} .overlap-members-list .link-popup__title {
  align-items: flex-start;
}
/* !-- */
</style>

<script type="application/javascript">
    (function () {
        const map_id = 'worldmap_widget-{{ $id }}';
        const status = {{ Js::from($status) }};
        const device_group = {{ (int) $device_group }};
        const disabled_alerts = {{ Js::from($disabled_alerts) }};
        const map_config = {{ Js::from($map_config) }};
        const group_radius = {{ (int) $group_radius }};
        // --- ADDED !--
        const show_netmap = {{ Js::from($show_netmap ?? \LibreNMS\Config::get('network_map_show_on_worldmap')) }};
        const netmap_source = {{ Js::from($netmap_source ?? \LibreNMS\Config::get('network_map_worldmap_link_type')) }};
        document.getElementById(map_id).dataset.theme = String(window.siteStyle).toLowerCase().includes('dark') ? 'dark' : 'light';
        const enableGeoLinks = Boolean(show_netmap) && String(netmap_source).trim().toLowerCase() === 'xdp';
        // !--

        function populate_map_markers(map_id, group_radius = 10, status = [0,1], device_group = 0) {
            $.ajax({
                type: "POST",
                url: '{{ route('maps.getdevices') }}',
                dataType: "json",
                data: { location_valid: 1, disabled: 0, disabled_alerts: disabled_alerts, statuses: status, group: device_group },
                success: function (data) {
                    var markers = Object.values(data).map((device) => {
                        // --- UPDATED !--
                        const markerColor = device.status ? 'green' : (device.maintenance == 1 ? 'blue' : 'red');
                        const isNetworkWired = device.typeIcon === 'network-wired' && device.icon;
                        if (isNetworkWired) {
                             var deviceMarker = L.divIcon({
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
                            var deviceMarker = L.AwesomeMarkers.icon({
                                icon: device.typeIcon,
                                markerColor,
                                prefix: 'fa',
                                iconColor: 'white'
                            });
                        }
                        // !--

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
                        marker.deviceData = device; // --- ADDED
                        const marker_html = createMarkerHTML(device, device["id"]); // --- ADDED
                        // --- UPDATED !--
                        const widgetWidth = document.getElementById(map_id)?.clientWidth || 600;
                        const popupMaxWidth = Math.max(260, widgetWidth - 24);
                        marker.bindPopup(marker_html, {
                            className: 'marker-style',
                            maxWidth: popupMaxWidth,
                            autoPan: true,
                            keepInView: true,
                            autoPanPadding: [12, 12]
                        });
                        // !--
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
                                    const deviceData = markers[i].deviceData; // --- ADDED
                                    // --- UPDATED !--
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

        // --- ADDED: render network links (including overlapping), graph builders (same as fullscreen map, minus HREFs) !--
        var link_markers = Object.create(null);
        var link_overlap_labels = Object.create(null);

        function html_chars(str) {
            return String(str).replaceAll('&', '&amp;').replaceAll("'", '&apos;')
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
                return `
                    <div class="link-popup__title">
                        <span>${html_chars(link['local_name'])}</span>
                        <span class="link-popup__arrow">${arrow}</span>
                        <span>${html_chars(link['remote_name'])}</span>
                    </div>
                `;
            }

            return `
                <div class="link-popup__title">
                    <span>${link['local_lat']},${link['local_lng']}</span>
                    <span class="link-popup__arrow">${arrow}</span>
                    <span>${link['remote_lat']},${link['remote_lng']}</span>
                </div>
            `;
        }

        function createLinkHTML(link) {
            let html = '';

            const members = Array.isArray(link.members) && link.members.length ? link.members : [link];
            const primary = members[0];

            if (primary['local_device_id'] && primary['local_port_id']) {
                html = `<div style="text-align:center;">${createLinkTitle(primary)}<br><img class="graph-image" src="${createGraphURL(null, primary['local_port_id'],"port_bits","-1d","yes")}"></div>`;
            }
            else if (primary['remote_device_id'] && primary['remote_port_id']) {
                html = `<div style="text-align:center;">${createLinkTitle(primary)}<br><img class="graph-image" src="${createGraphURL(null, primary['remote_port_id'],"port_bits","-1d","yes")}"></div>`;
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
                        ${createLinkTitle(member)}
                        <div class="overlap-member-meta">
                            <span>${formatBps(member.speed)}</span>
                            <span class="overlap-member-sep">|</span>
                            <span>${formatStatus(member.up)}</span>
                        </div>
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

        function createMarkerHTML(device, device_id) { 
            return `<div class="device-popup">
                <div class="device-popup__title">
                    <span>${html_chars(device["sname"] ?? device_id)}</span>
                </div>

                <div class="device-popup__graphs">

                    <div class="device-popup__graph">
                        <div class="device-popup__graph-title">Device Traffic</div>
                        <img class="graph-image"
                            src="${createGraphURL(device_id, null, "device_bits", "-1d", "no", 400, 180)}">
                    </div>

                    <div class="device-popup__graph">
                        <div class="device-popup__graph-title">Processor Usage</div>
                        <img class="graph-image"
                            src="${createGraphURL(device_id, null, "device_processor", "-1d", "no", 400, 180)}">
                    </div>

                    <div class="device-popup__graph">
                        <div class="device-popup__graph-title">Memory Usage</div>
                        <img class="graph-image"
                            src="${createGraphURL(device_id, null, "device_mempool", "-1d", "no", 400, 180)}">
                    </div>

                </div>
            </div>`;
        }

        const __lnmsLinkMinWeight = 2;
        const __lnmsLinkMaxWeight = 3.5;
        const __lnmsLinkOpacity   = 0.3; // lower vals = more transparent
        function __lnmsClamp(num, min, max) {
            return Math.max(min, Math.min(max, num));
        }
        function __lnmsSetLinkBase(line, rawWeight) {
            if (!line || !line.options) return;
            line.options.__lnmsBaseWeight = rawWeight ?? line.options.weight ?? 1;
            var map = get_map(map_id);
            line.options.__lnmsBaseZoom = map.getZoom();
        }
        function __lnmsRescaleLink(line) {
            if (!line || typeof line.setStyle !== 'function' || !line.options) return;
            if (line.options.__lnmsBaseWeight === undefined) {
                __lnmsSetLinkBase(line);
            }
            var map = get_map(map_id);
            const scale = map.getZoomScale(
                map.getZoom(),
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

        function getMidpoint(latlngs) {
            const a = latlngs[0];
            const b = latlngs[1];
            return L.latLng(
                (a.lat + b.lat) / 2,
                (a.lng + b.lng) / 2
            );
        }
        function updateOverlapLabel(link_id, latlngs, count, bgColor) {
            var map = get_map(map_id);
            if (count <= 1) {
                if (link_overlap_labels[link_id]) {
                    map.removeLayer(link_overlap_labels[link_id]);
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
                    icon: makeOverlapIcon(count, bgColor),
                    interactive: false,
                });
                marker.addTo(map);
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
                if (document.getElementById(map_id)?.dataset.theme === 'dark') {
                    color = '#FFFFFF';
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

        function populate_map_links(map_id) {
            $.ajax({
                type: "POST",
                url: '{{ route('maps.getgeolinks') }}',
                dataType: "json",
                data: {
                    link_type: {{ Js::from($netmap_source ?? 'xdp') }},
                    group: {{ (int) $device_group }},
                    disabled_alerts: {{ Js::from($disabled_alerts) }}
                },
                success: function(data) {
                    const map = get_map(map_id);

                    Object.entries(data).forEach(([link_id, link]) => {
                        const tip = (link['local_name'] && link['remote_name']) ? `${link['local_name']} ${arrow} ${link['remote_name']}` : `${link['local_lat']},${link['local_lng']} ${arrow} ${link['remote_lat']},${link['remote_lng']}`;
                        const isUp = !!link['up'];
                        const downDash  = '6,6';
                        const linkStyle = isUp
                            ? {
                                color: link['color'],
                                weight: link['width'],
                                opacity: __lnmsLinkOpacity,
                                dashArray: '',
                            }
                            : {
                                color: '#e40606',
                                weight: link['width'],
                                opacity: 0.9,
                                dashArray: '6,6',
                            };
                        const latlng = [
                            L.latLng(link.local_lat, link.local_lng),
                            L.latLng(link.remote_lat, link.remote_lng)
                        ];

                        if (link_id in link_markers) {
                            const currentLink = link_markers[link_id];

                            currentLink.setStyle(linkStyle).setLatLngs(latlng);

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
                        } else {
                            const line = new L.polyline(latlng, linkStyle).addTo(map);
                            link_markers[link_id] = line;

                            line.bindTooltip(tip, { className: "leaflet-link-tooltip", sticky: true, opacity: 0.9 });

                            const popup_html = createLinkHTML(link);
                            const widgetWidth = document.getElementById(map_id)?.clientWidth || 600;
                            const popupMaxWidth = Math.max(240, widgetWidth - 24);
                            line.bindPopup(popup_html, {
                                className: 'link-style',
                                maxWidth: popupMaxWidth,
                                autoPan: true,
                                keepInView: true,
                                autoPanPadding: [12, 12]
                            });

                            __lnmsSetLinkBase(line, link['width']);
                            __lnmsRescaleLink(line);

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
                        }
                    });
                    Object.keys(link_markers).forEach((id) => {
                        if (!(id in data)) {
                            map.removeLayer(link_markers[id]);
                            delete link_markers[id];
                        }
                    });
                    // --- ADDED !--
                    if (typeof map.markerCluster.refreshClusters === 'function') {
                        map.markerCluster.refreshClusters();
                    }
                    // !--
                },
                error: err => toastr.error(err.statusText)
            });
        }
        // !--

        loadjs('js/leaflet.js', function () {
            loadjs('js/leaflet.markercluster.js', function () {
                loadjs('js/leaflet.awesome-markers.min.js', function () {
                    loadjs('js/L.Control.Locate.min.js', function () {
                        const map = init_map(map_id, map_config); // --- ADDED
                        map.setMaxZoom(19);                       // --- ADDED
                        map.scrollWheelZoom.disable();            // --- UPDATED
                        __lnmsLinkBaseZoom = map.getZoom();       // --- ADDED
                        populate_map_markers(map_id, group_radius, status, device_group);

                        // --- ADDED !--
                        if (enableGeoLinks) {
                            populate_map_links(map_id);
                            map.on('zoomend', __lnmsRescaleAllLinks);
                        }
                        // !--

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
                            // --- ADDED !--
                            if (enableGeoLinks) {
                                __lnmsLinkBaseZoom = get_map(map_id).getZoom();
                                populate_map_links(map_id);
                            }
                            // !--
                        }).on('destroy', function (event) {
                            destroy_map(map_id);
                        });
                    });
                });
            });
        });
    })();
</script>