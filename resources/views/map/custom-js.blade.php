<script type="text/javascript" src="{{ asset('js/vis-network.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/vis-data.min.js') }}"></script>
<script type="text/javascript">
    var custommap = {
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
            old_nodes = nodes.get({filter: function(node) { return node.id.startsWith("legend_") }});
            old_nodes.forEach((node) => {
                nodes.remove(node.id);
            });
            if (x_pos >= 0) {
                font_size =  font_size;
                y_pos =  y_pos;
                x_pos =  x_pos;
                let y_inc = font_size + 10;

                let legend_header = {id: "legend_header", label: "<b>Legend</b>", shape: "box", borderWidth: 0, x: x_pos, y: y_pos, font: {multi: 'html', size: font_size}, color: {background: "white"}};
                nodes.add(legend_header);
                y_pos += y_inc;

                if (!(Boolean(hide_invalid))) {
                    let this_colour = "black";
                    if(colours) {
                        this_colour = colours['-1'];
                    }
                    let legend_invalid = {id: "legend_invalid", label: "???", title: "Link is down or link speed is not defined", shape: "box", borderWidth: 0, x: x_pos, y: y_pos, font: {face: 'courier new', size: font_size, color: "white"}, color: {background: this_colour}};
                    y_pos += y_inc;
                    nodes.add(legend_invalid);
                }

                if(colours) {
                    let i = 0;
                    Object.keys(colours).sort((a,b) => parseInt(a) > parseInt(b)).forEach((pct_key) => {
                        let this_pct = parseFloat(pct_key);
                        if(!isNaN(this_pct) && this_pct >= 0.0) {
                            let legend_step = {id: "legend_" + i.toString(), label: this_pct.toString().padStart(3, " ") + "%", shape: "box", borderWidth: 0, x: x_pos, y: y_pos, font: {face: 'courier new', size: font_size, color: "black"}, color: {background: colours[pct_key]}};
                            nodes.add(legend_step);
                            y_pos += y_inc;
                            i++;
                        }
                    });
                } else {
                    let pct_step;
                    if (Boolean(hide_overspeed)) {
                        pct_step = 100.0 / (num_steps - 1);
                    } else {
                        pct_step = 150.0 / (num_steps - 1);
                    }
                    for (let i=0; i < num_steps; i++) {
                        let this_pct = Math.round(pct_step * i);
                        let legend_step = {id: "legend_" + i.toString(), label: this_pct.toString().padStart(3, " ") + "%", shape: "box", borderWidth: 0, x: x_pos, y: y_pos, font: {face: 'courier new', size: font_size, color: "black"}, color: {background: custommap.legendPctDefaultColour(this_pct)}};
                        nodes.add(legend_step);
                        y_pos += y_inc;
                    }
                }
                nodes.flush();
            }
        },

        createNetwork: function (elementId, scale, nodes, edges, options, bgtype, bgdata) {
            // Flush the nodes and edges so they are rendered immediately
            nodes.flush();
            edges.flush();

            var container = document.getElementById(elementId);
            var network = new vis.Network(container, {nodes: nodes, edges: edges, stabilize: true}, options);

            // width/height might be % get values in pixels
            network_height = $($(container).children(".vis-network")[0]).height();
            network_width = $($(container).children(".vis-network")[0]).width();
            var centreY = Math.round(network_height / (2 * scale));
            var centreX = Math.round(network_width / (2 * scale));
            network.moveTo({position: {x: centreX, y: centreY}, scale: scale});

            setCustomMapBackground(elementId, bgtype, bgdata);

            network.on('zoom', function (data) {
                if(data.scale < scale) {
                    network.moveTo({position: {x: centreX, y: centreY}, scale: scale});
                }
            });

            return network;
        },

        getNodeCfg: function (nodeid, node, screenshot, custom_image_base) {
            let nodeimage_base = '{{ route('maps.nodeimage.show', ['image' => '?' ]) }}'.replace("?", "");
            var node_cfg = {};
            node_cfg.id = nodeid;

            if(node.linked_map_name) {
                node_cfg.title = "Go to " + node.linked_map_name;
            } else if(node.device_id) {
                node_cfg.title = document.createElement("div");
                node_cfg.title.innerHTML = node.device_info;
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
            node_cfg.font = {face: node.text_face, size: node.text_size, color: node.text_colour};
            node_cfg.size = node.size;
            node_cfg.color = {background: node.colour_bg_view, border: node.colour_bdr_view};
            if(node.style == "icon") {
                node_cfg.icon = {face: 'FontAwesome', code: String.fromCharCode(parseInt(node.icon, 16)), size: node.size, color: node.colour_bdr};
            } else {
                node_cfg.icon = {};
            }
            if(node.style == "image" || node.style == "circularImage") {
                if(node.image) {
                    node_cfg.image = {unselected: custom_image_base + node.image};
                } else if(node.nodeimage) {
                    node_cfg.image = {unselected: nodeimage_base + node.nodeimage};
                } else if (node.device_image) {
                    node_cfg.image = {unselected: node.device_image};
                } else {
                    // Default to box if we do not get a valid image from the database
                    node.style = 'box';
                    node_cfg.shape = 'box';
                    node_cfg.image = undefined;
                }
            } else {
                node_cfg.image = undefined;
            }
            if(! ["ellipse", "circle", "database", "box", "text"].includes(node.style)) {
                node_cfg.font.background = "#FFFFFF";
            }
            return node_cfg;
        },

        getEdgeCfg: function (edgeid, edge, fromto, reverse_arrows) {
            if (Boolean(reverse_arrows)) {
                arrows = {from: {enabled: true, scaleFactor: 0.6}, to: {enabled: false}};
            } else {
                arrows = {to: {enabled: true, scaleFactor: 0.6}, from: {enabled: false}};
            }

            var edge_cfg = {id: edgeid + "_" + fromto, to: edgeid + "_mid", arrows: arrows, font: {face: edge.text_face, size: edge.text_size, color: edge.text_colour, background: "#FFFFFF", align: edge.text_align || "horizontal"}, smooth: {type: edge.style}, arrowStrikethrough: false};
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

                // Special case for curved lines
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
            var mid_x =  edge.mid_x;
            var mid_y =  edge.mid_y;

            return {id: edgeid + "_mid", shape: "dot", size: 0, x: mid_x, y: mid_y, label: screenshot ? '' : edge.label, font: {face: edge.text_face, size:  edge.text_size, color: edge.text_colour}};
        },
    }
</script>
