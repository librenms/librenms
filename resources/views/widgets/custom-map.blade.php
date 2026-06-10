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
        var bgtype = {{ Js::from($map->background_type) }};
        var bgdata = {{ Js::from($background_config) }};
        var reverse_arrows = {{$map->reverse_arrows}};
        var legend = @json($map->legend);
        var custom_image_base = "{{ $base_url }}images/custommap/icons/";
        var network_nodes = new vis.DataSet({queue: {delay: 100}});
        var network_edges = new vis.DataSet({queue: {delay: 100}});
        var edge_port_map = {};

        var network_options = {{ Js::from($map_conf) }};

        var scale = {{ $scale }}

        $.get( '{{ route('maps.custom.data', ['map' => $map->custom_map_id]) }}')
            .done(function( data ) {
                // Add/update nodes
                $.each( data.nodes, function( nodeid, node) {
                    var node_cfg = custommap.getNodeCfg(nodeid, node, Boolean({{$screenshot}}), custom_image_base);
                    network_nodes.add([node_cfg]);
                });

                $.each( data.edges, function( edgeid, edge) {
                    var mid = custommap.getEdgeMidCfg(edgeid, edge, Boolean({{$screenshot}}));
                    var edge1 = custommap.getEdgeCfg(edgeid, edge, "from", reverse_arrows);
                    var edge2 = custommap.getEdgeCfg(edgeid, edge, "to", reverse_arrows);

                    if(edge.port_id) {
                        edge_port_map[edgeid] = {device_id: edge.device_id, port_id: edge.port_id};
                    }
                    network_nodes.add([mid]);
                    network_edges.add([edge1, edge2]);
                });

                custommap.redrawDefaultLegend(network_nodes, {{ $map->legend_steps }}, {{ $map->legend_x }}, {{ $map->legend_y }}, {{ $map->legend_font_size }}, {{ $map->legend_hide_invalid }}, {{ $map->legend_hide_overspeed }}, {{ Js::from($map->legend_colours) }});

                network = custommap.createNetwork('custom-map-{{ $id }}', scale, network_nodes, network_edges, network_options, bgtype, bgdata);

                network.on('doubleClick', function (properties) {
                    edge_id = null;
                    if (properties.nodes.length > 0) {
                        node_id = properties.nodes[0];
                        node = network_nodes.get(node_id);
                        if(node.linked_map_id) {
                            window.location.href = '{{ route('maps.custom.show', ['map' => '?']) }}'.replace('?', node.linked_map_id);
                        } else if (node.device_id) {
                            window.location.href = "device/"+node.device_id;
                        } else if (node_id.endsWith('_mid')) {
                            edge_id = node_id.split("_")[0];
                        }
                    } else if (properties.edges.length > 0) {
                        edge_id = properties.edges[0].split("_")[0];
                    }

                    if (edge_id && (edge_id in edge_port_map)) {
                       window.location.href = 'device/device=' + edge_port_map[edge_id].device_id + '/tab=port/port=' + edge_port_map[edge_id].port_id + '/';
                    }
                });
        });
    })();
</script>
