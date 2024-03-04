@extends('layouts.librenmsv1')

@section('title', __('map.custom.title.edit'))

@section('content')

@include('map.custom-background-modal')
@include('map.custom-node-modal')
@include('map.custom-edge-modal')
@include('map.custom-map-modal')
@include('map.custom-map-list-modal')

<div class="container-fluid">
  <div class="row" id="control-row">
    <div class="col-md-5">
      <button type=button value="mapedit" id="map-editButton" class="btn btn-primary" onclick="editMapSettings();">{{ __('map.custom.edit.map.edit') }}</button>
      <button type=button value="mapbg" id="map-bgButton" class="btn btn-primary" onclick="editMapBackground();">{{ __('map.custom.edit.bg.title') }}</button>
      <button type=button value="editnodedefaults" id="map-nodeDefaultsButton" class="btn btn-primary" onclick="editNodeDefaults();">{{ __('map.custom.edit.node.edit_defaults') }}</button>
      <button type=button value="editedgedefaults" id="map-edgeDefaultsButton" class="btn btn-primary" onclick="editEdgeDefaults();">{{ __('map.custom.edit.edge.edit_defaults') }}</button>
    </div>
    <div class="col-md-2">
      <center>
        <h4 id="title">{{ $name }}</h4>
      </center>
    </div>
    <div class="col-md-5 text-right">
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
    var network;
    var network_height;
    var network_width;
    var network_nodes = new vis.DataSet({queue: {delay: 100}});
    var network_edges = new vis.DataSet({queue: {delay: 100}});
    var edge_nodes_map = [];
    var node_device_map = {};
    var custom_image_base = "{{ $base_url }}images/custommap/icons/";

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
        if ( node_align && !nodeid.endsWith("_mid")) {
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
        var options = {!! json_encode($map_conf) !!};

        // Set up the triggers for adding and editing map items
        options['manipulation']['addNode'] = function (data, callback) {
                callback(null);
                $("#nodeModalLabel").text('{{ __('map.custom.edit.node.add') }}');
                var node = structuredClone(newnodeconf);
                node.id = "new" + newcount++;
                node.label = "New Node";
                node.x = node_align ? Math.round(data.x / node_align) * node_align : data.x;
                node.y = node_align ? Math.round(data.y / node_align) * node_align : data.y;
                node.add = true;
                $(".single-node").show();
                editNode(node, editNodeSave);
            }
        options['manipulation']['editNode'] = function (data, callback) {
                callback(null);
                $("#nodeModalLabel").text('{{ __('map.custom.edit.node.edit') }}');
                $(".single-node").show();
                editNode(data, editNodeSave);
            }
        options['manipulation']['deleteNode'] = function (data, callback) {
                callback(null);
                $.each( data.edges, function( edge_idx, edgeid ) {
                    edgeid = edgeid.split("_")[0];
                    deleteEdge(edgeid);
                });
                $.each( data.nodes, function( node_idx, nodeid ) {
                    network_nodes.remove(nodeid);
                    network_nodes.flush();
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

                $("#edgeModalLabel").text('{{ __('map.custom.edit.edge.add') }}');
                editEdge(edgedata, editEdgeSave);
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
                node_id = properties.nodes[0];
                node = network_nodes.get(node_id);
                $("#nodeModalLabel").text('{{ __('map.custom.edit.node.edit') }}');
                $(".single-node").show();
                editNode(node, editNodeSave);
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
                $.each( nodepos, function( nodeid, node ) {
                    if ( nodeid.startsWith("legend_") ) {
                        // Make sure the moved node is still on the map
                        fixNodePos(nodeid, node);

                        // Get the current node config
                        cur_node = network_nodes.get(nodeid);

                        // Move the header relative to the node movement
                        legend.x = legend.x + node.x - cur_node.x;
                        legend.y = legend.y + node.y - cur_node.y;

                        redrawLegend();
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
    var port_search_device_id_1 = 0;
    var port_search_device_id_2 = 0;

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

    function swapArrows(reverse) {
        var arrows;
        if (reverse) {
            arrows = {from: {enabled: true, scaleFactor: 0.6}, to: {enabled: false}};
        } else {
            arrows = {to: {enabled: true, scaleFactor: 0.6}, from: {enabled: false}};
        }
        network_edges.forEach((edge) => {
            edge.arrows = arrows;
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

    function editMapSuccess(data) {
        $("#title").text(data.name);
        $("#savemap-alert").attr("class", "col-sm-12");
        $("#savemap-alert").text("");

        edge_sep = data.edge_separation;
        if(reverse_arrows != parseInt(data.reverse_arrows)) {
            swapArrows(Boolean(parseInt(data.reverse_arrows)));
        }
        reverse_arrows = parseInt(data.reverse_arrows);
        redrawLegend();

        // Re-create the network because network.setSize() blanks out the map
        CreateNetwork();

        editMapCancel();
    }

    function editMapCancel() {
        $('#mapModal').modal('hide');
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
                edges[edgeid] = {id: edgeid, text_colour: edge1.font.color, text_size: edge1.font.size, text_face: edge1.font.face, from: edge1.from, to: edge2.from, showpct: (edge1.label != null && edge1.label.includes("xx%")), showbps: (edge1.label != null && edge1.label.includes("bps")), label: (node.label || ''), port_id: edge1.title, style: edge1.smooth.type, mid_x: node.x, mid_y: node.y, reverse: (edgeid in edge_port_map ? edge_port_map[edgeid].reverse : false)};
            } else {
                if(node.icon.code) {
                    node.icon = node.icon.code.charCodeAt(0).toString(16);
                } else {
                    node.icon = null;
                }
                if("unselected" in node.image) {
                    if(node.image.unselected.indexOf(custom_image_base) == 0) {
                        node.image.unselected = node.image.unselected.replace(custom_image_base, "");
                    } else {
                        node.image = {};
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
        $("#mapBackgroundCancel").hide();
        $("#mapBackgroundSelect").val(null);

        if($("#custom-map").children()[0].canvas.style.backgroundImage) {
            $("#mapBackgroundClearRow").show();
        } else {
            $("#mapBackgroundClearRow").hide();
        }
        $('#bgModal').modal({backdrop: 'static', keyboard: false}, 'show');
    }

    function nodeStyleChange() {
        var nodestyle = $("#nodestyle").val();
        if(nodestyle == 'icon') {
            $("#nodeIconRow").show();
        } else {
            $("#nodeIconRow").hide();
        }
        if(nodestyle == 'image' || nodestyle == 'circularImage') {
            $("#nodeImageRow").show();
        } else {
            $("#nodeImageRow").hide();
        }
    }

    function nodeDeviceSelect(e) {
        var id = e.params.data.id;
        var name = e.params.data.text;
        $("#device_id").val(id);
        $("#device_name").text(name);
        $("#nodelabel").val(name.split(".")[0].split(" ")[0]);
        $("#device_image").val(e.params.data.icon);
        $("#nodeDeviceSearchRow").hide();
        $("#nodeMapLinkRow").hide();
        $("#deviceiconimage").show();
        $("#nodeDeviceRow").show();
    }

    function nodeDeviceClear() {
        $("#devicesearch").val('');
        $("#devicesearch").trigger('change');
        $("#device_id").val("");
        $("#device_name").text("");
        $("#device_image").val("");
        $("#nodeDeviceRow").hide();
        $("#deviceiconimage").hide();
        $("#nodeDeviceSearchRow").show();
        $("#nodeMapLinkRow").show();

        // Reset device style if we were using the device image
        if(($("#nodestyle").val() == "image" || $("#nodestyle").val() == "circularImage") && !$("#nodeimage").val()){
            $("#nodestyle").val(newnodeconf.shape);
            $("#nodeImageRow").hide();
            setNodeImage();
        }
    }

    function nodeMapLinkChange() {
        if($("#maplink").val()) {
            $("#nodeDeviceSearchRow").hide();
        } else {
            $("#nodeDeviceSearchRow").show();
        }
    }

    function setNodeImage() {
        // If the selected option is not visible, select the top option
        if($("#nodeimage option:selected").css('display') == 'none') {
            $("#nodeimage").val($("#nodeimage option:eq(1)").val());
        }
        // Set the image preview src
        if($("#nodeimage").val()) {
            $("#nodeimagepreview").attr("src", custom_image_base + $("#nodeimage").val());
        } else {
            $("#nodeimagepreview").attr("src", $("#device_image").val());
        }
    }

    function setNodeIcon() {
        var newcode = $("#nodeicon").val();
        $("#nodeiconpreview").text(String.fromCharCode(parseInt(newcode, 16)));
    }

    function editNodeDefaults() {
        $("#nodeModalLabel").text('{{ __('map.custom.edit.node.defaults_title') }}');
        $(".single-node").hide();
        var node = structuredClone(newnodeconf);
        editNode(node, editNodeDefaultsSave);
    }

    function editNodeDefaultsSave() {
        newnodeconf.shape = $("#nodestyle").val();
        newnodeconf.font.face = $("#nodetextface").val();
        newnodeconf.font.size = $("#nodetextsize").val();
        newnodeconf.font.color = $("#nodetextcolour").val();
        newnodeconf.color.background = $("#nodecolourbg").val();
        newnodeconf.color.border = $("#nodecolourbdr").val();
        if(newnodeconf.shape == "icon") {
            newnodeconf.icon = {face: 'FontAwesome', code: String.fromCharCode(parseInt($("#nodeicon").val(), 16)), size: $("#nodesize").val(), color: newnodeconf.color.border};
        } else {
            newnodeconf.icon = {};
        }
        if(newnodeconf.shape == "image" || newnodeconf.shape == "circularImage") {
            newnodeconf.image = {unselected: custom_image_base + $("#nodeimage").val()};
        } else {
            delete newnodeconf.image;
        }
        $("#map-saveDataButton").show();
    }

    function checkColourReset(itemColour, defaultColour, resetControlId) {
        if(!itemColour || itemColour.toLowerCase() == defaultColour.toLowerCase()) {
            $("#" + resetControlId).attr('disabled','disabled');
        } else {
            $("#" + resetControlId).removeAttr('disabled');
        }
    }

    function editNode(data, callback) {
        $("#devicesearch").val('');
        $("#devicesearch").trigger('change');
        if(data.id && isNaN(data.id) && data.id.endsWith("_mid")) {
            edge = network_edges.get((data.id.split("_")[0]) + "_to");
            editExistingEdge(edge, null);
            return;
        }
        if(data.id in node_device_map) {
            // Nodes is linked to a device
            $("#device_id").val(node_device_map[data.id].device_id);
            $("#device_name").text(node_device_map[data.id].device_name);
            // Hide device selection row
            $("#nodeDeviceSearchRow").hide();
            $("#nodeMapLinkRow").hide();
            // Show device image as an option
            $("#deviceiconimage").show();
            $("#device_image").val(node_device_map[data.id].device_image);
        } else {
            // Node is not linked to a device
            $("#device_id").val("");
            $("#device_name").text("");
            // Hide the selected device row
            $("#nodeDeviceRow").hide();
            // Hide device image as an option
            $("#deviceiconimage").hide();
            $("#device_image").val("");
        }
        if(data.title && data.title.toString().startsWith("map:")) {
            // Hide device selection row
            $("#nodeDeviceSearchRow").hide();
            $("#maplink").val(data.title.replace("map:",""));
        }
        $("#nodelabel").val(data.label);
        $("#nodestyle").val(data.shape);
        // Show or hide the image selection if the shape is an image type
        if(data.shape == "image" || data.shape == "circularImage") {
            $("#nodeImageRow").show();
            if(data.image.unselected.indexOf(custom_image_base) == 0) {
                $("#nodeimage").val(data.image.unselected.replace(custom_image_base, ""));
            } else {
                $("#nodeimage").val("");
            }
        } else {
            $("#nodeImageRow").hide();
            $("#nodeimage").val("");
        }
        setNodeImage();
        // Show or hide the icon selection if the shape is icon
        if(data.shape == "icon") {
            $("#nodeicon").val(data.icon.code.charCodeAt(0).toString(16));
            $("#nodeIconRow").show();
        } else {
            $("#nodeIconRow").hide();
        }
        $("#nodesize").val(data.size);
        $("#nodetextface").val(data.font.face);
        $("#nodetextsize").val(data.font.size);
        $("#nodetextcolour").val(data.font.color);
        if(data.color && data.color.background) {
            $("#nodecolourbg").val(data.color.background);
            $("#nodecolourbdr").val(data.color.border);
        } else {
            // The background colour is blank because a device has been selected - start with defaults
            $("#nodecolourbg").val(newnodeconf.color.background);
            $("#nodecolourbdr").val(newnodeconf.color.border);
        }

        checkColourReset(data.font.color, newnodeconf.font.color, "nodecolourtextreset");
        checkColourReset(data.color.background, newnodeconf.color.background, "nodecolourbgreset");
        checkColourReset(data.color.border, newnodeconf.color.border, "nodecolourbdrreset");

        if(data.id) {
            $("#node-saveButton").on("click", {data: data}, callback);
            $("#node-saveButton").show();
            $("#node-saveDefaultsButton").hide();
        } else {
            $("#node-saveButton").hide();
            $("#node-saveDefaultsButton").show();
        }
        $('#nodeModal').modal({backdrop: 'static', keyboard: false}, 'show');
    }

    function editNodeSave(event) {
        node = event.data.data;

        editNodeHide();

        if($("#device_id").val()) {
            node.title = $("#device_id").val();
        } else if($("#maplink").val()) {
            node.title = "map:" + $("#maplink").val();
        } else {
            node.title = '';
        }
        // Update the node with the selected values on success and run the callback
        node.label = $("#nodelabel").val();
        node.shape = $("#nodestyle").val();
        node.font.face = $("#nodetextface").val();
        node.font.size = parseInt($("#nodetextsize").val());
        node.font.color = $("#nodetextcolour").val();
        node.color = {highlight: {}, hover: {}};
        node.color.background = node.color.highlight.background = node.color.hover.background = $("#nodecolourbg").val();
        node.color.border = node.color.highlight.border = node.color.hover.border = $("#nodecolourbdr").val();
        node.size = $("#nodesize").val();
        if(node.shape == "image" || node.shape == "circularImage") {
            if($("#nodeimage").val()) {
                node.image = {unselected: custom_image_base + $("#nodeimage").val()};
            } else {
                node.image = {unselected: $("#device_image").val()};
            }
        } else {
            node.image = {};
        }
        if(node.shape == "icon") {
            node.icon = {face: 'FontAwesome', code: String.fromCharCode(parseInt($("#nodeicon").val(), 16)), size: $("#nodesize").val(), color: node.color.border};
        } else {
            node.icon = {};
        }
        if(node.add) {
            delete node.add;
            network_nodes.add(node);
        } else {
            network_nodes.update(node);
        }

        if(node.id) {
            if($("#device_id").val()) {
                node_device_map[node.id] = {device_id: $("#device_id").val(), device_name: $("#device_name").text(), device_image: $("#device_image").val()}
            } else {
                delete node_device_map[node.id];
            }
        }

        $("#map-saveDataButton").show();
        $("#map-renderButton").show();
    }

    function editNodeCancel(event) {
        editNodeHide();
    }

    function editNodeHide() {
        $("#node-saveButton").off("click");
    }

    function updateEdgePortSearch(node1_id, node2_id, edge_id) {
        node1 = network_nodes.get(node1_id);
        node2 = network_nodes.get(node2_id);

        if(isNaN(node1.title) && isNaN(node2.title)) {
            // Neither node has a device - clear port config
            $("#port_id").val("");
            $("#edgePortRow").hide();
            $("#edgePortReverseRow").hide();
            $("#edgePortSearchRow").hide();
            return;
        }
        if(edge_id in edge_port_map) {
            $("#port_id").val(edge_port_map[edge_id].port_id);
            $("#port_name").text(edge_port_map[edge_id].port_name);
            $("#portreverse").bootstrapSwitch('state', edge_port_map[edge_id].reverse);
            $("#edgePortRow").show();
            $("#edgePortReverseRow").show();
            $("#edgePortSearchRow").hide();
        } else {
            $("#port_id").val("");
            $("#portreverse").bootstrapSwitch('state', false);
            $("#edgePortRow").hide();
            $("#edgePortReverseRow").hide();
            $("#edgePortSearchRow").show();
        }
        port_search_device_id_1 = (node1.id in node_device_map) ? node_device_map[node1.id].device_id : 0;
        port_search_device_id_2 = (node2.id in node_device_map) ? node_device_map[node2.id].device_id : 0;
    }

    function edgePortSelect(e) {
        var id = e.params.data.id;
        var name = e.params.data.text;
        var reverse = e.params.data.device_id != port_search_device_id_1;
        $("#port_id").val(id);
        $("#port_name").text(name);
        $("#portreverse").bootstrapSwitch('state', reverse);

        $("#edgePortSearchRow").hide();
        $("#edgePortRow").show();
        $("#edgePortReverseRow").show();
    }

    function edgePortClear() {
        $("#portsearch").val('');
        $("#portsearch").trigger('change');
        $("#port_id").val("");
        $("#port_name").text("");
        $("#edgePortSearchRow").show();
        $("#edgePortRow").hide();
        $("#edgePortReverseRow").hide();
    }

    function editEdgeDefaults() {
        $("#edgeModalLabel").text('{{ __('map.custom.edit.edge.defaults_title') }}');
        $("#divEdgeFrom").hide();
        $("#divEdgeTo").hide();
        $("#edgePortRow").hide();
        $("#edgePortReverseRow").hide();
        $("#edgePortSearchRow").hide();
        $("#edgeRecenterRow").hide();
        $("#edgelabel").hide();

        $("#edgestyle").val(newedgeconf.smooth.type);
        $("#edgetextface").val(newedgeconf.font.face);
        $("#edgetextsize").val(newedgeconf.font.size);
        $("#edgetextcolour").val(newedgeconf.font.color);
        $("#edgetextshow").bootstrapSwitch('state', (newedgeconf.label.includes('xx%') || newedgeconf.label.includes('true')));
        $("#edgebpsshow").bootstrapSwitch('state', (newedgeconf.label.includes('bps')));
        $('#edgecolourtextreset').attr('disabled', 'disabled');

        $("#edge-saveButton").hide();
        $("#edge-saveDefaultsButton").show();
        $('#edgeModal').modal({backdrop: 'static', keyboard: false}, 'show');
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

    function editEdgeDefaultsSave() {
        editEdgeHide();
        newedgeconf.smooth.type = $("#edgestyle").val();
        newedgeconf.font.face = $("#edgetextface").val();
        newedgeconf.font.size = $("#edgetextsize").val();
        newedgeconf.font.color = $("#edgetextcolour").val();
        newedgeconf.label = edgeLabel($("#edgetextshow").prop('checked'), $("#edgebpsshow").prop('checked'), '');
        $("#map-saveDataButton").show();
    }

    function editEdge(edgedata, callback) {
        $("#portsearch").val('');
        $("#portsearch").trigger('change');
        var nodes = network_nodes.get({
          fields: ['id', 'label'],
          filter: function (item) {
            // We do not want to be able to link to the mid nodes
            return (!item.id.endsWith("_mid"));
          },
        });
        $("#edgefrom").find('option').remove().end();
        $("#edgeto").find('option').remove().end();
        $.each( nodes, function( node_idx, node ) {
            $("#edgefrom").append('<option value="' + node.id + '">' + node.label+ '</option>');
            $("#edgeto").append('<option value="' + node.id + '">' + node.label+ '</option>');
        });
        $("#edgefrom").val(edgedata.edge1.from);
        $("#edgeto").val(edgedata.edge2.from);

        updateEdgePortSearch($("#edgefrom").val(), $("#edgeto").val(), edgedata.id);
        checkColourReset(edgedata.edge1.font.color, newedgeconf.font.color, "edgecolourtextreset");

        $("#edgestyle").val(edgedata.edge1.smooth.type);
        $("#edgetextface").val(edgedata.edge1.font.face);
        $("#edgetextsize").val(edgedata.edge1.font.size);
        $("#edgetextcolour").val(edgedata.edge1.font.color);
        $("#edgetextshow").bootstrapSwitch('state', (edgedata.edge1.label != null && edgedata.edge1.label.includes('xx%')));
        $("#edgebpsshow").bootstrapSwitch('state', (edgedata.edge1.label != null && edgedata.edge1.label.includes('bps')));
        $("#edgelabel").val('label' in edgedata.mid ? edgedata.mid.label : '');

        $("#edgeRecenterRow").show();
        $("#divEdgeFrom").show();
        $("#divEdgeTo").show();
        $("#edgelabel").show();
        $("#edge-saveButton").show();
        $("#edge-saveDefaultsButton").hide();
        $("#edge-saveButton").on("click", {data: edgedata}, callback);

        $('#edgeModal').modal({backdrop: 'static', keyboard: false}, 'show');
    }

    function editEdgeSave(event) {
        edgedata = event.data.data;

        edgeNodesUpdate(edgedata.id, $("#edgefrom").val(), $("#edgeto").val(), edgedata.edge1.from, edgedata.edge2.from);

        editEdgeHide();
        edgedata.edge1.smooth.type = $("#edgestyle").val();
        edgedata.edge2.smooth.type = $("#edgestyle").val();
        edgedata.edge1.from = $("#edgefrom").val();
        edgedata.edge2.from = $("#edgeto").val();
        edgedata.edge1.font.face = edgedata.edge2.font.face = $("#edgetextface").val();
        edgedata.edge1.font.size = edgedata.edge2.font.size = $("#edgetextsize").val();
        edgedata.edge1.font.color = edgedata.edge2.font.color = $("#edgetextcolour").val();
        edgedata.edge1.label = edgedata.edge2.label = edgeLabel($("#edgetextshow").prop('checked'), $("#edgebpsshow").prop('checked'), null);
        edgedata.edge1.title = edgedata.edge2.title = $("#port_id").val();
	let newlabel = $("#edgelabel").val() || '';
	if (newlabel == '' && edgedata.mid.label != '') {
            $("#map-renderButton").show();
	}
        edgedata.mid.label = newlabel;

        if(edgedata.id) {
            if($("#port_id").val()) {
                edge_port_map[edgedata.id] = {port_id: $("#port_id").val(), port_name: $("#port_name").text(), reverse: $("#portreverse")[0].checked}
            } else {
                delete edge_port_map[edgedata.id];
            }
        }

        // Special case for curved lines
        if(edgedata.edge2.smooth.type == "curvedCW") {
            edgedata.edge2.smooth.type = "curvedCCW";
        } else if (edgedata.edge2.smooth.type == "curvedCCW") {
            edgedata.edge2.smooth.type = "curvedCW";
        }

        if(edgedata.add) {
            network_nodes.add([edgedata.mid]);
            network_nodes.flush();
            network_edges.add([edgedata.edge1, edgedata.edge2]);
            network_edges.flush();
        } else {
            network_edges.update([edgedata.edge1, edgedata.edge2]);
            network_nodes.update([edgedata.mid]);

            if($("#edgerecenter").is(":checked")) {
                var pos = network.getPositions([edgedata.edge1.from, edgedata.edge2.from]);
                const mid_pos = getMidPos(edgedata.id, edgedata.edge1.from, edgedata.edge2.from);

                edgedata.mid.x = mid_pos.x;
                edgedata.mid.y = mid_pos.y;
                network_nodes.update([edgedata.mid]);
                $("#map-renderButton").show();
            }

            // Blank labels need to be selected to update.  Select both to ensure this happens
            if(! edgedata.edge1.label) {
                network_edges.flush();
                network.selectEdges([edgedata.edge2.id]);
                // Redraw to make sure the above change is reflected in the view before we select the next edge
                network.redraw();
                // Select the first edge, which will trigger another update
                network.selectEdges([edgedata.edge1.id]);
            }
        }
        $("#edgerecenter").prop( "checked", false );
        $("#map-saveDataButton").show();
    }

    function editEdgeCancel(event) {
        editEdgeHide();
    }

    function editEdgeHide() {
        $("#edge-saveButton").off("click");
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

        $("#edgeModalLabel").text("Edit Edge");
        editEdge(edgedata, editEdgeSave);
    }

    function deleteEdge(edgeid) {
        const edge1 = network_edges.get(edgeid + "_from");
        const edge2 = network_edges.get(edgeid + "_to");
        var nm_id = edge1.from < edge2.from ? edge1.from + '.' + edge2.from : edge2.from + '.' + edge1.from;
        edgeNodesRemove(nm_id, edgeid);
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
                    if(node.device_id) {
                        node_device_map[nodeid] = {device_id: node.device_id, device_name: node.device_name, device_image: node.device_image};
                        node_cfg.title = node.device_id;
                    } else if(node.linked_map_id) {
                        node_cfg.title = "map:" + node.linked_map_id;
                    } else {
                        node_cfg.title = null;
                    }
                    node_cfg.label = node.label;
                    node_cfg.shape = node.style;
                    node_cfg.borderWidth = node.border_width;
                    node_cfg.x = node.x_pos;
                    node_cfg.y = node.y_pos;
                    node_cfg.font = {face: node.text_face, size: node.text_size, color: node.text_colour};
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
                    edgeNodesUpdate(edgeid, edge.custom_map_node1_id, edge.custom_map_node2_id, -1, -1);

                    var mid_x = edge.mid_x;
                    var mid_y = edge.mid_y;

                    var mid = {id: edgeid + "_mid", shape: "dot", size: 0, x: mid_x, y: mid_y, label: edge.label};
                    mid.size = 3;

                    var arrows;
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
                        edge_port_map[edgeid] = {port_id: edge.port_id, port_name: edge.port_name, reverse: edge.reverse};
                        edge1.title = edge2.title = edge.port_id;
                    } else {
                        edge1.title = edge2.title = '';
                    }
                    edge1.label = edge2.label = edgeLabel(edge.showpct, edge.showbps, '');
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

    $(document).ready(function () {
        init_select2('#devicesearch', 'device', {limit: 100}, '', '{{ __('map.custom.edit.node.device_select') }}', {dropdownParent: $('#nodeModal')});
        $("#devicesearch").on("select2:select", nodeDeviceSelect);

        init_select2('#portsearch', 'port', function(params) {
            return {
                limit: 100,
                devices: [port_search_device_id_1, port_search_device_id_2],
                term: params.term,
                page: params.page || 1
            }
        }, '', '{{ __('map.custom.edit.edge.port_select') }}', {dropdownParent: $('#edgeModal')});
        $("#portsearch").on("select2:select", edgePortSelect);

        refreshMap();
    });
</script>
@endsection

