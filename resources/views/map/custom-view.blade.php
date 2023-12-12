@extends('layouts.librenmsv1')

@section('title', __($name . ' | Custom Map'))

@section('content')
<div class="container-fluid">
  <div class="row" id="alert-row">
    <div class="col-md-12">
      <div class="alert alert-warning" role="alert" id="alert">Loading data</div>
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

@section('javascript')
<script type="text/javascript" src="{{ asset('js/vis.min.js') }}"></script>
@endsection

@section('scripts')
<script type="text/javascript">
    var bgimage = {{ $background ? "true" : "false" }};
    var network;
    var network_height;
    var network_width;
    var network_nodes = new vis.DataSet({queue: {delay: 100}});
    var network_edges = new vis.DataSet({queue: {delay: 100}});
    var node_device_map = {};

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
            $(canvas).css('background-image','url({{ route('maps.custom.background', ['map_id' => $map_id]) }})').css('background-size', 'cover');
        }

        network.on('doubleClick', function (properties) {
            if (properties.nodes > 0 && properties.nodes[0] in node_device_map) {
                window.location.href = "device/"+node_device_map[properties.nodes[0]].device_id;
            }
        });
    }
    var Countdown;
    function refreshMap() {
        $.get( '{{ route('maps.custom.getdata', ['map_id' => $map_id]) }}')
            .done(function( data ) {
                // Add/update nodes
                $.each( data.nodes, function( nodeid, node) {
                    var node_cfg = {};
                    node_cfg.id = nodeid;
                    if(node.device_id) {
                        node_device_map[nodeid] = {device_id: node.device_id, device_name: node.device_name};
                        node_cfg.title = node.device_info;
                        node_cfg.image = {unselected: node.device_image};
                    } else {
                        node_cfg.title = null;
                        node_cfg.image = {};
                    }
                    node_cfg.label = node.label;
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

                    if (network_nodes.get(nodeid)) {
                        network_nodes.update(node_cfg);
                    } else {
                        network_nodes.add([node_cfg]);
                    }
                });

                $.each( data.edges, function( edgeid, edge) {
                    var mid_x = edge.mid_x;
                    var mid_y = edge.mid_y;

                    var mid = {id: edgeid + "_mid", shape: "dot", size: 0, x: mid_x, y: mid_y};

                    var edge1 = {id: edgeid + "_from", from: edge.custom_map_node1_id, to: edgeid + "_mid", arrows: {to: {enabled: true, scaleFactor: 0.6}}, font: {face: edge.text_face, size: edge.text_size, color: edge.text_colour}, smooth: {type: edge.style}};
                    var edge2 = {id: edgeid + "_to", from: edge.custom_map_node2_id, to: edgeid + "_mid", arrows: {to: {enabled: true, scaleFactor: 0.6}}, font: {face: edge.text_face, size: edge.text_size, color: edge.text_colour}, smooth: {type: edge.style}};

                    // Special case for curved lines
                    if(edge2.smooth.type == "curvedCW") {
                        edge2.smooth.type = "curvedCCW";
                    } else if (edge2.smooth.type == "curvedCCW") {
                        edge2.smooth.type = "curvedCW";
                    }
                    if(edge.port_id) {
                        edge1.title = edge2.title = edge.port_info;
                        if(edge.showpct) {
                            edge1.label = edge.port_frompct + "%";
                            edge2.label = edge.port_topct + "%";
                        }
                        edge1.color = {color: edge.colour_from};
                        edge1.width = edge.width_from;
                        edge2.color = {color: edge.colour_to};
                        edge2.width = edge.width_to;
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

                // Flush in order to make sure nodes exist for edges to connect to
                network_nodes.flush();
                network_edges.flush();
                if (Object.keys(data).length == 0) {
                    $("#alert").html("No devices found");
                    $("#alert-row").show();
                } else {
                    $("#alert").html("");
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
