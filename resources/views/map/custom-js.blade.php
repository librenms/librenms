@once
<div id="custom-map-hover-popup"
     class="tw:hidden tw:fixed tw:z-50 tw:w-min tw:max-w-[calc(100vw-24px)] tw:max-h-[calc(100vh-80px)] tw:overflow-y-auto tw:overflow-x-hidden tw:bg-white tw:dark:bg-dark-gray-300 tw:text-slate-800 tw:dark:text-white tw:border-2 tw:border-gray-200 tw:dark:border-dark-gray-200 tw:rounded-xl tw:shadow-2xl tw:text-sm tw:pointer-events-auto">
  <div id="custom-map-hover-popup-content"></div>
</div>

<div id="custom-map-context-menu"
     class="tw:hidden tw:fixed tw:z-50 tw:min-w-48 tw:bg-white tw:dark:bg-dark-gray-400 tw:text-slate-800 tw:dark:text-dark-white-100 tw:border tw:border-gray-200 tw:dark:border-dark-gray-100 tw:rounded-lg tw:shadow-xl tw:py-1 tw:text-sm">
  <div id="context-menu-header" class="tw:px-3 tw:py-1.5 tw:font-bold tw:border-b tw:border-gray-100 tw:dark:border-dark-gray-300 tw:text-xs tw:text-slate-500 tw:dark:text-dark-white-300 tw:truncate"></div>
  <div id="context-menu-items" class="tw:flex tw:flex-col"></div>
</div>

<style id="custom-map-shared-style">
    .vis-tooltip { display: none !important; }
    #custom-map-hover-popup .panel { margin-bottom: 0; border: none; background: transparent; box-shadow: none; }
    #custom-map-hover-popup .panel-body { padding: 0; }
</style>
@endonce

<script type="text/javascript" src="{{ asset('js/vis-network.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/vis-data.min.js') }}"></script>
<script type="text/javascript">
    var custommap = {
        popupCache: {},
        _hoverTimeout: null,
        _hideTimeout: null,
        baseUrl: '',

        legendPctDefaultColour: function (pct) {
            if (pct < 0) {
                return "black";
            } else if (pct < 50) {
                // 100% green and slowly increase the red until we get to yellow
                return '#' + parseInt(5.1 * pct).toString(16).padStart(2, 0) + 'ff00';
            } else if (pct < 100) {
                // 100% red and slowly remove green to go from yellow to red
                return '#ff' + parseInt(5.1 * (100.0 - pct)).toString(16).padStart(2, 0) + '00';
            } else if (pct < 150) {
                // 100% red and slowly increase blue to go purple
                return '#ff00' + parseInt(5.1 * (pct - 100.0)).toString(16).padStart(2, 0);
            }

            // Default to purple for links over 150%
            return '#ff00ff';
        },

        redrawDefaultLegend: function (nodes, num_steps, x_pos, y_pos, font_size, hide_invalid, hide_overspeed, colours) {
            // Clear out the old legend
            var old_nodes = nodes.get({filter: function(node) { return node.id.startsWith("legend_") }});
            old_nodes.forEach(function (node) {
                nodes.remove(node.id);
            });
            if (x_pos >= 0) {
                font_size = font_size;
                y_pos = y_pos;
                x_pos = x_pos;
                var y_inc = font_size + 10;

                var legend_header = {id: "legend_header", label: "<b>{{ trans('map.custom.view.legend') }}</b>", shape: "box", borderWidth: 0, x: x_pos, y: y_pos, font: {multi: 'html', size: font_size}, color: {background: "white"}, scaling: {min: 10, max: 30, label: {enabled: false, drawThreshold: 0, maxVisible: 100000}}};
                nodes.add(legend_header);
                y_pos += y_inc;

                if (!(Boolean(hide_invalid))) {
                    var this_colour = "black";
                    if(colours) {
                        this_colour = colours['-1'];
                    }
                    var legend_invalid = {id: "legend_invalid", label: "{{ trans('map.custom.view.unknown') }}", title: "{{ trans('map.custom.view.invalid_link') }}", shape: "box", borderWidth: 0, x: x_pos, y: y_pos, font: {face: 'courier new', size: font_size, color: "white"}, color: {background: this_colour}, scaling: {min: 10, max: 30, label: {enabled: false, drawThreshold: 0, maxVisible: 100000}}};
                    y_pos += y_inc;
                    nodes.add(legend_invalid);
                }

                if(colours) {
                    var i = 0;
                    Object.keys(colours).sort(function (a,b) { return parseInt(a) > parseInt(b) ? 1 : -1; }).forEach(function (pct_key) {
                        var this_pct = parseFloat(pct_key);
                        if(!isNaN(this_pct) && this_pct >= 0.0) {
                            var legend_step = {id: "legend_" + i.toString(), label: this_pct.toString().padStart(3, " ") + "%", shape: "box", borderWidth: 0, x: x_pos, y: y_pos, font: {face: 'courier new', size: font_size, color: "black"}, color: {background: colours[pct_key]}, scaling: {min: 10, max: 30, label: {enabled: false, drawThreshold: 0, maxVisible: 100000}}};
                            nodes.add(legend_step);
                            y_pos += y_inc;
                            i++;
                        }
                    });
                } else {
                    var pct_step;
                    if (Boolean(hide_overspeed)) {
                        pct_step = 100.0 / (num_steps - 1);
                    } else {
                        pct_step = 150.0 / (num_steps - 1);
                    }
                    for (var i = 0; i < num_steps; i++) {
                        var this_pct = Math.round(pct_step * i);
                        var legend_step = {id: "legend_" + i.toString(), label: this_pct.toString().padStart(3, " ") + "%", shape: "box", borderWidth: 0, x: x_pos, y: y_pos, font: {face: 'courier new', size: font_size, color: "black"}, color: {background: custommap.legendPctDefaultColour(this_pct)}, scaling: {min: 10, max: 30, label: {enabled: false, drawThreshold: 0, maxVisible: 100000}}};
                        nodes.add(legend_step);
                        y_pos += y_inc;
                    }
                }
                nodes.flush();
            }
        },

        createNetwork: function (elementId, scale, nodes, edges, options, bgtype, bgdata, logicalWidth, logicalHeight) {
            nodes.flush();
            edges.flush();

            options = options || {};
            options.physics = false;
            options.nodes = options.nodes || {};
            options.nodes.scaling = {
                min: 10,
                max: 30,
                label: {
                    enabled: false,
                    drawThreshold: 0,
                    maxVisible: 100000
                }
            };
            options.edges = options.edges || {};
            options.edges.scaling = {
                min: 1,
                max: 15,
                label: {
                    enabled: false,
                    drawThreshold: 0,
                    maxVisible: 100000
                }
            };
            options.interaction = options.interaction || {};
            options.interaction.hover = true;
            options.interaction.tooltipDelay = 100;
            options.interaction.dragView = true;
            options.interaction.zoomView = true;

            var container = document.getElementById(elementId);
            var network = new vis.Network(container, {nodes: nodes, edges: edges}, options);

            var network_height = $($(container).children(".vis-network")[0]).height() || $(container).height();
            var network_width = $($(container).children(".vis-network")[0]).width() || $(container).width();
            var mapWidth = logicalWidth || Math.round(network_width / scale);
            var mapHeight = logicalHeight || Math.round(network_height / scale);
            container._mapWidth = mapWidth;
            container._mapHeight = mapHeight;
            container._visNetwork = network;
            container._minScale = scale;
            container._maxScale = 12.0;

            var centreY = Math.round(mapHeight / 2);
            var centreX = Math.round(mapWidth / 2);
            network.moveTo({position: {x: centreX, y: centreY}, scale: scale});

            var lastValidPos = { x: centreX, y: centreY };
            var lastValidScale = scale;

            network.on('beforeDrawing', function (ctx) {
                var s = network.getScale();
                var minScale = container._minScale || 0.1;
                var maxScale = container._maxScale || 12.0;
                if (s <= maxScale + 0.001 && s >= minScale - 0.001) {
                    lastValidPos = network.getViewPosition();
                    lastValidScale = s;
                }

                if (network.body && network.body.nodes) {
                    for (var id in network.body.nodes) {
                        var n = network.body.nodes[id];
                        var nodeData = nodes.get(id);
                        if (!nodeData) continue;

                        var shape = nodeData.shape || nodeData.style || (n.options && n.options.shape);

                        // Draw background container for icon nodes if configured
                        if (shape === 'icon') {
                            var bgCol = (nodeData.color && nodeData.color.background) || nodeData.colour_bg_view;
                            var bdrCol = (nodeData.color && nodeData.color.border) || nodeData.colour_bdr_view;
                            var bdrWidth = typeof nodeData.borderWidth === 'number' ? nodeData.borderWidth : 0;

                            if (bgCol || (bdrCol && bdrWidth > 0)) {
                                var iconSize = (nodeData.size || 25) * 1.18;
                                ctx.save();
                                ctx.beginPath();
                                ctx.arc(n.x, n.y, iconSize, 0, 2 * Math.PI, false);
                                if (bgCol) {
                                    ctx.fillStyle = bgCol;
                                    ctx.fill();
                                }
                                if (bdrWidth > 0 && bdrCol) {
                                    ctx.lineWidth = bdrWidth;
                                    ctx.strokeStyle = bdrCol;
                                    ctx.stroke();
                                }
                                ctx.restore();
                            }
                        }
                    }
                }
            });

            network.on('zoom', function (properties) {
                if (network._isClamping) return;
                var minScale = container._minScale || 0.1;
                var maxScale = container._maxScale || 12.0;

                if (properties.scale > maxScale) {
                    network._isClamping = true;
                    network.moveTo({
                        position: lastValidPos,
                        scale: maxScale,
                        animation: false
                    });
                    network._isClamping = false;
                } else if (properties.scale < minScale) {
                    network._isClamping = true;
                    network.moveTo({
                        position: lastValidPos,
                        scale: minScale,
                        animation: false
                    });
                    network._isClamping = false;
                } else {
                    lastValidPos = network.getViewPosition();
                    lastValidScale = properties.scale;
                }
            });

            network.on('dragEnd', function () {
                var viewPos = network.getViewPosition();
                var curScale = network.getScale();
                var minX = -mapWidth * 0.15;
                var maxX = mapWidth * 1.15;
                var minY = -mapHeight * 0.15;
                var maxY = mapHeight * 1.15;

                var clampedX = Math.max(minX, Math.min(viewPos.x, maxX));
                var clampedY = Math.max(minY, Math.min(viewPos.y, maxY));

                if (clampedX !== viewPos.x || clampedY !== viewPos.y) {
                    network.moveTo({
                        position: { x: clampedX, y: clampedY },
                        scale: curScale,
                        animation: { duration: 250, easingFunction: 'easeOutQuad' }
                    });
                }
            });

            setCustomMapBackground(elementId, bgtype, bgdata, network);

            return network;
        },

        zoomIn: function (network, container) {
            if (!network) return;
            var curScale = network.getScale();
            var maxScale = (container && container._maxScale) ? container._maxScale : 12.0;
            var targetScale = Math.min(curScale * 1.25, maxScale);
            network.moveTo({
                scale: targetScale,
                animation: { duration: 200, easingFunction: 'easeInOutQuad' }
            });
        },

        zoomOut: function (network, container) {
            if (!network) return;
            var curScale = network.getScale();
            var minScale = (container && container._minScale) ? container._minScale : 0.1;
            var targetScale = Math.max(curScale / 1.25, minScale);
            network.moveTo({
                scale: targetScale,
                animation: { duration: 200, easingFunction: 'easeInOutQuad' }
            });
        },

        fitMap: function (network, container) {
            if (!network || !container) return;
            var mapWidth = container._mapWidth;
            var mapHeight = container._mapHeight;
            var scale = container._minScale || 1;
            var centreX = Math.round(mapWidth / 2);
            var centreY = Math.round(mapHeight / 2);
            network.moveTo({
                position: {x: centreX, y: centreY},
                scale: scale,
                animation: { duration: 300, easingFunction: 'easeInOutQuad' }
            });
        },

        getNodeCfg: function (nodeid, node, screenshot, custom_image_base) {
            let nodeimage_base = '{{ route('maps.nodeimage.show', ['image' => '?' ]) }}'.replace("?", "");
            var node_cfg = {};
            node_cfg.id = nodeid;

            if(node.linked_map_name) {
                node_cfg.title = "{{ trans('map.custom.view.go_to') }} " + node.linked_map_name;
            } else {
                node_cfg.title = null;
            }
            node_cfg.device_id = node.device_id;
            node_cfg.linked_map_id = node.linked_map_id;
            node_cfg.label = screenshot ? node.label.replace(/./g, ' ') : node.label;
            node_cfg.shape = node.style;
            node_cfg.borderWidth = node.border_width;
            node_cfg.x = node.x_pos;
            node_cfg.y = node.y_pos;
            node_cfg.font = {
                face: node.text_face || 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
                size: node.text_size || 13,
                color: node.text_colour || undefined
            };
            node_cfg.shapeProperties = { borderRadius: 6 };
            node_cfg.size = node.size;
            node_cfg.color = {background: node.colour_bg_view, border: node.colour_bdr_view};
            node_cfg.scaling = {
                min: 10,
                max: 30,
                label: {
                    enabled: false,
                    drawThreshold: 0,
                    maxVisible: 100000
                }
            };
            if(node.style == "icon") {
                node_cfg.icon = {
                    face: 'FontAwesome',
                    code: String.fromCharCode(parseInt(node.icon, 16)),
                    size: node.size || 25,
                    color: node.colour_bdr || node.colour_bdr_view || '#2b7ce9'
                };
            } else {
                node_cfg.icon = {};
            }
            if(node.style == "image" || node.style == "circularImage") {
                if(node.image) {
                    var img = String(node.image);
                    node_cfg.image = {unselected: (img.startsWith('http') || img.startsWith('/') ? img : custom_image_base + img)};
                } else if(node.nodeimage) {
                    node_cfg.image = {unselected: nodeimage_base + node.nodeimage};
                } else if (node.device_image) {
                    var devImg = String(node.device_image);
                    if (!devImg.startsWith('http') && !devImg.startsWith('/')) {
                        devImg = (custommap.baseUrl || '{{ \App\Facades\LibrenmsConfig::get('base_url') }}') + devImg;
                    }
                    node_cfg.image = {unselected: devImg};
                } else {
                    node.style = 'box';
                    node_cfg.shape = 'box';
                    node_cfg.image = undefined;
                }
            } else {
                node_cfg.image = undefined;
            }
            node_cfg.physics = false;
            return node_cfg;
        },

        getEdgeCfg: function (edgeid, edge, fromto, reverse_arrows) {
            var arrows;
            if (Boolean(reverse_arrows)) {
                arrows = {from: {enabled: true, scaleFactor: 0.6}, to: {enabled: false}};
            } else {
                arrows = {to: {enabled: true, scaleFactor: 0.6}, from: {enabled: false}};
            }

            var edge_cfg = {
                id: edgeid + "_" + fromto,
                to: edgeid + "_mid",
                arrows: arrows,
                font: {
                    face: edge.text_face || 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
                    size: edge.text_size || 12,
                    color: edge.text_colour || undefined,
                    align: edge.text_align || "horizontal"
                },
                scaling: {
                    min: 1,
                    max: 15,
                    label: {
                        enabled: false,
                        drawThreshold: 0,
                        maxVisible: 100000
                    }
                },
                smooth: {type: edge.style},
                arrowStrikethrough: false,
                physics: false
            };
            if (fromto == "from") {
                edge_cfg.from = edge.custom_map_node1_id;
                var port_pct = Boolean(reverse_arrows) ? edge.port_topct : edge.port_frompct;
                var port_bps = Boolean(reverse_arrows) ? edge.port_tobps : edge.port_frombps;
                var port_colour = Boolean(reverse_arrows) ? edge.colour_to : edge.colour_from;
                var port_width = Boolean(reverse_arrows) ? edge.width_to : edge.width_from;
            } else if (fromto == "to") {
                edge_cfg.from = edge.custom_map_node2_id;
                var port_pct = Boolean(reverse_arrows) ? edge.port_frompct : edge.port_topct;
                var port_bps = Boolean(reverse_arrows) ? edge.port_frombps : edge.port_tobps;
                var port_colour = Boolean(reverse_arrows) ? edge.colour_from : edge.colour_to;
                var port_width = Boolean(reverse_arrows) ? edge.width_from : edge.width_to;

                if(edge_cfg.smooth.type == "curvedCW") {
                    edge_cfg.smooth.type = "curvedCCW";
                } else if (edge_cfg.smooth.type == "curvedCCW") {
                    edge_cfg.smooth.type = "curvedCW";
                }
            } else {
                console.log("custommapGetEdgeCfg got an invalid value in fromto:" + fromto);
                return {};
            }
            if(edge.port_id) {
                edge_cfg.title = document.createElement("div");
                edge_cfg.title.innerHTML = edge.port_info;
                if(edge.showpct) {
                    edge_cfg.label = port_pct + "%";
                }
                if(edge.showbps) {
                    if(edge_cfg.label == null) {
                        edge_cfg.label = '';
                    } else {
                        edge_cfg.label += "\n";
                    }
                    edge_cfg.label += port_bps;
                }
                edge_cfg.color = {color: port_colour};
                edge_cfg.width = parseFloat(edge.fixed_width) || port_width;
            }
            return edge_cfg;
        },

        getEdgeMidCfg: function (edgeid, edge, screenshot) {
            var mid_x = edge.mid_x;
            var mid_y = edge.mid_y;

            return {
                id: edgeid + "_mid",
                shape: "dot",
                size: 0,
                x: mid_x,
                y: mid_y,
                label: screenshot ? '' : edge.label,
                font: {
                    face: edge.text_face || 'sans-serif',
                    size: edge.text_size || 12,
                    color: edge.text_colour || undefined
                },
                scaling: {
                    min: 10,
                    max: 30,
                    label: {
                        enabled: false,
                        drawThreshold: 0,
                        maxVisible: 100000
                    }
                },
                physics: false
            };
        },

        ensureSharedUI: function () {
            var $popup = $('#custom-map-hover-popup');
            if ($popup.length && !$popup.data('listeners-bound')) {
                $popup.data('listeners-bound', true)
                    .on('mouseenter', function () {
                        clearTimeout(custommap._hideTimeout);
                    }).on('mouseleave', function () {
                        custommap.hidePopup(200);
                    });
            }

            if (!$(document).data('custommap-context-bound')) {
                $(document).data('custommap-context-bound', true)
                    .on('click', function (e) {
                        if (!$(e.target).closest('#custom-map-context-menu').length) {
                            custommap.closeContextMenu();
                        }
                    });
            }
        },

        positionPopup: function (targetX, targetY) {
            var $popup = $('#custom-map-hover-popup');
            var popupWidth = $popup.outerWidth();
            var popupHeight = $popup.outerHeight();
            var winWidth = $(window).width();
            var winHeight = $(window).height();

            var left = Math.max(12, Math.min(targetX - (popupWidth / 2), winWidth - popupWidth - 12));
            var clearance = 30;
            var top = targetY + clearance;

            if (top + popupHeight > winHeight - 12 && (targetY - clearance - 70 > winHeight - (targetY + clearance))) {
                top = Math.max(70, targetY - clearance - popupHeight);
            }

            $popup.css({
                left: left + 'px',
                top: top + 'px'
            });
        },

        showDevicePopup: function (deviceId, domX, domY) {
            clearTimeout(custommap._hideTimeout);
            clearTimeout(custommap._hoverTimeout);

            custommap._hoverTimeout = setTimeout(function () {
                var $popup = $('#custom-map-hover-popup');
                var $content = $('#custom-map-hover-popup-content');

                var renderHtml = function (html) {
                    $content.html(html);
                    if (window.Countdown && window.Countdown.refreshNum) {
                        $content.find('.graph-image').each(function () {
                            var src = $(this).attr('src');
                            if (src && src.includes('&refreshnum=')) {
                                $(this).attr('src', src.replace(/&refreshnum=\d+/, '&refreshnum=' + window.Countdown.refreshNum));
                            }
                        });
                    }
                    $popup.show().removeClass('tw:hidden');
                    custommap.positionPopup(domX, domY);
                };

                if (custommap.popupCache['device_' + deviceId]) {
                    renderHtml(custommap.popupCache['device_' + deviceId]);
                } else {
                    $content.html('<div class="tw:p-4 tw:flex tw:items-center tw:gap-2"><i class="fa-solid fa-circle-notch fa-spin"></i> {{ __('Loading device details...') }}</div>');
                    $popup.show().removeClass('tw:hidden');
                    custommap.positionPopup(domX, domY);

                    var url = (custommap.baseUrl || '') + 'device/' + deviceId + '/popup?type=device_bits&from[]=-1d&from[]=-7d';
                    $.get(url, function (html) {
                        custommap.popupCache['device_' + deviceId] = html;
                        renderHtml(html);
                    }).fail(function () {
                        $content.html('<div class="tw:p-3 tw:text-red-500">{{ __('Failed to load device details.') }}</div>');
                    });
                }
            }, 150);
        },

        showPortPopup: function (portId, domX, domY) {
            clearTimeout(custommap._hideTimeout);
            clearTimeout(custommap._hoverTimeout);

            custommap._hoverTimeout = setTimeout(function () {
                var $popup = $('#custom-map-hover-popup');
                var $content = $('#custom-map-hover-popup-content');

                var renderHtml = function (html) {
                    $content.html(html);
                    if (window.Countdown && window.Countdown.refreshNum) {
                        $content.find('.graph-image').each(function () {
                            var src = $(this).attr('src');
                            if (src && src.includes('&refreshnum=')) {
                                $(this).attr('src', src.replace(/&refreshnum=\d+/, '&refreshnum=' + window.Countdown.refreshNum));
                            }
                        });
                    }
                    $popup.show().removeClass('tw:hidden');
                    custommap.positionPopup(domX, domY);
                };

                if (custommap.popupCache['port_' + portId]) {
                    renderHtml(custommap.popupCache['port_' + portId]);
                } else {
                    $content.html('<div class="tw:p-4 tw:flex tw:items-center tw:gap-2"><i class="fa-solid fa-circle-notch fa-spin"></i> {{ __('Loading port details...') }}</div>');
                    $popup.show().removeClass('tw:hidden');
                    custommap.positionPopup(domX, domY);

                    var url = (custommap.baseUrl || '') + 'port/' + portId + '/popup?from=-1d';
                    $.get(url, function (html) {
                        custommap.popupCache['port_' + portId] = html;
                        renderHtml(html);
                    }).fail(function () {
                        $content.html('<div class="tw:p-3 tw:text-red-500">{{ __('Failed to load port details.') }}</div>');
                    });
                }
            }, 150);
        },

        hidePopup: function (delay) {
            clearTimeout(custommap._hoverTimeout);
            clearTimeout(custommap._hideTimeout);
            if (delay === 0) {
                $('#custom-map-hover-popup').hide().addClass('tw:hidden');
            } else {
                custommap._hideTimeout = setTimeout(function () {
                    $('#custom-map-hover-popup').hide().addClass('tw:hidden');
                }, delay || 200);
            }
        },

        showContextMenu: function (header, items, x, y) {
            var $menu = $('#custom-map-context-menu');
            var $header = $('#context-menu-header');
            var $items = $('#context-menu-items');

            if (header) {
                $header.text(header).show();
            } else {
                $header.hide();
            }

            $items.empty();
            items.forEach(function (item) {
                if (item.divider) {
                    $items.append('<div class="tw:border-t tw:border-gray-100 tw:dark:border-dark-gray-300 tw:my-1"></div>');
                    return;
                }
                var $btn = $('<button type="button" class="tw:w-full tw:text-left tw:px-3 tw:py-1.5 tw:hover:bg-blue-50 tw:dark:hover:bg-dark-gray-300 tw:flex tw:items-center tw:gap-2 tw:text-slate-700 tw:dark:text-dark-white-200 tw:transition-colors"></button>');
                if (item.icon) {
                    $btn.append($('<i class="' + item.icon + ' tw:w-4 tw:text-center tw:text-slate-400 tw:dark:text-dark-white-400"></i>'));
                }
                $btn.append($('<span></span>').text(item.label));
                $btn.on('click', function () {
                    custommap.closeContextMenu();
                    if (item.action) item.action();
                });
                $items.append($btn);
            });

            $menu.show().removeClass('tw:hidden');

            var menuW = $menu.outerWidth() || 200;
            var menuH = $menu.outerHeight() || 200;
            var winW = $(window).width();
            var winH = $(window).height();

            if (x + menuW > winW - 10) x = winW - menuW - 10;
            if (y + menuH > winH - 10) y = winH - menuH - 10;

            $menu.css({ left: x + 'px', top: y + 'px' });
        },

        closeContextMenu: function () {
            $('#custom-map-context-menu').hide().addClass('tw:hidden');
        },

        Viewer: class {
            constructor(config) {
                this.elementId = config.elementId || 'custom-map';
                this.containerId = config.containerId || 'map-container';
                this.mapId = config.mapId;
                this.dataUrl = config.dataUrl;
                this.editUrl = config.editUrl;
                this.showUrlTemplate = config.showUrlTemplate;
                this.bgType = config.bgType;
                this.bgData = config.bgData;
                this.reverseArrows = Boolean(config.reverseArrows);
                this.screenshot = Boolean(config.screenshot);
                this.legend = config.legend || {};
                this.networkOptions = config.networkOptions || {};
                this.mapLogicalWidth = parseInt(this.networkOptions.width) || parseInt(config.width) || 1800;
                this.mapLogicalHeight = parseInt(this.networkOptions.height) || parseInt(config.height) || 800;
                this.baseUrl = config.baseUrl || '';
                this.customImageBase = this.baseUrl + 'images/custommap/icons/';
                this.enableKeyboard = Boolean(config.enableKeyboard);
                this.autoRefresh = config.autoRefresh || 0;
                this.alertId = config.alertId;
                this.alertRowId = config.alertRowId;
                this.isWidget = Boolean(config.isWidget);

                this.network = null;
                this.networkNodes = new vis.DataSet({ queue: { delay: 100 } });
                this.networkEdges = new vis.DataSet({ queue: { delay: 100 } });
                this.edgePortMap = {};
                this.resizeObserver = null;
                this.refreshInterval = null;
                this.isDestroyed = false;

                custommap.baseUrl = this.baseUrl;
                this.init();
            }

            init() {
                custommap.ensureSharedUI();
                this.bindEvents();
                this.refresh();
            }

            calculateScale() {
                var $container = $('#' + this.containerId);
                var containerWidth = $container.width() || $(window).width();
                var containerHeight = $container.height() || $(window).height();
                var logicalWidth = this.mapLogicalWidth || 1800;
                var logicalHeight = this.mapLogicalHeight || 800;
                return Math.min(containerWidth / logicalWidth, containerHeight / logicalHeight) || 1;
            }

            refresh() {
                var self = this;
                $.get(this.dataUrl)
                    .done(function (data) {
                        if (self.isDestroyed) return;

                        // Add/update nodes
                        if (data.nodes) {
                            $.each(data.nodes, function (nodeid, node) {
                                var node_cfg = custommap.getNodeCfg(nodeid, node, self.screenshot, self.customImageBase);
                                if (self.networkNodes.get(nodeid)) {
                                    self.networkNodes.update(node_cfg);
                                } else {
                                    self.networkNodes.add([node_cfg]);
                                }
                            });
                        }

                        // Add/update edges
                        if (data.edges) {
                            $.each(data.edges, function (edgeid, edge) {
                                var mid = custommap.getEdgeMidCfg(edgeid, edge, self.screenshot);
                                var edge1 = custommap.getEdgeCfg(edgeid, edge, "from", self.reverseArrows);
                                var edge2 = custommap.getEdgeCfg(edgeid, edge, "to", self.reverseArrows);
                                if (edge.port_id) {
                                    self.edgePortMap[edgeid] = { device_id: edge.device_id, port_id: edge.port_id };
                                } else {
                                    delete self.edgePortMap[edgeid];
                                }
                                if (self.networkNodes.get(mid.id)) {
                                    self.networkNodes.update(mid);
                                    self.networkEdges.update(edge1);
                                    self.networkEdges.update(edge2);
                                } else {
                                    self.networkNodes.add([mid]);
                                    self.networkEdges.add([edge1, edge2]);
                                }
                            });
                        }

                        // Remove obsolete nodes/edges
                        $.each(self.networkNodes.getIds(), function (node_idx, nodeid) {
                            if (nodeid.endsWith('_mid')) {
                                var edgeid = nodeid.split("_")[0];
                                if (!data.edges || !(edgeid in data.edges)) {
                                    self.networkNodes.remove(edgeid + "_mid");
                                    self.networkEdges.remove(edgeid + "_to");
                                    self.networkEdges.remove(edgeid + "_from");
                                    delete self.edgePortMap[edgeid];
                                }
                            } else if (!nodeid.startsWith('legend_')) {
                                if (!data.nodes || !(nodeid in data.nodes)) {
                                    self.networkNodes.remove(nodeid);
                                }
                            }
                        });

                        // Redraw legend
                        if (self.legend && self.legend.steps !== undefined) {
                            custommap.redrawDefaultLegend(
                                self.networkNodes,
                                self.legend.steps,
                                self.legend.x,
                                self.legend.y,
                                self.legend.font_size,
                                self.legend.hide_invalid,
                                self.legend.hide_overspeed,
                                self.legend.colours
                            );
                        }

                        self.networkNodes.flush();
                        self.networkEdges.flush();

                        if (self.alertId) {
                            if (!data.nodes || Object.keys(data.nodes).length === 0) {
                                $('#' + self.alertId).text("{{ __('map.custom.view.no_devices') }}");
                                if (self.alertRowId) $('#' + self.alertRowId).show();
                            } else {
                                $('#' + self.alertId).text("");
                                if (self.alertRowId) $('#' + self.alertRowId).hide();
                            }
                        }

                        if (!self.network) {
                            self.createNetwork();
                        }
                    });
            }

            createNetwork() {
                var scale = this.calculateScale();
                var netOptions = $.extend(true, {}, this.networkOptions);
                netOptions.width = '100%';
                netOptions.height = '100%';

                this.network = custommap.createNetwork(
                    this.elementId,
                    scale,
                    this.networkNodes,
                    this.networkEdges,
                    netOptions,
                    this.bgType,
                    this.bgData,
                    this.mapLogicalWidth,
                    this.mapLogicalHeight
                );

                var self = this;
                var container = document.getElementById(this.elementId);

                this.network.on('hoverNode', function (params) {
                    var node = self.networkNodes.get(params.node);
                    if (node && node.device_id) {
                        var domPos = self.network.canvasToDOM({ x: node.x, y: node.y });
                        var canvasEl = $('#' + self.elementId + ' canvas')[0];
                        var canvasRect = canvasEl ? canvasEl.getBoundingClientRect() : { left: 0, top: 0 };
                        custommap.showDevicePopup(node.device_id, canvasRect.left + domPos.x, canvasRect.top + domPos.y);
                    }
                });

                this.network.on('blurNode', function () {
                    custommap.hidePopup(200);
                });

                this.network.on('hoverEdge', function (params) {
                    var edgeId = String(params.edge).split('_')[0];
                    if (edgeId && self.edgePortMap[edgeId]) {
                        var portData = self.edgePortMap[edgeId];
                        var midNode = self.networkNodes.get(edgeId + '_mid');
                        var canvasEl = $('#' + self.elementId + ' canvas')[0];
                        var canvasRect = canvasEl ? canvasEl.getBoundingClientRect() : { left: 0, top: 0 };
                        var domPos = midNode ? self.network.canvasToDOM({ x: midNode.x, y: midNode.y }) : { x: $('#' + self.elementId).width() / 2, y: $('#' + self.elementId).height() / 2 };
                        custommap.showPortPopup(portData.port_id, canvasRect.left + domPos.x, canvasRect.top + domPos.y);
                    }
                });

                this.network.on('blurEdge', function () {
                    custommap.hidePopup(200);
                });

                this.network.on('doubleClick', function (properties) {
                    if (properties.nodes.length === 0 && properties.edges.length === 0) {
                        custommap.fitMap(self.network, container);
                        return;
                    }

                    var edge_id = null;
                    if (properties.nodes.length > 0) {
                        var node_id = properties.nodes[0];
                        var node = self.networkNodes.get(node_id);
                        if (node.linked_map_id) {
                            var showUrl = self.showUrlTemplate ? self.showUrlTemplate.replace('?', node.linked_map_id) : (self.baseUrl + 'maps/custom/' + node.linked_map_id);
                            window.location.href = showUrl;
                            return;
                        } else if (node.device_id) {
                            window.location.href = (self.baseUrl || '') + "device/" + node.device_id;
                            return;
                        } else if (node_id.endsWith('_mid')) {
                            edge_id = node_id.split("_")[0];
                        }
                    } else if (properties.edges.length > 0) {
                        edge_id = properties.edges[0].split("_")[0];
                    }

                    if (edge_id && (edge_id in self.edgePortMap)) {
                        window.location.href = (self.baseUrl || '') + 'device/device=' + self.edgePortMap[edge_id].device_id + '/tab=port/port=' + self.edgePortMap[edge_id].port_id + '/';
                    }
                });

                this.network.on('oncontext', function (params) {
                    if (params.event.shiftKey) return;
                    params.event.preventDefault();
                    custommap.hidePopup(0);

                    var domX = params.event.clientX;
                    var domY = params.event.clientY;
                    var nodeId = self.network.getNodeAt(params.pointer.DOM);
                    var edgeId = self.network.getEdgeAt(params.pointer.DOM);

                    var menuItems = [];
                    var menuHeader = '';

                    if (nodeId) {
                        var node = self.networkNodes.get(nodeId);
                        if (node && node.device_id) {
                            menuHeader = node.label || "{{ __('Device') }}";
                            menuItems.push({
                                icon: 'fa-solid fa-server',
                                label: "{{ __('View Device') }}",
                                action: function () { window.location.href = (self.baseUrl || '') + 'device/' + node.device_id; }
                            });
                            menuItems.push({
                                icon: 'fa-solid fa-network-wired',
                                label: "{{ __('View Ports') }}",
                                action: function () { window.location.href = (self.baseUrl || '') + 'device/' + node.device_id + '/tab=ports/'; }
                            });
                            menuItems.push({
                                icon: 'fa-solid fa-chart-area',
                                label: "{{ __('View Graphs') }}",
                                action: function () { window.location.href = (self.baseUrl || '') + 'device/' + node.device_id + '/tab=graphs/'; }
                            });
                            if (node.label) {
                                menuItems.push({
                                    icon: 'fa-solid fa-copy',
                                    label: "{{ __('Copy Name') }}",
                                    action: function () {
                                        navigator.clipboard.writeText(node.label);
                                        if (window.toastr) toastr.success("{{ __('Copied to clipboard') }}");
                                    }
                                });
                            }
                        } else if (node && node.linked_map_id) {
                            menuHeader = node.label || "{{ __('Map') }}";
                            menuItems.push({
                                icon: 'fa-solid fa-map',
                                label: "{{ __('Open Map') }}",
                                action: function () {
                                    var showUrl = self.showUrlTemplate ? self.showUrlTemplate.replace('?', node.linked_map_id) : ((self.baseUrl || '') + 'maps/custom/' + node.linked_map_id);
                                    window.location.href = showUrl;
                                }
                            });
                        }
                    } else if (edgeId) {
                        var baseEdgeId = String(edgeId).split('_')[0];
                        if (baseEdgeId && self.edgePortMap[baseEdgeId]) {
                            var portData = self.edgePortMap[baseEdgeId];
                            menuHeader = "{{ __('Port Interface') }}";
                            menuItems.push({
                                icon: 'fa-solid fa-network-wired',
                                label: "{{ __('View Port') }}",
                                action: function () { window.location.href = (self.baseUrl || '') + 'device/device=' + portData.device_id + '/tab=port/port=' + portData.port_id + '/'; }
                            });
                        }
                    }

                    if (menuItems.length > 0) {
                        menuItems.push({ divider: true });
                    } else {
                        menuHeader = "{{ __('Map Navigation') }}";
                    }

                    menuItems.push({
                        icon: 'fa-solid fa-expand',
                        label: "{{ __('Fit to Window') }} (F)",
                        action: function () { custommap.fitMap(self.network, container); }
                    });
                    menuItems.push({
                        icon: 'fa-solid fa-magnifying-glass-plus',
                        label: "{{ __('Zoom In') }} (+)",
                        action: function () { custommap.zoomIn(self.network, container); }
                    });
                    menuItems.push({
                        icon: 'fa-solid fa-magnifying-glass-minus',
                        label: "{{ __('Zoom Out') }} (-)",
                        action: function () { custommap.zoomOut(self.network, container); }
                    });
                    if (self.editUrl) {
                        menuItems.push({
                            icon: 'fa-solid fa-pen-to-square',
                            label: "{{ __('Edit Map') }}",
                            action: function () { window.location.href = self.editUrl; }
                        });
                    }

                    custommap.showContextMenu(menuHeader, menuItems, domX, domY);
                });
            }

            bindEvents() {
                var self = this;
                var containerEl = document.getElementById(this.containerId) || document.getElementById(this.elementId);

                if (window.ResizeObserver && containerEl) {
                    this.resizeObserver = new ResizeObserver(function () {
                        if (self.isDestroyed || !self.network) return;
                        var currentScale = self.calculateScale();
                        var networkContainer = document.getElementById(self.elementId);
                        if (networkContainer) {
                            networkContainer._minScale = currentScale;
                        }
                        if (self.network.getScale() < currentScale) {
                            self.network.moveTo({ scale: currentScale });
                        }
                        self.network.redraw();
                    });
                    this.resizeObserver.observe(containerEl);
                }

                $(window).on('resize.custommap_' + this.elementId, function () {
                    if (self.isDestroyed || !self.network) return;
                    var currentScale = self.calculateScale();
                    var networkContainer = document.getElementById(self.elementId);
                    if (networkContainer) {
                        networkContainer._minScale = currentScale;
                    }
                    self.network.redraw();
                });

                if (this.enableKeyboard) {
                    $(document).on('keydown.custommap_' + this.elementId, function (e) {
                        if (self.isDestroyed || !self.network) return;
                        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
                            return;
                        }
                        var networkContainer = document.getElementById(self.elementId);
                        if (e.key === '0' || e.key.toLowerCase() === 'f' || e.key === 'Home') {
                            e.preventDefault();
                            custommap.fitMap(self.network, networkContainer);
                        } else if (e.key === '+' || e.key === '=') {
                            e.preventDefault();
                            custommap.zoomIn(self.network, networkContainer);
                        } else if (e.key === '-' || e.key === '_') {
                            e.preventDefault();
                            custommap.zoomOut(self.network, networkContainer);
                        } else if (e.key === 'ArrowUp' || e.key === 'ArrowDown' || e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                            e.preventDefault();
                            var curPos = self.network.getViewPosition();
                            var curScale = self.network.getScale();
                            var step = 100 / curScale;
                            var dx = 0, dy = 0;
                            if (e.key === 'ArrowUp') dy = -step;
                            if (e.key === 'ArrowDown') dy = step;
                            if (e.key === 'ArrowLeft') dx = -step;
                            if (e.key === 'ArrowRight') dx = step;
                            self.network.moveTo({
                                position: { x: curPos.x + dx, y: curPos.y + dy },
                                scale: curScale,
                                animation: { duration: 100, easingFunction: 'linear' }
                            });
                        } else if (e.key === 'Escape') {
                            custommap.closeContextMenu();
                            custommap.hidePopup(0);
                        }
                    });
                }

                if (this.autoRefresh > 0) {
                    this.refreshInterval = setInterval(function () {
                        self.refresh();
                    }, this.autoRefresh * 1000);
                }

                var $cont = $('#' + this.containerId);
                $cont.on('refresh', function () {
                    self.refresh();
                });
                $cont.on('destroy', function () {
                    self.destroy();
                });
            }

            destroy() {
                this.isDestroyed = true;
                if (this.refreshInterval) {
                    clearInterval(this.refreshInterval);
                    this.refreshInterval = null;
                }
                if (this.resizeObserver) {
                    this.resizeObserver.disconnect();
                    this.resizeObserver = null;
                }
                $(window).off('resize.custommap_' + this.elementId);
                $(document).off('keydown.custommap_' + this.elementId);
                $('#' + this.containerId).off('refresh destroy');
                if (this.network) {
                    this.network.destroy();
                    this.network = null;
                }
                this.networkNodes.clear();
                this.networkEdges.clear();
                this.edgePortMap = {};
            }
        },

        initViewer: function (config) {
            return new custommap.Viewer(config);
        }
    };
</script>
