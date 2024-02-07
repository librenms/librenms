@extends('layouts.librenmsv1')

@section('title', __('map.custom.title.view', ['name' => $name]))

@section('content')
<div class="container-fluid">
  <div class="row" id="alert-row">
    <div class="col-md-12">
      <div class="alert alert-warning" role="alert" id="alert">{{ __('map.custom.view.loading') }}</div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <center>
        <div id="custom-map"></div>
      </center>
    </div>
  </div>
</div>
@endsection

@section('javascript')
<script type="text/javascript" src="{{ asset('js/vis.min.js') }}"></script>
@endsection

@section('scripts')
<script type="text/javascript">
    var bgimage = {{ $background ? "true" : "false" }};
    var screenshot = {{ $screenshot ? "true" : "false" }};
    var reverse_arrows = {{$reverse_arrows}};
    var legend = @json($legend);
    var network;
    var network_height;
    var network_width;
    var network_nodes = new vis.DataSet({queue: {delay: 100}});
    var network_edges = new vis.DataSet({queue: {delay: 100}});
    var edge_port_map = {};
    var node_device_map = {};
    var node_link_map = {};
    var custom_image_base = "{{ $base_url }}images/custommap/icons/";

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

    function redrawLegend() {
        // Clear out the old legend
        old_nodes = network_nodes.get({filter: function(node) { return node.id.startsWith("legend_") }});
        old_nodes.forEach((node) => {
            network_nodes.remove(node.id);
        });
        if (legend.x >= 0) {
            let y_pos = legend.y;
            let y_inc = legend.font_size + 10;

            let legend_header = {id: "legend_header", label: "<b>Legend</b>", shape: "box", borderWidth: 0, x: legend.x, y: y_pos, font: {multi: 'html', size: legend.font_size}, color: {background: "white"}};
            network_nodes.add(legend_header);
            y_pos += y_inc;

            if (!(Boolean(legend.hide_invalid))) {
                let legend_invalid = {id: "legend_invalid", label: "???", title: "Link is down or link speed is not defined", shape: "box", borderWidth: 0, x: legend.x, y: y_pos, font: {face: 'courier new', size: legend.font_size, color: "white"}, color: {background: "black"}};
                y_pos += y_inc;
                network_nodes.add(legend_invalid);
            }

            let pct_step;
            if (Boolean(legend.hide_overspeed)) {
                pct_step = 100.0 / (legend.steps - 1);
            } else {
                pct_step = 150.0 / (legend.steps - 1);
            }
            for (let i=0; i < legend.steps; i++) {
                let this_pct = Math.round(pct_step * i);
                let legend_step = {id: "legend_" + i.toString(), label: this_pct.toString().padStart(3, " ") + "%", shape: "box", borderWidth: 0, x: legend.x, y: y_pos, font: {face: 'courier new', size: legend.font_size, color: "black"}, color: {background: legendPctColour(this_pct)}};
                network_nodes.add(legend_step);
                y_pos += y_inc;
            }
            network_nodes.flush();
        }
    }

    function CreateNetwork() {
        // Flush the nodes and edges so they are rendered immediately
        network_nodes.flush();
        network_edges.flush();

        var container = document.getElementById('custom-map');
        var options = {!! json_encode($map_conf) !!};

        network = new vis.Network(container, {nodes: network_nodes, edges: network_edges, stabilize: true}, options);
        network_height = $($(container).children(".vis-network")[0]).height();
        network_width = $($(container).children(".vis-network")[0]).width();
        var centreY = parseInt(network_height / 2);
        var centreX = parseInt(network_width / 2);

        network.moveTo({position: {x: centreX, y: centreY}, scale: 1});

        if(bgimage) {
            canvas = $("#custom-map").children()[0].canvas;
            $(canvas).css('background-image','url({{ route('maps.custom.background', ['map' => $map_id]) }}?ver={{$bgversion}})').css('background-size', 'cover');
        }

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
    }
    var Countdown;
    function refreshMap() {
        $.get( '{{ route('maps.custom.data', ['map' => $map_id]) }}')
            .done(function( data ) {
                // Add/update nodes
                $.each( data.nodes, function( nodeid, node) {
                    var node_cfg = {};
                    node_cfg.id = nodeid;
                    if(node.device_id) {
                        node_device_map[nodeid] = {device_id: node.device_id, device_name: node.device_name};
                        delete node_link_map[nodeid];
                        node_cfg.title = node.device_info;
                    } else if(node.linked_map_name) {
                        delete node_device_map[nodeid];
                        node_link_map[nodeid] = node.linked_map_id;
                        node_cfg.title = "Go to " + node.linked_map_name;
                    } else {
                        node_cfg.title = null;
                    }
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

                    if (network_nodes.get(nodeid)) {
                        network_nodes.update(node_cfg);
                    } else {
                        network_nodes.add([node_cfg]);
                    }
                });

                $.each( data.edges, function( edgeid, edge) {
                    var mid_x = edge.mid_x;
                    var mid_y = edge.mid_y;

                    var mid = {id: edgeid + "_mid", shape: "dot", size: 0, x: mid_x, y: mid_y, label: screenshot ? '' : edge.label};

                    if (Boolean(reverse_arrows)) {
                        arrows = {from: {enabled: true, scaleFactor: 0.6}, to: {enabled: false}};
                    } else {
                        arrows = {to: {enabled: true, scaleFactor: 0.6}, from: {enabled: false}};
                    }

                    var edge1 = {id: edgeid + "_from", from: edge.custom_map_node1_id, to: edgeid + "_mid", arrows: arrows, font: {face: edge.text_face, size: edge.text_size, color: edge.text_colour}, smooth: {type: edge.style}};
                    var edge2 = {id: edgeid + "_to", from: edge.custom_map_node2_id, to: edgeid + "_mid", arrows: arrows, font: {face: edge.text_face, size: edge.text_size, color: edge.text_colour}, smooth: {type: edge.style}};

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
                    } else {
                        delete edge_port_map[edgeid];
                    }
                    if (network_nodes.get(mid.id)) {
                        network_nodes.update(mid);
                        network_edges.update(edge1);
                        network_edges.update(edge2);
                    } else {
                        network_nodes.add([mid]);
                        network_edges.add([edge1, edge2]);
                    }
                });

                // Remove any nodes that are not in the database, includes edges
                $.each( network_nodes.getIds(), function( node_idx, nodeid ) {
                    if(nodeid.endsWith('_mid')) {
                        edgeid = nodeid.split("_")[0];
                        if(! (edgeid in data.edges)) {
                            network_nodes.remove(edgeid + "_mid");
                            network_edges.remove(edgeid + "_to");
                            network_edges.remove(edgeid + "_from");
                        }
                    } else {
                        if(! (nodeid in data.nodes)) {
                            network_nodes.remove(nodeid);
                        }
                    }
                });

                // Re-draw the legend
                redrawLegend();

                // Flush in order to make sure nodes exist for edges to connect to
                network_nodes.flush();
                network_edges.flush();
                if (Object.keys(data).length == 0) {
                    $("#alert").text('{{ __('map.custom.view.no_devices') }}');
                    $("#alert-row").show();
                } else {
                    $("#alert").text("");
                    $("#alert-row").hide();
                }
            });

        // Initialise map if it does not exist
        if (! network) {
            CreateNetwork();
        }
    }

    $(document).ready(function () {
        Countdown = {
            sec: {{$page_refresh}},

            Start: function () {
                var cur = this;
                this.interval = setInterval(function () {
                    cur.sec -= 1;
                    if (cur.sec <= 0) {
                        refreshMap();
                        cur.sec = {{$page_refresh}};
                    }
                }, 1000);
            },

            Pause: function () {
                clearInterval(this.interval);
                delete this.interval;
            },
        };

        Countdown.Start();
        refreshMap();
    });
</script>
@endsection

