@extends('layouts.librenmsv1')

@section('title', __('map.custom.title.edit'))

@section('content')

@include('map.custom-background-modal')
@include('map.custom-node-modal')
@include('map.custom-edge-modal')
@include('map.custom-map-modal')
@include('map.custom-legend-modal')
@include('map.custom-map-list-modal')

<div class="container-fluid">
  <div class="row" id="control-row">
    <div class="col-md-5">
      <button type=button value="mapedit" id="map-editButton" class="btn btn-primary" onclick="editMapSettings()">{{ __('map.custom.edit.map.edit') }}</button>
      <button type=button value="mapbg" id="map-bgButton" class="btn btn-primary" onclick="editMapBackground()">{{ __('map.custom.edit.bg.title') }}</button>
      <button type=button value="mapbg" id="map-bgEndAdjustButton" class="btn btn-primary" onclick="endBackgroundMapAdjust()" style="display:none">{{ __('map.custom.edit.bg.adjust_map_finish') }}</button>
      <button type=button value="editnodedefaults" id="map-nodeDefaultsButton" class="btn btn-primary" onclick="nodeEdit(newnodeconf)">{{ __('map.custom.edit.node.edit_defaults') }}</button>
      <button type=button value="editedgedefaults" id="map-edgeDefaultsButton" class="btn btn-primary" onclick="edgeEditDefaults()">{{ __('map.custom.edit.edge.edit_defaults') }}</button>
      <button type=button value="togglelegend" id="map-legendToggleButton" class="btn btn-primary" onclick="toggleLegend()">{{ __('map.custom.edit.map.legend_toggle') }}</button>
    </div>
    <div class="col-md-2">
      <center>
          <h4><a id="title" href="{{ route('maps.custom.show', $map_id) }}">{{ $name }}</a></h4>
      </center>
    </div>
    <div class="col-md-5 text-right">
      <button type=button value="mapselectall" id="map-selectallButton" class="btn btn-primary" onclick="network.selectNodes(network_nodes.getIds());" title="{{ __('map.custom.edit.map.multiselect_info') }}">{{ __('map.custom.edit.map.selectall') }}</button>
      <button type=button value="maprender" id="map-renderButton" class="btn btn-primary" style="display: none" onclick="CreateNetwork();">{{ __('map.custom.edit.map.rerender') }}</button>
      <button type=button value="mapsave" id="map-saveDataButton" class="btn btn-primary" style="display: none" onclick="saveMapData();">{{ __('map.custom.edit.map.save') }}</button>
      <button type=button value="maplist" id="map-listButton" class="btn btn-primary" onclick="mapList();">{{ __('map.custom.edit.map.list') }}</button>
    </div>
  </div>
  <div class="row" id="control-map-sep">
    <div class="col-md-12">
      <hr>
    </div>
  </div>
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
@endsection

@section('javascript')
<script type="text/javascript" src="{{ asset('js/vis-network.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/vis-data.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/leaflet.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/L.Control.Locate.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/leaflet-image.js') }}"></script>
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
<script type="text/javascript">
    var bgtype = {{ Js::from($background_type) }};
    var bgdata = {{ Js::from($background_config) }};
    var network;
    var network_height;
    var network_width;
    var network_nodes = new vis.DataSet({queue: {delay: 100}});
    var network_edges = new vis.DataSet({queue: {delay: 100}});
    var edge_nodes_map = [];
    var node_device_map = {};
    var custom_image_base = "{{ $base_url }}images/custommap/icons/";
    var nodeimage_base = '{{ route('maps.nodeimage.show', ['image' => '?' ]) }}'.replace("?", "");
    var network_options = {{ Js::from($map_conf) }}

    function edgeNodesRemove(nm_id, edgeid) {
        // Remove old item from map if it exists
        if (nm_id in edge_nodes_map) {
            const edge_idx = edge_nodes_map[nm_id].indexOf(edgeid);
            if (edge_idx >= 0) {
                edge_nodes_map[nm_id].splice(edge_idx, 1);
            }
        }
    }

    function edgeNodesUpdate(edgeid, node1_id, node2_id, old_node1_id, old_node2_id) {
        var nm_id = node1_id < node2_id ? node1_id + '.' + node2_id : node2_id + '.' + node1_id;
        var old_nm_id = old_node1_id < old_node2_id ? old_node1_id + '.' + old_node2_id : old_node2_id + '.' + old_node1_id;

        // No update is needed if the new and old are the same
        if (nm_id == old_nm_id) {
            return;
        }

        if (old_node1_id > 0 && old_node2_id > 0) {
            edgeNodesRemove(old_nm_id, edgeid);
        }

        if (!(nm_id in edge_nodes_map)) {
            edge_nodes_map[nm_id] = [];
        }
        edge_nodes_map[nm_id].push(edgeid);
    }

    function getMidOffests(pos1, pos2) {
        // First work out which pos is on the left-hand side
        var left_pos;
        var right_pos;
        if(pos1.x < pos2.x) {
            left_pos = pos1;
            right_pos = pos2;
        } else {
            left_pos = pos2;
            right_pos = pos1;
        }

        // The X axis needs to move left/right based on whether the line rises or falls
        var x_diff = right_pos.y - left_pos.y;
        // The Y axis needs to move up always based on how far apart the left and right nodes are
        var y_diff = left_pos.x - right_pos.x;

        // Calculate how far each mid point needs to move
        var tot_diff = Math.abs(x_diff) + Math.abs(y_diff);
        return {x: Math.round(edge_sep * (x_diff / tot_diff)), y: Math.round(edge_sep * (y_diff / tot_diff))};
    }

    function getMidPos(edgeid, from_id, to_id) {
        var nm_id = from_id < to_id ? from_id + '.' + to_id : to_id + '.' + from_id;
        const node_links = nm_id in edge_nodes_map ? edge_nodes_map[nm_id] : [];

        var node_offsets = [];
        node_links.forEach((link_edgeid) => {
            // Ignore the edge we are creating
            if (link_edgeid == edgeid) {
                return;
            }

            // Save the offset in the hash
            let link_mid = network_nodes.get(link_edgeid + "_mid");
            let link_mid_offset = link_mid.x + '.' + link_mid.y;
            node_offsets[link_mid_offset] = true;
        });

        var pos = network.getPositions([from_id, to_id]);

        const offsets = getMidOffests(pos[from_id], pos[to_id]);

        // Calculate the center point
        var mid_center = {x: (pos[from_id].x + pos[to_id].x) >> 1, y: (pos[from_id].y + pos[to_id].y) >> 1};
        var mids = [mid_center];
        for (let i = 1; i < node_links.length; i++) {
            let multiplier = ((i + 1) >> 1);
            let this_x = mid_center.x;
            let this_y = mid_center.y;
            if(i & 1) {
                // Odd numbers go the normal direction
                mids.push({x: mid_center.x + (multiplier * offsets.x), y: mid_center.y + (multiplier * offsets.y)});
            } else {
                // Even numbers go the opposite direction
                mids.push({x: mid_center.x - (multiplier * offsets.x), y: mid_center.y - (multiplier * offsets.y)});
            }
        }

        // Find the first unused mid point from the center
        for (let i = 0; i < mids.length; i++) {
            let this_offset = mids[i].x + '.' + mids[i].y;
            if (!(this_offset in node_offsets)) {
                return {x: mids[i].x, y: mids[i].y};
            }
        }

        // Default to mid point
        return {x: mid_center.x, y: mid_center.y};
    }

    function fixNodePos(nodeid, node) {
        var move=false;
        var is_aux = nodeid.endsWith("_mid") || (typeof nodeid === 'string' && /_w[ft]_\d+$/.test(nodeid));
        if ( node_align && !is_aux) {
            node.x = Math.round(node.x / node_align) * node_align;
            node.y = Math.round(node.y / node_align) * node_align;
            move = true;
        }
        if ( node.x < {{ $hmargin }} ) {
            node.x = {{ $hmargin }};
            move = true;
        } else if ( node.x > network_width - {{ $hmargin }} ) {
            node.x = network_width - {{ $hmargin }};
            move = true;
        }
        if ( node.y < {{ $vmargin }} ) {
            node.y = {{ $vmargin }};
            move = true;
        } else if ( node.y > network_height - {{ $vmargin }} ) {
            node.y = network_height - {{ $vmargin }};
            move = true;
        }
        return move;
    }

    function CreateNetwork() {
        // Flush the nodes and edges so they are rendered immediately
        network_nodes.flush();
        network_edges.flush();

        var container = document.getElementById('custom-map');
        var options = network_options;

        // Set up the triggers for adding and editing map items
        options['manipulation']['addNode'] = function (data, callback) {
                callback(null);
                var node = structuredClone(newnodeconf);
                node.id = "new" + newcount++;
                node.label = "New Node";
                node.x = node_align ? Math.round(data.x / node_align) * node_align : data.x;
                node.y = node_align ? Math.round(data.y / node_align) * node_align : data.y;
                node.add = true;
                nodeEdit(node);
            }
        options['manipulation']['editNode'] = function (data, callback) {
                callback(null);
                checkEditNode(data);
            }
        options['manipulation']['deleteNode'] = function (data, callback) {
                callback(null);

                // Separate waypoint dot nodes from real map nodes
                var real_nodes = [];
                var rebuild_edges = {};
                $.each( data.nodes, function( node_idx, nodeid ) {
                    var m = (typeof nodeid === 'string') ? nodeid.match(/^(.+)_w[ft]_\d+$/) : null;
                    if (m) {
                        rebuild_edges[m[1]] = true;
                        network_nodes.remove(nodeid);
                    } else {
                        real_nodes.push(nodeid);
                    }
                });

                // Only delete whole edges when a real node is being removed (not when removing a waypoint)
                if (real_nodes.length > 0) {
                    $.each( data.edges, function( edge_idx, edgeid ) {
                        edgeid = edgeid.split("_")[0];
                        deleteEdge(edgeid);
                    });
                    $.each( real_nodes, function( node_idx, nodeid ) {
                        network_nodes.remove(nodeid);
                        network_nodes.flush();
                    });
                }

                // Re-index and re-render any edge that lost a waypoint
                Object.keys(rebuild_edges).forEach(function (eid) {
                    rebuildEdgeChain(eid);
                });

                $("#map-saveDataButton").show();
            }
        options['manipulation']['addEdge'] = function (data, callback) {
                // Because we deal with multiple edges, do not use the default callback
                callback(null);

                // Do not allow linking to the same node
                if(data.to == data.from) {
                    return;
                }
                // Do not allow linking to the mid point nodes
                if(isNaN(data.to) && data.to.endsWith("_mid")) {
                    return;
                }
                if(isNaN(data.from) && data.from.endsWith("_mid")) {
                    return;
                }

                var edgeid = "new" + newcount++;

                edgeNodesUpdate(edgeid, data.from, data.to, -1, -1);
                const mid_pos = getMidPos(edgeid, data.from, data.to);

                // Default to using the center point
                var mid_x = mid_pos.x;
                var mid_y = mid_pos.y;

                var mid = {id: edgeid + "_mid", shape: "dot", size: 3, x: mid_x, y: mid_y, label: ''};

                var edge1 = structuredClone(newedgeconf);
                edge1.id = edgeid + "_from";
                edge1.from = data.from;
                edge1.to = edgeid + "_mid";

                var edge2 = structuredClone(newedgeconf);
                edge2.id = edgeid + "_to";
                edge2.from = data.to;
                edge2.to = edgeid + "_mid";

                var edgedata = {id: edgeid, mid: mid, edge1: edge1, edge2: edge2, add: true}

                edgeEdit(edgedata);
            }
        options['manipulation']['editEdge'] = { editWithoutDrag: editExistingEdge };
        options['manipulation']['deleteEdge'] = function (data, callback) {
            callback(null);
            $.each( data.edges, function( edge_idx, edgeid ) {
                edgeid = edgeid.split("_")[0];
                deleteEdge(edgeid);
            });
        };

        network = new vis.Network(container, {nodes: network_nodes, edges: network_edges, stabilize: true}, options);

        // width/height might be % get values in pixels
        network_height = $($(container).children(".vis-network")[0]).height();
        network_width = $($(container).children(".vis-network")[0]).width();
        var centreY = Math.round(network_height / 2);
        var centreX = Math.round(network_width / 2);
        network.moveTo({position: {x: centreX, y: centreY}, scale: 1});

        setCustomMapBackground('custom-map', bgtype, bgdata);

        network.on('doubleClick', function (properties) {
            edge_id = null;
            if (properties.nodes.length > 0) {
                node_id = properties.nodes[0];
                node = network_nodes.get(node_id);
                $("#nodeModalLabel").text('{{ __('map.custom.edit.node.edit') }}');
                $(".single-node").show();
                checkEditNode(node);
            } else if (properties.edges.length > 0) {
                edge_id = properties.edges[0].split("_")[0];
                edge = network_edges.get(edge_id + "_to");
                editExistingEdge(edge, null);
            }
        });

        network.on('dragEnd', function (data) {
            if(data.edges.length > 0 || data.nodes.length > 0) {
                // Make sure a node is not dragged outside the canvas
                nodepos = network.getPositions(data.nodes);
                legendMoved = false;
                $.each( nodepos, function( nodeid, node ) {
                    if ( nodeid.startsWith("legend_") ) {
                        // Only move the legend once
                        if (legendMoved) {
                            return;
                        }
                        legendMoved = true;

                        // Get the current node config
                        cur_node = network_nodes.get(nodeid);

                        // Move the header relative to the node movement
                        legend.x = legend.x + node.x - cur_node.x;
                        legend.y = legend.y + node.y - cur_node.y;

                        // Make sure the top of the legend is still on the map
                        fixNodePos("legend", legend);

                        redrawLegend();

                        // Make sure the bottom of the legend is still on the map
                        legendEndNode = network_nodes.get("legend_" + (legend.steps - 1))
                        moveUp = legendEndNode.y - network_height + {{ $vmargin }};
                        if (moveUp > 0) {
                            legend.y -= moveUp;
                            redrawLegend();
                        }

                        return;
                    }
                    let move = fixNodePos(nodeid, node);
                    if ( move ) {
                        network.moveNode(nodeid, node.x, node.y);
                    }
                    node.id = nodeid;
                    network_nodes.update(node);
                });
                $("#map-saveDataButton").show();
                $("#map-renderButton").show();
            }
        });
        $("#map-renderButton").hide();
    }

    function editMapSettings() {
        $('#mapModal').modal({backdrop: 'static', keyboard: false}, 'show');
    }

    var newedgeconf = @json($newedge_conf);
    var newnodeconf = @json($newnode_conf);
    var newcount = 1;

    // Make sure the new edge config has an appropriate label value
    if (!("label" in newedgeconf)) {
        newedgeconf.label = "xx%";
    } else if (newedgeconf.label == null) {
        newedgeconf.label = "xx%";
    } else if (typeof(newedgeconf.label) == 'boolean') {
        newedgeconf.label = newedgeconf.label ? "xx%" : "";
    }

    var edge_port_map = {};

    function mapList() {
        if($("#map-saveDataButton").is(":visible")) {
            $('#mapListModal').modal({backdrop: 'static', keyboard: false}, 'show');
        } else {
            viewList();
        }
    }

    function viewList() {
        window.location.href = "{{ route('maps.custom.index') }}";
    }

    // Build a vis.js arrows config honouring the per-edge arrow style (ARROWSTYLE)
    function buildArrows(reverse, arrow_type, arrow_scale) {
        var type = arrow_type || 'arrow';
        var scale = parseFloat(arrow_scale) || 0.6;
        var head = type === 'none' ? {enabled: false} : {enabled: true, scaleFactor: scale, type: type};
        return reverse ? {from: head, to: {enabled: false}} : {to: head, from: {enabled: false}};
    }

    // Extract the {type, scale} from a vis.js arrows object (whichever end is enabled)
    function arrowProps(arrows) {
        var head = (arrows && arrows.to && arrows.to.enabled) ? arrows.to
                 : (arrows && arrows.from && arrows.from.enabled) ? arrows.from
                 : null;
        if (!head) {
            return {type: 'none', scale: 0.6};
        }
        return {type: head.type || 'arrow', scale: head.scaleFactor || 0.6};
    }

    function swapArrows(reverse) {
        network_edges.forEach((edge) => {
            var props = arrowProps(edge.arrows);
            edge.arrows = buildArrows(Boolean(reverse), props.type, props.scale);
            network_edges.update(edge);
        });
        network_edges.flush();
    }

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

    function toggleLegend() {
        var width = $("#mapwidth").val();
        var mapwdith = 100;
        if (!isNaN(width)) {
            mapwidth = width;
        } else if (width.includes("px")) {
            mapwidth = width.replace("px", "");
        } else if (width.includes("%")) {
            mapwidth = window.innerWidth * width.replace("%", "") / 100;
        }

        // Update the x and y coordinates
        if (legend.x < 0) {
            legend.x = mapwidth - 50;
            legend.y = 100;
        } else {
            legend.x = -1;
            legend.y = -1;
        }
        redrawLegend();
    }

    function redrawLegend() {
        // Save list of selected nodes because we are going to remove and re-add the legend
        selectedNodes = network.selectionHandler.getSelectedNodes().map((n) => {return n.id});

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
                let this_colour = 'black';
                if(legend.colours) {
                    this_colour = legend.colours['-1'];
                }
                let legend_invalid = {id: "legend_invalid", label: "???", title: "Link is down or link speed is not defined", shape: "box", borderWidth: 0, x: legend.x, y: y_pos, font: {face: 'courier new', size: legend.font_size, color: "white"}, color: {background: this_colour}};
                y_pos += y_inc;
                network_nodes.add(legend_invalid);
            }

            if(legend.colours) {
                let i = 0;
                Object.keys(legend.colours).sort((a,b) => parseInt(a) > parseInt(b)).forEach((pct_key) => {
                    let this_pct = parseFloat(pct_key);
                    if(!isNaN(this_pct) && this_pct >= 0.0) {
                        let legend_step = {id: "legend_" + i.toString(), label: this_pct.toString().padStart(3, " ") + "%", shape: "box", borderWidth: 0, x: legend.x, y: y_pos, font: {face: 'courier new', size: legend.font_size, color: "black"}, color: {background: legend.colours[pct_key]}};
                        network_nodes.add(legend_step);
                        y_pos += y_inc;
                        i++;
                    }
                });
            } else {
                let pct_step;
                if (Boolean(legend.hide_overspeed)) {
                    pct_step = 100.0 / (legend.steps - 1);
                } else {
                    pct_step = 150.0 / (legend.steps - 1);
                }
                for (let i=0; i < legend.steps; i++) {
                    let this_pct = Math.round(pct_step * i);
                    let legend_step = {id: "legend_" + i.toString(), label: this_pct.toString().padStart(3, " ") + "%", shape: "box", borderWidth: 0, x: legend.x, y: y_pos, font: {face: 'courier new', size:
 legend.font_size, color: "black"}, color: {background: legendPctColour(this_pct)}};
                    network_nodes.add(legend_step);
                    y_pos += y_inc;
                }
            }
            network_nodes.flush();
        }

        // Re-select nodes if multiple nodes are selected
        if (selectedNodes.length > 1) {
            network.selectNodes(selectedNodes);
        }
    }

    function editMapSuccess(data) {
        $("#title").text(data.name);
        $("#savemap-alert").attr("class", "col-sm-12");
        $("#savemap-alert").text("");

        edge_sep = data.edge_separation;
        if(reverse_arrows != parseInt(data.reverse_arrows)) {
            swapArrows(Boolean(parseInt(data.reverse_arrows)));
        }
        reverse_arrows = parseInt(data.reverse_arrows);

        // update dimensions
        network_options.width = data.width;
        network_options.height = data.height;
        $("#custom-map-bg-geo-map").css('width', data.width).css('height', data.height);

        // Re-create the network because network.setSize() blanks out the map
        CreateNetwork();

        $('#mapModal').modal('hide');
    }

    function editMapCancel() {
        mapSettingsReset();
        $('#mapModal').modal('hide');
        $("#savemap-alert").attr("class", "col-sm-12");
        $("#savemap-alert").text("");
    }

    function saveMapData() {
        $("#map-saveDataButton").attr('disabled', 'disabled');
        var nodes = {};
        var edges = {};

        $.each(network_nodes.get(), function (node_idx, node) {
            if(node.id.startsWith("legend_")) {
                return;
            } else if(node.id.endsWith("_mid")) {
                edgeid = node.id.split("_")[0];
                edge1 = network_edges.get(edgeid + "_from");
                edge2 = network_edges.get(edgeid + "_to");
                var arrow_props = arrowProps(edge1.arrows);
                edges[edgeid] = {id: edgeid, text_colour: edge1.font.color, text_size: edge1.font.size, text_face: edge1.font.face, text_align: edge1.font.align, from: edge1.from, to: edge2.from, showpct: (edge1.label != null && edge1.label.includes("xx%")), showbps: (edge1.label != null && edge1.label.includes("bps")), label: (node.label || ''), fixed_width: (edge1.width || null), port_id: edge1.title, style: edge1.smooth.type, arrow_type: arrow_props.type, arrow_scale: arrow_props.scale, mid_x: node.x, mid_y: node.y, waypoints: collectEdgeWaypoints(edgeid), reverse: (edgeid in edge_port_map ? edge_port_map[edgeid].reverse : false)};
            } else {
                if(node.icon.code) {
                    node.icon = node.icon.code.charCodeAt(0).toString(16);
                } else {
                    node.icon = null;
                }
                if(node.image && "unselected" in node.image) {
                    if(node.image.unselected.indexOf(custom_image_base) == 0) {
                        node.image.unselected = node.image.unselected.replace(custom_image_base, "");
                        node.nodeimage = null;
                    } else if(node.image.unselected.indexOf(nodeimage_base) == 0) {
                        node.nodeimage = node.image.unselected.replace(nodeimage_base, "");
                        node.image = undefined;
                    } else {
                        node.image = undefined;
                        node.nodeimage = null;
                    }
                }
                nodes[node.id] = node;
            }
        });

        $.ajax({
            url: '{{ route('maps.custom.data.save', ['map' => $map_id]) }}',
            data: JSON.stringify({
                newnodeconf: newnodeconf,
                newedgeconf: newedgeconf,
                nodes: nodes,
                edges: edges,
                legend_x: legend.x,
                legend_y: legend.y,
                legend_steps: legend.steps,
                legend_font_size: legend.font_size,
                legend_hide_invalid: legend.hide_invalid,
                legend_hide_overspeed: legend.hide_overspeed,
                legend_colours: legend.colours,
            }),
            contentType: "application/json",
            dataType: 'json',
            type: 'POST'
        }).done(function (data, status, resp) {
            $("#map-saveDataButton").hide();
            $("#alert-row").hide();

            // Re-read the map from the DB in case any items were modified
            refreshMap();
        }).fail(function (resp, status, error) {
            var data = resp.responseJSON;
            if (data['message']) {
                let alert_content = $("#alert");
                alert_content.text(data['message']);
                alert_content.attr("class", "col-sm-12 alert alert-danger");
            } else {
                let alert_content = $("#alert");
                alert_content.text('{{ __('map.custom.edit.map.save_error', ['code' => '?']) }}'.replace('?', resp.status));
                alert_content.attr("class", "col-sm-12 alert alert-danger");
            }
        }).always(function (resp, status, error) {
            $("#map-saveDataButton").removeAttr('disabled');
        });
    }

    function editMapBackground() {
        $('#bgModal').modal('show');
    }

    function checkEditNode(data) {
        // If we have an ID that is non numeric, we can check node type further
        if(data.id && isNaN(data.id)) {
            // Editing a mid point node triggers editing the edge
            if(data.id.endsWith("_mid")) {
                edge = network_edges.get((data.id.split("_")[0]) + "_to");
                editExistingEdge(edge, null);
                return;
            }

            // Legend nodes cannot be edited
            if (data.id.startsWith("legend_") ) {
                $('#mapLegendModal').modal({backdrop: 'static', keyboard: false}, 'show');
                return;
            }
        }
        nodeEdit(data);
    }

    function edgeLabel(show_pct, show_bps, default_val) {
        var label = '';
        if(show_pct) {
            label = 'xx%';
        }
        if(show_bps) {
            if(Boolean(label.length)) {
                label += "\n";
            }
            label += 'xx bps';
        }
        if(Boolean(label.length)) {
            return label;
        }
        return default_val;
    }

    function editExistingEdge (edge, callback) {
        if(callback) {
            callback(null);
        }
        var edgeinfo = edge.id.split("_");

        if(edgeinfo[1] == "to") {
            edge1 = network_edges.get(edgeinfo[0] + "_from");
            edge2 = network_edges.get(edge.id);
        } else {
            edge1 = network_edges.get(edge.id);
            edge2 = network_edges.get(edgeinfo[0] + "_to");
        }
        var mid = network_nodes.get(edgeinfo[0] + "_mid");

        var edgedata = {id: edgeinfo[0], mid: mid, edge1: edge1, edge2: edge2}

        edgeEdit(edgedata);
    }

    // VIA waypoints: gather the waypoint node positions for an edge, keyed by half (from/to).
    // Scans all nodes (rather than counting up from 0) so it is robust to gaps after a removal.
    function collectEdgeWaypoints(edgeid) {
        var fromArr = [];
        var toArr = [];
        network_nodes.get().forEach(function (n) {
            if (typeof n.id !== 'string') {
                return;
            }
            var m = n.id.match(/^(.+)_w([ft])_(\d+)$/);
            if (m && m[1] === edgeid) {
                (m[2] === 'f' ? fromArr : toArr).push({i: parseInt(m[3]), x: Math.round(n.x), y: Math.round(n.y)});
            }
        });
        fromArr.sort((a, b) => a.i - b.i);
        toArr.sort((a, b) => a.i - b.i);
        var wp = {
            from: fromArr.map((p) => [p.x, p.y]),
            to: toArr.map((p) => [p.x, p.y]),
        };
        return (wp.from.length === 0 && wp.to.length === 0) ? null : wp;
    }

    // Remove the waypoint nodes and pass-through segments for an edge (leaves the canonical _from/_to/_mid).
    function removeEdgeWaypointArtifacts(edgeid) {
        network_edges.getIds().forEach(function (id) {
            if (typeof id === 'string' && (id.indexOf(edgeid + "_from_seg_") === 0 || id.indexOf(edgeid + "_to_seg_") === 0)) {
                network_edges.remove(id);
            }
        });
        network_nodes.getIds().forEach(function (id) {
            if (typeof id === 'string' && (id.indexOf(edgeid + "_wf_") === 0 || id.indexOf(edgeid + "_wt_") === 0)) {
                network_nodes.remove(id);
            }
        });
    }

    // Build the waypoint dot nodes and pass-through segments for one half of an edge.
    // Returns {nodes, segments, firstTo} where firstTo is the id the canonical _from/_to segment should point to.
    function buildHalfExtras(edgeid, edge, half, baseEdge) {
        var hk = half[0];
        var midId = edgeid + "_mid";
        var wps = (edge.waypoints && edge.waypoints[half]) ? edge.waypoints[half] : [];
        if (wps.length === 0) {
            return {nodes: [], segments: [], firstTo: midId};
        }

        var nodes = [];
        var segments = [];
        for (var i = 0; i < wps.length; i++) {
            nodes.push({id: edgeid + "_w" + hk + "_" + i, shape: "dot", size: 3, x: wps[i][0], y: wps[i][1], label: ''});
            var fromId = edgeid + "_w" + hk + "_" + i;
            var toId = (i + 1 < wps.length) ? (edgeid + "_w" + hk + "_" + (i + 1)) : midId;
            segments.push({
                id: edgeid + "_" + half + "_seg_" + i,
                from: fromId,
                to: toId,
                arrows: {to: {enabled: false}, from: {enabled: false}},
                color: baseEdge.color,
                width: baseEdge.width,
                smooth: baseEdge.smooth,
                font: baseEdge.font,
                title: baseEdge.title,
                arrowStrikethrough: false,
            });
        }
        return {nodes: nodes, segments: segments, firstTo: edgeid + "_w" + hk + "_0"};
    }

    // Rebuild an edge's waypoint chain from the current network state (used after add/remove without a server round-trip).
    function rebuildEdgeChain(edgeid) {
        var edge1 = network_edges.get(edgeid + "_from");
        var edge2 = network_edges.get(edgeid + "_to");
        if (!edge1 || !edge2) {
            return;
        }
        var wp = collectEdgeWaypoints(edgeid) || {from: [], to: []};
        removeEdgeWaypointArtifacts(edgeid);
        var synthetic = {waypoints: wp};
        var fromExtras = buildHalfExtras(edgeid, synthetic, 'from', edge1);
        var toExtras = buildHalfExtras(edgeid, synthetic, 'to', edge2);
        edge1.to = fromExtras.firstTo;
        edge2.to = toExtras.firstTo;
        network_nodes.update(fromExtras.nodes.concat(toExtras.nodes));
        network_edges.update([edge1, edge2].concat(fromExtras.segments).concat(toExtras.segments));
        network_nodes.flush();
        network_edges.flush();
    }

    // Add a waypoint to one half of an edge, positioned halfway between the half's last point and the mid node.
    function addWaypointToEdge(edgeid, half) {
        var canonical = network_edges.get(edgeid + "_" + half);
        var mid = network_nodes.get(edgeid + "_mid");
        if (!canonical || !mid) {
            return;
        }
        var wp = collectEdgeWaypoints(edgeid) || {from: [], to: []};
        var lastPt;
        if (wp[half].length > 0) {
            lastPt = {x: wp[half][wp[half].length - 1][0], y: wp[half][wp[half].length - 1][1]};
        } else {
            lastPt = network.getPositions([canonical.from])[canonical.from];
        }
        var newPt = [Math.round((lastPt.x + mid.x) / 2), Math.round((lastPt.y + mid.y) / 2)];
        var hk = half[0];
        network_nodes.add({id: edgeid + "_w" + hk + "_" + wp[half].length, shape: "dot", size: 3, x: newPt[0], y: newPt[1], label: ''});
        network_nodes.flush();
        rebuildEdgeChain(edgeid);
        $("#map-saveDataButton").show();
        $("#map-renderButton").show();
    }

    function deleteEdge(edgeid) {
        const edge1 = network_edges.get(edgeid + "_from");
        const edge2 = network_edges.get(edgeid + "_to");
        var nm_id = edge1.from < edge2.from ? edge1.from + '.' + edge2.from : edge2.from + '.' + edge1.from;
        edgeNodesRemove(nm_id, edgeid);
        removeEdgeWaypointArtifacts(edgeid);
        network_edges.remove(edgeid + "_to");
        network_edges.remove(edgeid + "_from");
        network_edges.flush();
        network_nodes.remove(edgeid + "_mid");
        network_nodes.flush();
        $("#map-saveDataButton").show();
    }

    function refreshMap() {
        edge_nodes_map = [];
        $.get( '{{ route('maps.custom.data', ['map' => $map_id]) }}')
            .done(function( data ) {
                // Add/update nodes
                $.each( data.nodes, function( nodeid, node) {
                    var node_cfg = {};
                    node_cfg.id = nodeid;
                    node_cfg.device_id = node.device_id;
                    node_cfg.linked_map_id = node.linked_map_id;
                    if(node.device_id) {
                        node_device_map[nodeid] = {device_id: node.device_id, device_name: node.device_name, device_image: node.device_image};
                        node_cfg.title = "Device " + node.device_id;
                    } else if(node.linked_map_id) {
                        node_cfg.title = "Link to map " + node.linked_map_id;
                    } else {
                        node_cfg.title = null;
                    }
                    node_cfg.label = node.label;
                    node_cfg.shape = node.style;
                    node_cfg.borderWidth = node.border_width;
                    node_cfg.x = node.x_pos;
                    node_cfg.y = node.y_pos;
                    node_cfg.font = {face: node.text_face, size: node.text_size, color: node.text_colour, background: '#FFFFFF'};
                    node_cfg.size = node.size;
                    node_cfg.color = {background: node.colour_bg, border: node.colour_bdr};
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
                            // If we do not get a valid image from the database, use defaults
                            node_cfg.shape = newnodeconf.shape;
                            node_cfg.icon = newnodeconf.icon;
                            node_cfg.image = newnodeconf.image || undefined;
                        }
                    } else {
                        node_cfg.image = undefined;
                    }
                    if(! ["ellipse", "circle", "database", "box", "text"].includes(node.style)) {
                        node_cfg.font.background = "#FFFFFF";
                    }

                    if (network_nodes.get(nodeid)) {
                        network_nodes.update(node_cfg);
                    } else {
                        network_nodes.add([node_cfg]);
                    }
                });

                $.each( data.edges, function( edgeid, edge) {
                    edgeNodesUpdate(edgeid, edge.custom_map_node1_id, edge.custom_map_node2_id, -1, -1);

                    var mid_x = edge.mid_x;
                    var mid_y = edge.mid_y;

                    var mid = {id: edgeid + "_mid", shape: "dot", size: 0, x: mid_x, y: mid_y, label: edge.label};
                    mid.size = 3;

                    var arrows = buildArrows(Boolean(reverse_arrows), edge.arrow_type, edge.arrow_scale);

                    var edge1 = {id: edgeid + "_from", from: edge.custom_map_node1_id, to: edgeid + "_mid", arrows: arrows, font: {face: edge.text_face, size: edge.text_size, color: edge.text_colour, align: edge.text_align, background: '#FFFFFF'}, smooth: {type: edge.style}, arrowStrikethrough: false};
                    var edge2 = {id: edgeid + "_to", from: edge.custom_map_node2_id, to: edgeid + "_mid", arrows: arrows, font: {face: edge.text_face, size: edge.text_size, color: edge.text_colour, align: edge.text_align, background: '#FFFFFF'}, smooth: {type: edge.style}, arrowStrikethrough: false};
                    if(edge.fixed_width) {
                        edge1.width = edge2.width = parseFloat(edge.fixed_width) || null;
                    }

                    // Special case for curved lines
                    if(edge2.smooth.type == "curvedCW") {
                        edge2.smooth.type = "curvedCCW";
                    } else if (edge2.smooth.type == "curvedCCW") {
                        edge2.smooth.type = "curvedCW";
                    }
                    if(edge.port_id) {
                        edge_port_map[edgeid] = {port_id: edge.port_id, port_name: edge.port_name, reverse: edge.reverse};
                        edge1.title = edge2.title = edge.port_id;
                    } else {
                        edge1.title = edge2.title = '';
                    }
                    edge1.label = edge2.label = edgeLabel(edge.showpct, edge.showbps, '');

                    // VIA waypoints: rebuild any waypoint chain from the (authoritative) server data
                    removeEdgeWaypointArtifacts(edgeid);
                    var fromExtras = buildHalfExtras(edgeid, edge, 'from', edge1);
                    var toExtras = buildHalfExtras(edgeid, edge, 'to', edge2);
                    edge1.to = fromExtras.firstTo;
                    edge2.to = toExtras.firstTo;
                    var wpNodes = fromExtras.nodes.concat(toExtras.nodes);
                    var segs = fromExtras.segments.concat(toExtras.segments);

                    network_nodes.update([mid].concat(wpNodes));
                    network_edges.update([edge1, edge2].concat(segs));
                });

                // Remove any nodes that are not in the database, includes edges
                $.each( network_nodes.getIds(), function( node_idx, nodeid ) {
                    if(nodeid.endsWith('_mid')) {
                        edgeid = nodeid.split("_")[0];
                        if(! (edgeid in data.edges)) {
                            removeEdgeWaypointArtifacts(edgeid);
                            network_nodes.remove(edgeid + "_mid");
                            network_edges.remove(edgeid + "_to");
                            network_edges.remove(edgeid + "_from");
                        }
                    } else if(typeof nodeid === 'string' && /_w[ft]_\d+$/.test(nodeid)) {
                        // waypoint node - cleaned up together with its edge, leave it here
                    } else {
                        if(! (nodeid in data.nodes)) {
                            network_nodes.remove(nodeid);
                        }
                    }
                });

                // Add the legend back to the map
                redrawLegend();

                // Flush in order to make sure nodes exist for edges to connect to
                network_nodes.flush();
                network_edges.flush();
                $("#alert").empty();
                $("#alert-row").hide();
            });

        // Initialise map if it does not exist
        if (! network) {
            CreateNetwork();
        }
    }

    function observeEditMode() {
        const targetNode = document.getElementsByClassName("vis-manipulation")[0];

        // Start observing the target node for configured mutations
        new MutationObserver((mutationList, observer) => {
            for (const mutation of mutationList) {
                if (mutation.addedNodes.length) {
                    if(Array.from(mutation.addedNodes).some(({classList}) => classList.contains("vis-back"))) {
                        document.getElementById("custom-map").classList.add("tw:cursor-crosshair")
                    }
                } else if (mutation.removedNodes.length) {
                    if(Array.from(mutation.removedNodes).some(({classList}) => classList.contains("vis-back"))) {
                        document.getElementById("custom-map").classList.remove("tw:cursor-crosshair")
                    }
                }
            }
        }).observe(targetNode, {attributes: false, childList: true, subtree: false});
    }

    $(document).ready(function () {
        refreshMap();

        // watch for addNode/editNode
        observeEditMode();
   });
</script>
@endsection

