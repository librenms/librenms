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
    var node_align = {{$node_align}};
    var network_nodes = new vis.DataSet({queue: {delay: 100}});
    var network_edges = new vis.DataSet({queue: {delay: 100}});
    var node_device_map = {};
    var custom_image_base = "images/custommap/icons/";

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

                var pos = network.getPositions([data.from, data.to]);
                var mid_x = (pos[data.from].x + pos[data.to].x) >> 1;
                var mid_y = (pos[data.from].y + pos[data.to].y) >> 1;

                var edgeid = "new" + newcount++;

                var mid = {id: edgeid + "_mid", shape: "dot", size: 3, x: mid_x, y: mid_y};

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
                    move = false;
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

    function editMapSuccess(data) {
        $("#title").text(data.name);
        $("#savemap-alert").attr("class", "col-sm-12");
        $("#savemap-alert").text("");
        network.setSize(data.width, data.height);

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
            if(node.id.endsWith("_mid")) {
                edgeid = node.id.split("_")[0];
                edge1 = network_edges.get(edgeid + "_from");
                edge2 = network_edges.get(edgeid + "_to");
                edges[edgeid] = {id: edgeid, text_colour: edge1.font.color, text_size: edge1.font.size, text_face: edge1.font.face, from: edge1.from, to: edge2.from, showpct: (edge1.label ? true : false), port_id: edge1.title, style: edge1.smooth.type, mid_x: node.x, mid_y: node.y, reverse: (edgeid in edge_port_map ? edge_port_map[edgeid].reverse : false)};
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
            data: {
                newnodeconf: newnodeconf,
                newedgeconf: newedgeconf,
                nodes: nodes,
                edges: edges,
            },
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

        $("#edgestyle").val(newedgeconf.smooth.type);
        $("#edgetextface").val(newedgeconf.font.face);
        $("#edgetextsize").val(newedgeconf.font.size);
        $("#edgetextcolour").val(newedgeconf.font.color);
        $("#edgetextshow").bootstrapSwitch('state', Boolean(newedgeconf.label));
        $('#edgecolourtextreset').attr('disabled', 'disabled');

        $("#edge-saveButton").hide();
        $("#edge-saveDefaultsButton").show();
        $('#edgeModal').modal({backdrop: 'static', keyboard: false}, 'show');
    }

    function editEdgeDefaultsSave() {
        editEdgeHide();
        newedgeconf.smooth.type = $("#edgestyle").val();
        newedgeconf.font.face = $("#edgetextface").val();
        newedgeconf.font.size = $("#edgetextsize").val();
        newedgeconf.font.color = $("#edgetextcolour").val();
        newedgeconf.label = $("#edgetextshow").prop('checked');
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
        $("#edgetextshow").bootstrapSwitch('state', Boolean(edgedata.edge1.label));

        $("#edgeRecenterRow").show();
        $("#divEdgeFrom").show();
        $("#divEdgeTo").show();
        $("#edge-saveButton").show();
        $("#edge-saveDefaultsButton").hide();
        $("#edge-saveButton").on("click", {data: edgedata}, callback);

        $('#edgeModal').modal({backdrop: 'static', keyboard: false}, 'show');
    }

    function editEdgeSave(event) {
        edgedata = event.data.data;

        editEdgeHide();
        edgedata.edge1.smooth.type = $("#edgestyle").val();
        edgedata.edge2.smooth.type = $("#edgestyle").val();
        edgedata.edge1.from = $("#edgefrom").val();
        edgedata.edge2.from = $("#edgeto").val();
        edgedata.edge1.font.face = edgedata.edge2.font.face = $("#edgetextface").val();
        edgedata.edge1.font.size = edgedata.edge2.font.size = $("#edgetextsize").val();
        edgedata.edge1.font.color = edgedata.edge2.font.color = $("#edgetextcolour").val();
        edgedata.edge1.label = edgedata.edge2.label = $("#edgetextshow").prop('checked') ? "xx%" : null;
        edgedata.edge1.title = edgedata.edge2.title = $("#port_id").val();

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

            if($("#edgerecenter").is(":checked")) {
                var pos = network.getPositions([edgedata.edge1.from, edgedata.edge2.from]);
                var mid_x = (pos[edgedata.edge1.from].x + pos[edgedata.edge2.from].x) >> 1;
                var mid_y = (pos[edgedata.edge1.from].y + pos[edgedata.edge2.from].y) >> 1;

                edgedata.mid.x = mid_x;
                edgedata.mid.y = mid_y;
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
        network_edges.remove(edgeid + "_to");
        network_edges.remove(edgeid + "_from");
        network_edges.flush();
        network_nodes.remove(edgeid + "_mid");
        network_nodes.flush();
        $("#map-saveDataButton").show();
    }

    function refreshMap() {
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
                    var mid_x = edge.mid_x;
                    var mid_y = edge.mid_y;

                    var mid = {id: edgeid + "_mid", shape: "dot", size: 0, x: mid_x, y: mid_y};
                    mid.size = 3;

                    var edge1 = {id: edgeid + "_from", from: edge.custom_map_node1_id, to: edgeid + "_mid", arrows: {to: {enabled: true, scaleFactor: 0.6}}, font: {face: edge.text_face, size: edge.text_size, color: edge.text_colour}, smooth: {type: edge.style}};
                    var edge2 = {id: edgeid + "_to", from: edge.custom_map_node2_id, to: edgeid + "_mid", arrows: {to: {enabled: true, scaleFactor: 0.6}}, font: {face: edge.text_face, size: edge.text_size, color: edge.text_colour}, smooth: {type: edge.style}};

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
                    if(edge.showpct) {
                        edge1.label = edge2.label = 'xx%';
                    } else {
                        edge1.label = edge2.label = '';
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

