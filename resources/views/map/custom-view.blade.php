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
      <div id="map-container">
        <div id="custom-map"></div>
        <x-geo-map id="custom-map-bg-geo-map"
           :init="$background_type == 'map'"
           :width="$map_conf['width']"
           :height="$map_conf['height']"
           :config="$background_config"
           readonly
        />
      </div>
    </div>
  </div>
</div>
@endsection

@section('javascript')
<script type="text/javascript" src="{{ asset('js/vis.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/leaflet.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/L.Control.Locate.min.js') }}"></script>
@endsection

@push('styles')
<style>
    #map-container {
        display: grid;
        grid-template: 1fr / 1fr;
        place-items: center;
    }
    #custom-map {
        grid-column: 1 / 1;
        grid-row: 1 / 1;
        z-index: 2;
    }
    #custom-map-bg-geo-map {
        grid-column: 1 / 1;
        grid-row: 1 / 1;
        z-index: 1;
    }
</style>
@endpush

@section('scripts')
@include('map.custom-js')
<script type="text/javascript">
    var bgtype = {{ Js::from($background_type) }};
    var bgdata = {{ Js::from($background_config) }};
    var screenshot = {{ $screenshot ? "true" : "false" }};
    var reverse_arrows = {{$reverse_arrows}};
    var legend = @json($legend);
    var network;
    var network_nodes = new vis.DataSet({queue: {delay: 100}});
    var network_edges = new vis.DataSet({queue: {delay: 100}});
    var edge_port_map = {};
    var node_device_map = {};
    var node_link_map = {};
    var custom_image_base = "{{ $base_url }}images/custommap/icons/";
    var network_options = {{ Js::from($map_conf) }};

    var Countdown;
    function refreshMap() {
        $.get( '{{ route('maps.custom.data', ['map' => $map_id]) }}')
            .done(function( data ) {
                // Add/update nodes
                $.each( data.nodes, function( nodeid, node) {
                    var node_cfg = custommap.getNodeCfg(nodeid, node, screenshot, custom_image_base);
                    if(node.device_id) {
                        node_device_map[nodeid] = {device_id: node.device_id, device_name: node.device_name};
                        delete node_link_map[nodeid];
                    } else if(node.linked_map_name) {
                        delete node_device_map[nodeid];
                        node_link_map[nodeid] = node.linked_map_id;
                    } else {
                        delete node_device_map[nodeid];
                        delete node_link_map[nodeid];
                    }

                    if (network_nodes.get(nodeid)) {
                        network_nodes.update(node_cfg);
                    } else {
                        network_nodes.add([node_cfg]);
                    }
                });

                $.each( data.edges, function( edgeid, edge) {
                    var mid = custommap.getEdgeMidCfg(edgeid, edge, screenshot);
                    var edge1 = custommap.getEdgeCfg(edgeid, edge, "from", reverse_arrows);
                    var edge2 = custommap.getEdgeCfg(edgeid, edge, "to", reverse_arrows);
                    if(edge.port_id) {
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
                custommap.redrawDefaultLegend(network_nodes, legend.steps, legend.x, legend.y, legend.font_size, legend.hide_invalid, legend.hide_overspeed);

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
            network = custommap.createNetwork("custom-map", 1, network_nodes, network_edges, network_options, bgtype, bgdata);

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
