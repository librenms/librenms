@extends('layouts.librenmsv1')

@section('title', __('Custom Map'))

@section('content')
@if($edit && !is_null($map_id))
<div class="modal fade" id="nodeModal" tabindex="-1" role="dialog" aria-labelledby="nodeModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="nodeModalLabel">New Node</h5>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="well well-lg">
              <div class="form-group row single-node" id="nodeDeviceLabelRow">
                <label for="nodelabel" class="col-sm-3 control-label">Label</label>
                <div class="col-sm-9">
                  <input type=text id="nodelabel" class="form-control input-sm" value="Node Name" />
                </div>
              </div>
              <div class="form-group row single-node" id="nodeDeviceSearchRow">
                <label for="devicesearch" class="col-sm-3 control-label">Select Device</label>
                <div class="col-sm-9">
                  <input class="form-control typeahead" type="search" id="devicesearch" name="devicesearch" placeholder="Select Device" autocomplete="off">
                </div>
              </div>
              <div class="form-group row single-node" id="nodeDeviceRow" style="display:none">
                <label for="deviceclear" class="col-sm-3 control-label">Selected Device</label>
                <div class="col-sm-7">
                  <div id="device_name">
                  </div>
                  <input type="hidden" id="device_id">
                </div>
                <div class="col-sm-2">
                  <button type=button class="btn btn-primary" value="save" id="deviceclear" onclick="nodeDeviceClear();">Clear</button>
                </div>
              </div>
              <div class="form-group row">
                <label for="nodestyle" class="col-sm-3 control-label">Style</label>
                <div class="col-sm-9">
                  <select id="nodestyle" class="form-control input-sm" onchange="nodeStyleChange();">
                    <option value="box">Box</option>
                    <option value="circle">Circle</option>
                    <option value="database">Database</option>
                    <option value="ellipse">Ellipse</option>
                    <option value="text">Text</option>
                    <option value="image" id="nodestyleimage">Device Image</option>
                    <option value="circularImage" id="nodestylecircularimage">Device Image (Circular)</option>
                    <option value="diamond">Diamond</option>
                    <option value="dot">Dot</option>
                    <option value="star">Star</option>
                    <option value="triangle">Triangle</option>
                    <option value="triangleDown">Triangle Inverted</option>
                    <option value="hexagon">Hexagon</option>
                    <option value="square">Square</option>
                    <option value="icon">Icon (select below)</option>
                  </select>
                  <input type="hidden" id="device_image">
                </div>
              </div>
              <div class="form-group row" id="nodeIconRow">
                <label for="nodeicon" class="col-sm-3 control-label">Icon</label>
                <div class="col-sm-6">
                  <select id="nodeicon" class="form-control input-sm" onchange="setNodeIcon();">
                    <option value="f233">Server</option>
                    <option value="f390">Desktop</option>
                    <option value="f7c0">Satellite Dish</option>
                    <option value="f7bf">Satellite</option>
                    <option value="f1eb">Wifi</option>
                    <option value="f0c2">Cloud</option>
                    <option value="f0ac">Globe</option>
                    <option value="f519">Tower</option>
                  </select>
                </div>
                <div class="col-sm-3">
                    <i class="fa" id="nodeiconpreview">&#xf233</i>
                </div>
              </div>
              <div class="form-group row">
                <label for="nodesize" class="col-sm-3 control-label">Node Size</label>
                <div class="col-sm-9">
                  <input type=number id="nodesize" class="form-control input-sm" value=50 />
                </div>
              </div>
              <div class="form-group row">
                <label for="nodetextface" class="col-sm-3 control-label">Text Font</label>
                <div class="col-sm-9">
                  <input type=text id="nodetextface" class="form-control input-sm" value="arial" />
                </div>
              </div>
              <div class="form-group row">
                <label for="nodetextsize" class="col-sm-3 control-label">Text Size</label>
                <div class="col-sm-9">
                  <input type=number id="nodetextsize" class="form-control input-sm" value=14 />
                </div>
              </div>
              <div class="form-group row">
                <label for="nodetextcolour" class="col-sm-3 control-label">Text Colour</label>
                <div class="col-sm-2">
                  <input type=color id="nodetextcolour" class="form-control input-sm" value="#343434" onchange="$('#nodecolourtextreset').removeAttr('disabled');" />
                </div>
                <div class="col-sm-5">
                </div>
                <div class="col-sm-2">
                  <button type=button class="btn btn-primary" value="reset" id="nodecolourtextreset" onclick="$('#nodetextcolour').val(newnodeconf.font.color); $(this).attr('disabled','disabled');">Reset</button>
                </div>
              </div>
              <div class="form-group row" id="nodeColourBgRow">
                <label for="nodecolourbg" class="col-sm-3 control-label">Background Colour</label>
                <div class="col-sm-2">
                  <input type=color id="nodecolourbg" class="form-control input-sm" value="#343434" onchange="$('#nodecolourbgreset').removeAttr('disabled');" />
                </div>
                <div class="col-sm-5">
                </div>
                <div class="col-sm-2">
                  <button type=button class="btn btn-primary" value="reset" id="nodecolourbgreset" onclick="$('#nodecolourbg').val(newnodeconf.color.background); $(this).attr('disabled','disabled');">Reset</button>
                </div>
              </div>
              <div class="form-group row" id="nodeColourBdrRow">
                <label for="nodecolourbdr" class="col-sm-3 control-label">Border Colour</label>
                <div class="col-sm-2">
                  <input type=color id="nodecolourbdr" class="form-control input-sm" value="#343434" onchange="$('#nodecolourbdrreset').removeAttr('disabled');" />
                </div>
                <div class="col-sm-5">
                </div>
                <div class="col-sm-2">
                  <button type=button class="btn btn-primary" value="reset" id="nodecolourbdrreset" onclick="$('#nodecolourbdr').val(newnodeconf.color.border); $(this).attr('disabled','disabled');">Reset</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <center>
          <button type=button class="btn btn-primary" value="savedefaults" id="node-saveDefaultsButton" data-dismiss="modal" style="display:none" onclick="editNodeDefaultsSave();">Set Defaults</button>
          <button type=button class="btn btn-primary" value="save" id="node-saveButton" data-dismiss="modal">Save</button>
          <button type=button class="btn btn-primary" value="cancel" id="node-cancelButton" data-dismiss="modal" onclick="editNodeCancel();">Cancel</button>
        </center>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="edgeModal" tabindex="-1" role="dialog" aria-labelledby="edgeModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="edgeModalLabel">New Edge</h5>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="well well-lg">
              <div class="form-group row" id="divEdgeFrom">
                <label for="edgefrom" class="col-sm-3 control-label">From</label>
                <div class="col-sm-9">
                  <select id="edgefrom" class="form-control input-sm">
                  </select>
                </div>
              </div>
              <div class="form-group row" id="divEdgeTo">
                <label for="edgeto" class="col-sm-3 control-label">To</label>
                <div class="col-sm-9">
                  <select id="edgeto" class="form-control input-sm">
                  </select>
                </div>
              </div>
              <div class="form-group row single-node" id="edgePortSearchRow" style="display:none">
                <label for="portsearch" class="col-sm-3 control-label">Select Port</label>
                <div class="col-sm-9">
                  <input class="form-control typeahead" type="search" id="portsearch" name="portsearch" placeholder="Select Port" autocomplete="off">
                </div>
              </div>
              <div class="form-group row" id="edgePortRow" style="display:none">
                <label for="portclear" class="col-sm-3 control-label">Selected Port</label>
                <div class="col-sm-7">
                  <div id="port_name">
                  </div>
                  <input type="hidden" id="port_id">
                </div>
                <div class="col-sm-2">
                  <button type=button class="btn btn-primary" value="save" id="portclear" onclick="edgePortClear();">Clear</button>
                </div>
              </div>
              <div class="form-group row" id="edgePortReverseRow" style="display:none">
                <label for="portreverse" class="col-sm-3 control-label">Reverse Port Direction</label>
                <div class="col-sm-9">
                  <input class="form-check-input" type="checkbox" role="switch" id="portreverse">
                </div>
              </div>
              <div class="form-group row">
                <label for="edgestyle" class="col-sm-3 control-label">Line Style</label>
                <div class="col-sm-9">
                  <select id="edgestyle" class="form-control input-sm">
                    <option value="dynamic">Dynamic</option>
                    <option value="continuous">Continuous</option>
                    <option value="discrete">Discrete</option>
                    <option value="diagonalCross">Diagonal Cross</option>
                    <option value="straightCross">Straight Cross</option>
                    <option value="horizontal">Horizontal</option>
                    <option value="vertical">Vertical</option>
                    <option value="curvedCW">Curved Clockwise</option>
                    <option value="curvedCCW">Curved Counter Clockwise</option>
                    <option value="cubicBezier">Cubic Bezier</option>
                  </select>
                </div>
              </div>
              <div class="form-group row">
                <label for="edgetextshow" class="col-sm-3 control-label">Show percent usage</label>
                <div class="col-sm-9">
                  <input class="form-check-input" type="checkbox" role="switch" id="edgetextshow">
                </div>
              </div>
              <div class="form-group row">
                <label for="edgetextface" class="col-sm-3 control-label">Text Font</label>
                <div class="col-sm-9">
                  <input type=text id="edgetextface" class="form-control input-sm" value="arial" />
                </div>
              </div>
              <div class="form-group row">
                <label for="edgetextsize" class="col-sm-3 control-label">Text Size</label>
                <div class="col-sm-9">
                  <input type=number id="edgetextsize" class="form-control input-sm" value=14 />
                </div>
              </div>
              <div class="form-group row">
                <label for="edgetextcolour" class="col-sm-3 control-label">Text Colour</label>
                <div class="col-sm-2">
                  <input type=color id="edgetextcolour" class="form-control input-sm" value="#343434" onchange="$('#edgecolourtextreset').removeAttr('disabled');" />
                </div>
                <div class="col-sm-5">
                </div>
                <div class="col-sm-2">
                  <button type=button class="btn btn-primary" value="reset" id="edgecolourtextreset" onclick="$('#edgetextcolour').val(newedgeconf.font.color); $(this).attr('disabled','disabled');">Reset</button>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-12" id="saveedge-alert">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <center>
          <button type=button class="btn btn-primary" value="savedefaults" id="edge-saveDefaultsButton" data-dismiss="modal" style="display:none" onclick="editEdgeDefaultsSave();">Set Defaults</button>
          <button type=button class="btn btn-primary" value="save" id="edge-saveButton" data-dismiss="modal">Save</button>
          <button type=button class="btn btn-primary" value="cancel" id="edge-cancelButton" data-dismiss="modal" onclick="editEdgeCancel();">Cancel</button>
        </center>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="mapModal" tabindex="-1" role="dialog" aria-labelledby="mapModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mapModalLabel">Map Settings</h5>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="well well-lg">
              <div class="form-group row">
                <label for="mapname" class="col-sm-3 control-label">Name</label>
                <div class="col-sm-9">
                    <input type="text" id="mapname" name="mapname" class="form-control input-sm" value="{{$name}}">
                </div>
              </div>
              <div class="form-group row">
                <label for="mapwidth" class="col-sm-3 control-label">Width</label>
                <div class="col-sm-9">
                    <input type="text" id="mapwidth" name="mapwidth" class="form-control input-sm" value="{{$map_conf['width']}}">
                </div>
              </div>
              <div class="form-group row">
                <label for="mapheight" class="col-sm-3 control-label">Height</label>
                <div class="col-sm-9">
                    <input type="text" id="mapheight" name="mapheight" class="form-control input-sm" value="{{$map_conf['height']}}">
                </div>
              </div>
              <div class="form-group row" id="mapBackgroundRow">
                <label for="selectbackground" class="col-sm-3 control-label">Background</label>
                <div class="col-sm-9">
                  <input id="mapBackgroundSelect" type="file" name="selectbackground" accept="image/png, image/jpeg" class="form-control" onchange="mapChangeBackground();">
                  <button id="mapBackgroundCancel" type="button" name="cancelbackground" class="btn btn-primary" onclick="mapChangeBackgroundCancel();" style="display:none">Cancel</button>
                </div>
              </div>
              <div class="form-group row" id="mapBackgroundClearRow">
                <label for="clearbackground" class="col-sm-3 control-label">Clear BG</label>
                <div class="col-sm-9">
                  <input type="hidden" id="mapBackgroundClearVal">
                  <button id="mapBackgroundClear" type="button" name="clearbackground" class="btn btn-primary" onclick="mapClearBackground();">Clear Background</button>
                </div>
              </div>
              <hr>
              <div class="row">
                <div class="col-sm-12" id="savemap-alert">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <center>
          <button type=button value="save" id="map-saveButton" class="btn btn-primary" onclick="saveMapSettings()">Save</button>
          <button type=button value="cancel" id="map-cancelButton" class="btn btn-primary" onclick="editMapCancel()">Cancel</button>
        </center>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="mapDeleteModal" tabindex="-1" role="dialog" aria-labelledby="mapDeleteModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mapDeleteModalLabel">Delete Map</h5>
      </div>
      <div class="modal-footer">
        <center>
          <button type=button value="delete" id="map-deleteConfirmButton" class="btn btn-danger" onclick="deleteMap()">Delete</button>
          <button type=button value="cancel" id="map-deleteCancelButton" class="btn btn-primary" onclick="$('#mapDeleteModal').modal('hide');">Cancel</button>
        </center>
      </div>
    </div>
  </div>
</div>
@endif {{-- Edit mode with map_id not null --}}

<div class="container-fluid">

@if($edit && is_null($map_id))
  <div class="row" id="control-row">
    <div class="col-md-12">
      <select id="show_group" class="page-availability-report-select" name="show_group" onchange="selectMap(this)">
        <option value="-1" selected>Select map to edit</option>
        <option value="0">Create New Map</option>
@foreach($maps as $map)
        <option value="{{$map->custom_map_id}}">{{$map->name}}</option>
@endforeach
      </select>
    </div>
  </div>
@elseif($edit)
  <div class="row" id="control-row">
    <div class="col-md-5">
      <button type=button value="mapedit" id="map-editButton" class="btn btn-primary" onclick="editMapSettings();">Edit Map Settings</button>
      <button type=button value="editnodedefaults" id="map-nodeDefaultsButton" class="btn btn-primary" onclick="editNodeDefaults();">Edit Node Defaults</button>
      <button type=button value="editedgedefaults" id="map-edgeDefaultsButton" class="btn btn-primary" onclick="editEdgeDefaults();">Edit Edge Defaults</button>
    </div>
    <div class="col-md-2">
      <center>
        <h4 id="title">{{$name}}</h4>
      </center>
    </div>
    <div class="col-md-5 text-right">
      <button type=button value="maprender" id="map-renderButton" class="btn btn-primary" style="display: none" onclick="CreateNetwork();">Re-Render Map</button>
      <button type=button value="mapsave" id="map-saveDataButton" class="btn btn-primary" style="display: none" onclick="saveMapData();">Save Map</button>
      <button type=button value="mapdelete" id="map-deleteButton" class="btn btn-danger" onclick="$('#mapDeleteModal').modal({backdrop: 'static', keyboard: false}, 'show');">Delete Map</button>
    </div>
  </div>

  <div class="row" id="control-map-sep">
    <div class="col-md-12">
      <hr>
    </div>
  </div>
@endif {{-- edit mode with map_id not null --}}
@if(! is_null($map_id))
  <div class="row" id="alert-row">
    <div class="col-md-12">
      <div class="alert alert-warning" role="alert" id="alert">Loading data</div>
    </div>
  </div>
@endif

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
@if(! is_null($map_id))
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

@if($edit)
        // Set up the triggers for adding and editing map items
        options['manipulation']['addNode'] = function (data, callback) {
                callback(null);
                $("#nodeModalLabel").text("Add Node");
                var node = structuredClone(newnodeconf);
                node.id = "new" + newcount++;
                node.label = "New Node";
                node.x = data.x;
                node.y = data.y;
                node.add = true;
                $(".single-node").show();
                editNode(node, editNodeSave);
            }
        options['manipulation']['editNode'] = function (data, callback) {
                callback(null);
                $("#nodeModalLabel").text("Edit Node");
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

                $("#edgeModalLabel").text("Add Edge");
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
@endif

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

@if($edit)
        network.on('dragEnd', function (data) {
            if(data.edges.length > 0 || data.nodes.length > 0) {
                // Make sure a node is not dragged outside the canvas
                nodepos = network.getPositions(data.nodes);
                $.each( nodepos, function( nodeid, node ) {
                    move = false;
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
@else
        network.on('doubleClick', function (properties) {
            if (properties.nodes > 0 && properties.nodes[0] in node_device_map) {
                window.location.href = "device/"+node_device_map[properties.nodes[0]].device_id;
            }
        });
@endif
    }
@endif
@if(!$edit)
    var Countdown;
@endif

@if($edit)
@if(is_null($map_id))
    function selectMap(caller) {
        if($(caller).val() < 0) {
            return true;
        } else if ($(caller).val() == 0) {
            window.location.href = "{{ route("maps.custom.edit") }}/new";
        }
        window.location.href = "{{ route("maps.custom.edit") }}/" + $(caller).val();
    }
@else {{-- edit mode with map id not null --}}
    function mapChangeBackground() {
        $("#mapBackgroundCancel").show();
    }

    function mapChangeBackgroundCancel() {
        $("#mapBackgroundCancel").hide();
        $("#mapBackgroundSelect").val(null);
    }

    function mapClearBackground() {
        if($('#mapBackgroundClearVal').val()) {
            $('#mapBackgroundClear').text('Clear Background');
            $('#mapBackgroundClearVal').val('');
        } else {
            $('#mapBackgroundClear').text('Keep Background');
            $('#mapBackgroundClearVal').val('clear');
        }
    }

    function editMapSettings() {
        $("#mapBackgroundCancel").hide();
        $("#mapBackgroundSelect").val(null);

        if($("#custom-map").children()[0].canvas.style.backgroundImage) {
            $("#mapBackgroundClearRow").show();
        } else {
            $("#mapBackgroundClearRow").hide();
        }
        $('#mapModal').modal({backdrop: 'static', keyboard: false}, 'show');
    }

    function saveMapSettings() {
        $("#map-saveButton").attr('disabled','disabled');
        $("#savemap-alert").text('Saving...');
        $("#savemap-alert").attr("class", "col-sm-12 alert alert-info");

        var name = $("#mapname").val();
        var width = $("#mapwidth").val();
        var height = $("#mapheight").val();
        var clearbackground = $('#mapBackgroundClearVal').val() ? true : false;
        var newbackground = $('#mapBackgroundSelect').prop('files').length ? $('#mapBackgroundSelect').prop('files')[0] : '';

        if(!isNaN(width)) {
            width = width + "px";
        }
        if(!isNaN(height)) {
            height = height + "px";
        }

        var fd = new FormData();
        fd.append('name', name);
        fd.append('width', width);
        fd.append('height', height);
        fd.append('bgclear', clearbackground);
        fd.append('bgimage', newbackground);

        $.ajax({
            url: '{{ route('maps.custom.savesettings', ['map_id' => $map_id]) }}',
            data: fd,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function( data, status, resp ) {
                if(data['errors'].length) {
                    $("#savemap-alert").html("Save failed due to the following errors:<br />" + data['errors'].join("<br />"));
                    $("#savemap-alert").attr("class", "col-sm-12 alert alert-danger");
                } else {
@if($map_id)
                    $("#title").text(name);
                    $("#savemap-alert").attr("class", "col-sm-12");
                    $("#savemap-alert").text("");
                    network.setSize(width, height);

                    canvas = $("#custom-map").children()[0].canvas;
                    if(data['bgimage']) {
                        $(canvas).css('background-image','url({{ route('maps.custom.background', ['map_id' => $map_id]) }})').css('background-size', 'cover');
                        bgimage = true;
                    } else {
                        $(canvas).css('background-image','');
                        bgimage = false;
                    }

                    editMapCancel();
@else
                    window.location.href = "{{ @route('maps.custom.edit') }}/" + data['id'];
@endif
                }
            },
            error: function( resp, status, error ) {
                $("#savemap-alert").text("Save failed.  Server returned error response code: " + resp.status);
                $("#savemap-alert").attr("class", "col-sm-12 alert alert-danger");
            },
            complete: function( resp, status, error ) {
                $("#map-saveButton").removeAttr('disabled');
            },
        });
    }
@if(!$map_id)
    // New map - pop up the modal to set initial settings
    $('#mapModal').modal({backdrop: 'static', keyboard: false}, 'show');
    $("#mapBackgroundClearRow").hide();

    function editMapCancel() {
        window.location.href = "{{ route("maps.custom.edit") }}/";
    }
@else {{-- edit mode with map id > 0 --}}
    var newedgeconf = {!! json_encode($newedge_conf) !!};
    var newnodeconf = {!! json_encode($newnode_conf) !!};
    var newcount = 1;
    var port_search_device_id_1 = 0;
    var port_search_device_id_2 = 0;

    var edge_port_map = {};

    function deleteMap() {
        $.post("{{ route('maps.custom.delete', ['map_id' => $map_id]) }}")
            .done(function() {
                window.location.href = "{{ route('maps.custom.edit') }}";
            });
    }

    function editMapCancel() {
        $('#mapBackgroundClear').text('Clear Background');
        $('#mapBackgroundClearVal').val('');
        $("#mapBackgroundCancel").hide();
        $("#mapBackgroundSelect").val(null);
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
                nodes[node.id] = node;
            }
        });
        var fd = new FormData();
        fd.append('newnodeconf', JSON.stringify(newnodeconf));
        fd.append('newedgeconf', JSON.stringify(newedgeconf));
        fd.append('nodes', JSON.stringify(nodes));
        fd.append('edges', JSON.stringify(edges));

        $.ajax({
            url: '{{ route('maps.custom.save', ['map_id' => $map_id]) }}',
            data: fd,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function( data, status, resp ) {
                if(data['errors'].length) {
                    $("#alert").html("Save failed due to the following errors:<br />" + data['errors'].join("<br />"));
                    $("#alert").attr("class", "col-sm-12 alert alert-danger");
                    $("#alert-row").show();
                } else {
                    $("#map-saveDataButton").hide();
                    $("#alert-row").hide();
                }
                // Re-read the map from the DB in case any items were modified
                refreshMap();
            },
            error: function( resp, status, error ) {
                $("#alert").text("Save failed.  Server returned error response code: " + resp.status);
                $("#alert").attr("class", "col-sm-12 alert alert-danger");
                $("#alert-row").show();
            },
            complete: function( resp, status, error ) {
                $("#map-saveDataButton").removeAttr('disabled');
            },
        });
    }

    function nodeStyleChange() {
        var nodestyle = $("#nodestyle").val();
        if(nodestyle == 'icon') {
            $("#nodeIconRow").show();
        } else {
            $("#nodeIconRow").hide();
        }
    }

    function nodeDeviceSelect(id, name, image) {
        $("#device_id").val(id);
        $("#device_name").text(name);
        $("#nodelabel").val(name.split(".")[0].split(" ")[0]);
        $("#device_image").val(image);
        $("#nodeDeviceSearchRow").hide();
        $("#nodestyleimage").show();
        $("#nodestylecircularimage").show();
        $("#nodeDeviceRow").show();
    }

    function nodeDeviceClear() {
        $("#devicesearch").typeahead('val','');
        $("#device_id").val("");
        $("#device_name").text("");
        $("#device_image").val("");
        $("#nodeDeviceRow").hide();
        $("#nodestyleimage").hide();
        $("#nodestylecircularimage").hide();
        $("#nodeDeviceSearchRow").show();

        // Reset device style if we were using the device image
        if($("#nodestyle").val() == "image" || $("#nodestyle").val() == "circularImage") {
            $("#nodestyle").val(newnodeconf.shape);
        }
    }

    function setNodeIcon() {
        var newcode = $("#nodeicon").val();
        $("#nodeiconpreview").text(String.fromCharCode(parseInt(newcode, 16)));
    }

    function editNodeDefaults() {
        $("#nodeModalLabel").text("Node Default Config");
        $(".single-node").hide();
        var node = structuredClone(newnodeconf);
        editNode(node, editNodeDefaultsSave);
    }

    function editNodeDefaultsSave() {
        newnodeconf.shape = $("#nodestyle").val();
        newnodeconf.font.face = $("#nodetextface").val();
        newnodeconf.font.size = $("#nodetextsize").val();
        newnodeconf.font.color = $("#nodetextcolour").val();
        newnodeconf.color.background = newnodeconf.color.highlight.background = newnodeconf.color.hover.background = $("#nodecolourbg").val();
        newnodeconf.color.border = newnodeconf.color.highlight.border = newnodeconf.color.hover.border = $("#nodecolourbdr").val();
        if(newnodeconf.shape == "icon") {
            newnodeconf.icon = {face: 'FontAwesome', code: String.fromCharCode(parseInt($("#nodeicon").val(), 16)), size: $("#nodesize").val(), color: newnodeconf.color.border};
        } else {
            newnodeconf.icon = {};
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
        $("#devicesearch").typeahead('val','');
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
        } else {
            // Node is not linked to a device
            $("#device_id").val("");
            $("#device_name").text("");
            // Hide the selected device row
            $("#nodeDeviceRow").hide();
        }
        // Show or hide the image option depending on whether the device has an image
        if(data.image) {
            $("#nodestyleimage").show();
            $("#nodestylecircularimage").show();
        } else {
            $("#nodestyleimage").hide();
            $("#nodestylecircularimage").hide();
        }
        $("#nodelabel").val(data.label);
        $("#nodestyle").val(data.shape);
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
            node.image = {unselected: $("#device_image").val()};
        } else {
            node.title = '';
            node.image = {};
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
                node_device_map[node.id] = {device_id: $("#device_id").val(), device_name: $("#device_name").text()}
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

        if(!node1.title && !node2.title) {
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

    function edgePortSelect(id, hostname, portname, reverse) {
        $("#port_id").val(id);
        $("#port_name").text(hostname + " - " + portname);
        $("#portreverse").bootstrapSwitch('state', reverse);

        $("#edgePortSearchRow").hide();
        $("#edgePortRow").show();
        $("#edgePortReverseRow").show();
    }

    function edgePortClear() {
        $("#portsearch").typeahead('val','');
        $("#port_id").val("");
        $("#port_name").text("");
        $("#edgePortSearchRow").show();
        $("#edgePortRow").hide();
        $("#edgePortReverseRow").hide();
    }

    function editEdgeDefaults() {
        $("#edgeModalLabel").text("Edge Default Config");
        $("#divEdgeFrom").hide();
        $("#divEdgeTo").hide();
        $("#edgePortRow").hide();
        $("#edgePortReverseRow").hide();
        $("#edgePortSearchRow").hide();

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
        $("#portsearch").typeahead('val','');
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
@endif {{-- map_id > 0 --}}
@endif {{-- map_id is not null --}}
@endif {{-- edit mode --}}

@if($map_id > 0)
    function refreshMap() {
        $.get( '{{ route('maps.custom.getdata', ['map_id' => $map_id]) }}')
            .done(function( data ) {
                // Add/update nodes
                $.each( data.nodes, function( nodeid, node) {
                    var node_cfg = {};
                    node_cfg.id = nodeid;
                    if(node.device_id) {
                        node_device_map[nodeid] = {device_id: node.device_id, device_name: node.device_name};
@if($edit)
                        node_cfg.title = node.device_id;
@else
                        node_cfg.title = node.device_info;
@endif
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
@if($edit)
                    node_cfg.color = {background: node.colour_bg, border: node.colour_bdr};
@else
                    node_cfg.color = {background: node.colour_bg_view, border: node.colour_bdr_view};
@endif
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
@if($edit)
                    mid.size = 3;
@endif

                    var edge1 = {id: edgeid + "_from", from: edge.custom_map_node1_id, to: edgeid + "_mid", arrows: {to: {enabled: true, scaleFactor: 0.6}}, font: {face: edge.text_face, size: edge.text_size, color: edge.text_colour}, smooth: {type: edge.style}};
                    var edge2 = {id: edgeid + "_to", from: edge.custom_map_node2_id, to: edgeid + "_mid", arrows: {to: {enabled: true, scaleFactor: 0.6}}, font: {face: edge.text_face, size: edge.text_size, color: edge.text_colour}, smooth: {type: edge.style}};

                    // Special case for curved lines
                    if(edge2.smooth.type == "curvedCW") {
                        edge2.smooth.type = "curvedCCW";
                    } else if (edge2.smooth.type == "curvedCCW") {
                        edge2.smooth.type = "curvedCW";
                    }
@if($edit)
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
@else
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
@endif
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
@if($edit)
                $("#alert").html("");
                $("#alert-row").hide();
@else
                if (Object.keys(data).length == 0) {
                    $("#alert").html("No devices found");
                    $("#alert-row").show();
                } else {
                    $("#alert").html("");
                    $("#alert-row").hide();
                }
@endif
            });

        // Initialise map if it does not exist
        if (! network) {
            CreateNetwork();
        }
    }

    $(document).ready(function () {
@if(! $edit)
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
@else {{-- edit mode and map id > 0 --}}
        var devices = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: ajax_url + "/search/device?search=%QUERY",
                filter: function (devices) {
                    return $.map(devices, function (device) {
                        return {
                            device_id: device.device_id,
                            device_image: device.device_image,
                            url: device.url,
                            name: device.name,
                            device_os: device.device_os,
                            version: device.version,
                            device_hardware: device.device_hardware,
                            device_ports: device.device_ports,
                            location: device.location
                        };
                    });
                },
                wildcard: "%QUERY"
            }
        });
        devices.initialize();

        var node1ports = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: ajax_url + "/search/device",
                replace: function(url, uriEncodedQuery) {
                    return url + '/' + port_search_device_id_1 + '/port?search=' + uriEncodedQuery;
                },
            }
        });
        node1ports.initialize();

        var node2ports = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: ajax_url + "/search/device",
                replace: function(url, uriEncodedQuery) {
                    return url + '/' + port_search_device_id_2 + '/port?search=' + uriEncodedQuery;
                },
            }
        });
        node2ports.initialize();

        $('#devicesearch').typeahead({
                hint: true,
                highlight: true,
                minLength: 1
            },
            {
                source: devices.ttAdapter(),
                limit: '25',
                async: true,
                display: 'name',
                templates: {
                    suggestion: Handlebars.compile('<p><img src="@{{device_image}}" style="float: left; min-height: 32px; margin-right: 5px;"> <small><strong>@{{name}}</strong> | @{{device_os}} | @{{version}} <br /> @{{device_hardware}} with @{{device_ports}} port(s) | @{{location}}</small></p>')
                }
            }).on('typeahead:select', function (ev, suggestion) {
                nodeDeviceSelect(suggestion.device_id, suggestion.name, suggestion.device_image);
            }).on('keyup', function (e) {
                // on enter go to the first selection
                if (e.which === 13) {
                    $('.tt-selectable').first().trigger( "click" );
                }
            });

        $('#portsearch').typeahead({
                hint: true,
                highlight: true,
                minLength: 1
            },
            {
                source: node1ports.ttAdapter(),
                limit: '25',
                async: true,
                display: 'name',
                templates: {
                    suggestion: Handlebars.compile('<p><small><i class="fa fa-link fa-sm icon-theme" aria-hidden="true"></i> <strong>@{{name}}</strong> - @{{hostname}}<br /><i>@{{description}}</i></small></p>')
                }
            },
            {
                source: node2ports.ttAdapter(),
                limit: '25',
                async: true,
                display: 'name',
                templates: {
                    suggestion: Handlebars.compile('<p><small><i class="fa fa-link fa-sm icon-theme" aria-hidden="true"></i> <strong>@{{name}}</strong> - @{{hostname}}<br /><i>@{{description}}</i></small></p>')
                }
            }).on('typeahead:select', function (ev, suggestion) {
                edgePortSelect(suggestion.port_id, suggestion.hostname, suggestion.name, (suggestion.device_id != port_search_device_id_1));
            }).on('keyup', function (e) {
                // on enter go to the first selection
                if (e.which === 13) {
                    $('.tt-selectable').first().trigger( "click" );
                }
            });

@endif {{-- edit mode --}}
        refreshMap();
    });
@endif {{-- map_id > 0 --}}
</script>
@endsection

