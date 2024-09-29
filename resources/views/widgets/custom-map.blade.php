<style>
    #map-container-{{ $id }} {
        display: grid;
        grid-template: 1fr / 1fr;
        place-items: center;
    }
    #custom-map-{{ $id }} {
        grid-column: 1 / 1;
        grid-row: 1 / 1;
        z-index: 2;
    }
    #custom-map-bg-geo-map-{{ $id }} {
        grid-column: 1 / 1;
        grid-row: 1 / 1;
        z-index: 1;
    }
</style>

<div id="map-container-{{ $id }}" style="width: 100%; height: 100%">
  <div id="custom-map-{{ $id }}" style="width: 99%; height: 99%"></div>
  <x-geo-map id="custom-map-bg-geo-map-{{ $id }}"
    :init="$map->background_type == 'map'"
    :config="$background_config"
    width="99%"
    height="99%"
    readonly
  />
</div>

<script type="application/javascript">
    (function () {
        function legendPctColour(pct) {
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
        }

        loadjs('js/vis.min.js', function () {
            var bgtype = {{ Js::from($map->background_type) }};
            var bgdata = {{ Js::from($background_config) }};
            var custom_image_base = "{{ $base_url }}images/custommap/icons/";
            var reverse_arrows = {{$map->reverse_arrows}};
            var legend = @json($map->legend);
            var network_height = Math.floor($("#custom-map-{{ $id }}").height());
            var network_width = Math.floor($("#custom-map-{{ $id }}").width());
            var network_nodes = new vis.DataSet({queue: {delay: 100}});
            var network_edges = new vis.DataSet({queue: {delay: 100}});
            var node_device_map = {};
            var edge_port_map = {};
            var node_link_map = {};

            var network_options = {{ Js::from($map_conf) }};

            var scale = {{ $scale }}

            $.get( '{{ route('maps.custom.data', ['map' => $map->custom_map_id]) }}')
                .done(function( data ) {
                    // Add/update nodes
                    $.each( data.nodes, function( nodeid, node) {
                        var node_cfg = {};
                        node_cfg.id = nodeid;
                        if(node.device_id) {
                            node_device_map[nodeid] = {device_id: node.device_id, device_name: node.device_name};
                            node_cfg.title = node.device_info;
                        } else if(node.linked_map_name) {
                            node_link_map[nodeid] = node.linked_map_id;
                            node_cfg.title = "Go to " + node.linked_map_name;
                        } else {
                            node_cfg.title = null;
                        }
                        node_cfg.label = node.label;
                        node_cfg.shape = node.style;
                        node_cfg.borderWidth = node.border_width;

                        // Scale values to fit into widget area
                        node_cfg.x = Math.floor(scale * node.x_pos);
                        node_cfg.y = Math.floor(scale * node.y_pos);
                        node_cfg.font = {face: node.text_face, size:  Math.floor(scale * node.text_size), color: node.text_colour};
                        node_cfg.size =  Math.floor(scale * node.size);

                        node_cfg.color = {background: node.colour_bg_view, border: node.colour_bdr_view};
                        if(node.style == "icon") {
                            node_cfg.icon = {face: 'FontAwesome', code: String.fromCharCode(parseInt(node.icon, 16)), size: node.size, color: node.colour_bdr};
                        } else {
                            node_cfg.icon = {};
                        }
                        if(node.style == "image" || node.style == "circularImage") {
                            if(node.image) {
                                node_cfg.image = {unselected: custom_image_base + node.image};
                            } else if (node.device_image) {
                                node_cfg.image = {unselected: node.device_image};
                            } else {
                                // If we do not get a valid image from the database, use defaults
                                node_cfg.shape = newnodeconf.shape;
                                node_cfg.icon = newnodeconf.icon;
                                node_cfg.image = newnodeconf.image;
                            }
                        } else {
                            node_cfg.image = {};
                        }

                        network_nodes.add([node_cfg]);
                    });

                    $.each( data.edges, function( edgeid, edge) {
                        // Scale values to fit into widget area
                        var mid_x =  Math.floor(scale * edge.mid_x);
                        var mid_y =  Math.floor(scale * edge.mid_y);

                        var mid = {id: edgeid + "_mid", shape: "dot", size: 0, x: mid_x, y: mid_y, label: edge.label, font: {face: edge.text_face, size:  Math.floor(scale * edge.text_size), color: edge.text_colour}};

                        if (Boolean(reverse_arrows)) {
                            arrows = {from: {enabled: true, scaleFactor: 0.6}, to: {enabled: false}};
                        } else {
                            arrows = {to: {enabled: true, scaleFactor: 0.6}, from: {enabled: false}};
                        }

                        var edge1 = {id: edgeid + "_from", from: edge.custom_map_node1_id, to: edgeid + "_mid", arrows: arrows, font: {face: edge.text_face, size:  Math.floor(scale * edge.text_size), color: edge.text_colour}, smooth: {type: edge.style}};
                        var edge2 = {id: edgeid + "_to", from: edge.custom_map_node2_id, to: edgeid + "_mid", arrows: arrows, font: {face: edge.text_face, size:  Math.floor(scale * edge.text_size), color: edge.text_colour}, smooth: {type: edge.style}};

                        // Special case for curved lines
                        if(edge2.smooth.type == "curvedCW") {
                            edge2.smooth.type = "curvedCCW";
                        } else if (edge2.smooth.type == "curvedCCW") {
                            edge2.smooth.type = "curvedCW";
                        }
                        if(edge.port_id) {
                            var edge_port_from;
                            var edge_port_to;
                            if (Boolean(reverse_arrows)) {
                                port_from = edge2;
                                port_to = edge1;
                            } else {
                                port_from = edge1;
                                port_to = edge2;
                            }
                            port_from.title = port_to.title = edge.port_info;
                            if(edge.showpct) {
                                port_from.label = edge.port_frompct + "%";
                                port_to.label = edge.port_topct + "%";
                            }
                            if(edge.showbps) {
                                if(port_from.label == null) {
                                    port_from.label = '';
                                    port_to.label = '';
                                } else {
                                    port_from.label += "\n";
                                    port_to.label += "\n";
                                }
                                port_from.label += edge.port_frombps;
                                port_to.label += edge.port_tobps;
                            }
                            port_from.color = {color: edge.colour_from};
                            port_from.width = edge.width_from;
                            port_to.color = {color: edge.colour_to};
                            port_to.width = edge.width_to;

                            edge_port_map[edgeid] = {device_id: edge.device_id, port_id: edge.port_id};
                        }
                        network_nodes.add([mid]);
                        network_edges.add([edge1, edge2]);
                    });

                    if ({{ $map->legend_x }} >= 0) {
                        // TODO: Scale
                        let legend_font_size =  Math.floor(scale * {{ $map->legend_font_size }});
                        let y_pos =  Math.floor(scale * {{ $map->legend_y }});
                        let x_pos =  Math.floor(scale * {{ $map->legend_x }});
                        let y_inc = legend_font_size + 10;

                        let legend_header = {id: "legend_header", label: "<b>Legend</b>", shape: "box", borderWidth: 0, x: x_pos, y: y_pos, font: {multi: 'html', size: legend_font_size}, color: {background: "white"}};
                        network_nodes.add(legend_header);
                        y_pos += y_inc;

                        if (!(Boolean({{ $map->legend_hide_invalid }}))) {
                            let legend_invalid = {id: "legend_invalid", label: "???", title: "Link is down or link speed is not defined", shape: "box", borderWidth: 0, x: x_pos, y: y_pos, font: {face: 'courier new', size: legend_font_size, color: "white"}, color: {background: "black"}};
                            y_pos += y_inc;
                            network_nodes.add(legend_invalid);
                        }

                        let pct_step;
                        if (Boolean({{ $map->legend_hide_overspeed }})) {
                            pct_step = 100.0 / ({{ $map->legend_steps }} - 1);
                        } else {
                            pct_step = 150.0 / ({{ $map->legend_steps }} - 1);
                        }
                        for (let i=0; i < {{ $map->legend_steps }}; i++) {
                            let this_pct = Math.round(pct_step * i);
                            let legend_step = {id: "legend_" + i.toString(), label: this_pct.toString().padStart(3, " ") + "%", shape: "box", borderWidth: 0, x: x_pos, y: y_pos, font: {face: 'courier new', size: legend_font_size, color: "black"}, color: {background: legendPctColour(this_pct)}};
                            network_nodes.add(legend_step);
                            y_pos += y_inc;
                        }
                    }

                    // Flush in order to make sure nodes exist for edges to connect to
                    network_nodes.flush();
                    network_edges.flush();

                    var container = document.getElementById('custom-map-{{ $id }}');
                    network = new vis.Network(container, {nodes: network_nodes, edges: network_edges, stabilize: true}, network_options);

                    // width/height might be % get values in pixels
                    var centreY = Math.round(network_height / 2);
                    var centreX = Math.round(network_width / 2);
                    network.moveTo({position: {x: centreX, y: centreY}, scale: 1});

                    setCustomMapBackground('custom-map-{{ $id }}', bgtype, bgdata);

                    network.on('doubleClick', function (properties) {
                        edge_id = null;
                        if (properties.nodes.length > 0) {
                            if(properties.nodes[0] in node_device_map) {
                                window.location.href = "device/"+node_device_map[properties.nodes[0]].device_id;
                            } else if (properties.nodes[0] in node_link_map) {
                                window.location.href = '{{ route('maps.custom.show', ['map' => '?']) }}'.replace('?', node_link_map[properties.nodes[0]]);
                            } else if (properties.nodes[0].endsWith('_mid')) {
                                edge_id = properties.nodes[0].split("_")[0];
                            }
                        } else if (properties.edges.length > 0) {
                            edge_id = properties.edges[0].split("_")[0];
                        }

                        if (edge_id && (edge_id in edge_port_map)) {
                           window.location.href = 'device/device=' + edge_port_map[edge_id].device_id + '/tab=port/port=' + edge_port_map[edge_id].port_id + '/';
                        }
                    });
            });
        });
    })();
</script>
