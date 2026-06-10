<div class="modal fade" id="edgeModal" tabindex="-1" role="dialog" aria-labelledby="edgeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edgeModalLabel">{{ __('map.custom.edit.edge.new') }}</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="well well-lg">
                            <div class="form-group row existing-edge" id="divEdgeFrom">
                                <label for="edgefrom" class="col-sm-3 control-label">{{ __('map.custom.edit.edge.from') }}</label>
                                <div class="col-sm-9">
                                    <select id="edgefrom" class="form-control input-sm">
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row existing-edge" id="divEdgeTo">
                                <label for="edgeto" class="col-sm-3 control-label">{{ __('map.custom.edit.edge.to') }}</label>
                                <div class="col-sm-9">
                                    <select id="edgeto" class="form-control input-sm">
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row" id="edgeDeviceSearchRow">
                                <label for="devicesearch" class="col-sm-3 control-label">{{ __('map.custom.edit.node.device_select') }}</label>
                                <div class="col-sm-9">
                                    <select name="edgedevicesearch" id="edgedevicesearch" class="form-control"></select>
                                </div>
                            </div>
                            <div class="form-group row existing-edge" id="edgePortSearchRow">
                                <label for="portsearch" class="col-sm-3 control-label">{{ __('map.custom.edit.edge.port_select') }}</label>
                                <div class="col-sm-9">
                                    <select name="portsearch" id="portsearch" class="form-control"></select>
                                </div>
                            </div>
                            <div class="form-group row existing-edge" id="edgePortRow" style="display:none">
                                <label for="portclear" class="col-sm-3 control-label">{{ __('Port') }}</label>
                                <div class="col-sm-7">
                                    <div id="port_name">
                                    </div>
                                    <input type="hidden" id="port_id">
                                </div>
                                <div class="col-sm-2">
                                    <button type=button class="btn btn-primary" value="save" id="portclear" onclick="edgePortClear();">{{ __('Clear') }}</button>
                                </div>
                            </div>
                            <div class="form-group row existing-edge" id="edgePortReverseRow" style="display:none">
                                <label for="portreverse" class="col-sm-3 control-label">{{ __('map.custom.edit.edge.reverse') }}</label>
                                <div class="col-sm-9">
                                    <input class="form-check-input" type="checkbox" role="switch" id="portreverse">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="edgestyle" class="col-sm-3 control-label">{{ __('map.custom.edit.edge.style') }}</label>
                                <div class="col-sm-9">
                                    <select id="edgestyle" class="form-control input-sm">
                                        <option value="dynamic">{{ __('map.custom.edit.edge.style_options.dynamic') }}</option>
                                        <option value="continuous">{{ __('map.custom.edit.edge.style_options.continuous') }}</option>
                                        <option value="discrete">{{ __('map.custom.edit.edge.style_options.discrete') }}</option>
                                        <option value="diagonalCross">{{ __('map.custom.edit.edge.style_options.diagonalCross') }}</option>
                                        <option value="straightCross">{{ __('map.custom.edit.edge.style_options.straightCross') }}</option>
                                        <option value="horizontal">{{ __('map.custom.edit.edge.style_options.horizontal') }}</option>
                                        <option value="vertical">{{ __('map.custom.edit.edge.style_options.vertical') }}</option>
                                        <option value="curvedCW">{{ __('map.custom.edit.edge.style_options.curvedCW') }}</option>
                                        <option value="curvedCCW">{{ __('map.custom.edit.edge.style_options.curvedCCW') }}</option>
                                        <option value="cubicBezier">{{ __('map.custom.edit.edge.style_options.cubicBezier') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="edgetextshow" class="col-sm-3 control-label">{{ __('map.custom.edit.edge.show_usage_percent') }}</label>
                                <div class="col-sm-9">
                                    <input class="form-check-input" type="checkbox" role="switch" id="edgetextshow">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="edgebpsshow" class="col-sm-3 control-label">{{ __('map.custom.edit.edge.show_usage_bps') }}</label>
                                <div class="col-sm-9">
                                    <input class="form-check-input" type="checkbox" role="switch" id="edgebpsshow">
                                </div>
                            </div>
                            <div class="form-group row existing-edge">
                                <label for="edgelabel" class="col-sm-3 control-label">{{ __('map.custom.edit.edge.label') }}</label>
                                <div class="col-sm-9">
                                    <input type=text id="edgelabel" class="form-control input-sm" value="" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="edgefixedwidth" class="col-sm-3 control-label">{{ __('map.custom.edit.edge.fixed_width') }}</label>
                                <div class="col-sm-9">
                                    <input type=number id="edgefixedwidth" class="form-control input-sm" placeholder="{{ __('map.custom.edit.edge.dynamic_width')  }}" step="0.1" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="edgetextface" class="col-sm-3 control-label">{{ __('map.custom.edit.text_font') }}</label>
                                <div class="col-sm-9">
                                    <input type=text id="edgetextface" class="form-control input-sm" value="arial" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="edgetextsize" class="col-sm-3 control-label">{{ __('map.custom.edit.text_size') }}</label>
                                <div class="col-sm-9">
                                    <input type=number id="edgetextsize" class="form-control input-sm" value=14 />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="edgetextcolour" class="col-sm-3 control-label">{{ __('map.custom.edit.text_color') }}</label>
                                <div class="col-sm-2">
                                    <input type=color id="edgetextcolour" class="form-control input-sm" value="#343434" onchange="$('#edgecolourtextreset').removeAttr('disabled');" />
                                </div>
                                <div class="col-sm-5">
                                </div>
                                <div class="col-sm-2">
                                    <button type=button class="btn btn-primary" value="reset" id="edgecolourtextreset" onclick="$('#edgetextcolour').val(newedgeconf.font.color); $(this).attr('disabled','disabled');">{{ __('Reset') }}</button>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="edgetextalign" class="col-sm-3 control-label">{{ __('map.custom.edit.edge.text_align') }}</label>
                                <div class="col-sm-9">
                                    <select id="edgetextalign" class="form-control input-sm">
                                        <option value="horizontal">{{ __('map.custom.edit.edge.align_options.horizontal') }}</option>
                                        <option value="top">{{ __('map.custom.edit.edge.align_options.top') }}</option>
                                        <option value="middle">{{ __('map.custom.edit.edge.align_options.middle') }}</option>
                                        <option value="bottom">{{ __('map.custom.edit.edge.align_options.bottom') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row existing-edge" id="edgeRecenterRow">
                                <label for="edgerecenter" class="col-sm-3 control-label">{{ __('map.custom.edit.edge.recenter') }}</label>
                                <div class="col-sm-9">
                                    <input type=checkbox class="form-check-input" value="recenter" id="edgerecenter">
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
                    <button type=button class="btn btn-primary new-edge" value="savedefaults" id="edge-saveDefaultsButton" data-dismiss="modal" style="display:none" onclick="edgeDefaultsSave();">{{ __('map.custom.edit.defaults') }}</button>
                    <button type=button class="btn btn-primary existing-edge" value="save" id="edge-saveButton" data-dismiss="modal">{{ __('Save') }}</button>
                    <button type=button class="btn btn-primary" value="cancel" id="edge-cancelButton" data-dismiss="modal">{{ __('Cancel') }}</button>
                </center>
            </div>
        </div>
    </div>
</div>

<script>
    var port_search_device_id_1 = 0;
    var port_search_device_id_2 = 0;

    function edgeCheckColourReset(itemColour, defaultColour, resetControlId) {
        if(!itemColour || itemColour.toLowerCase() == defaultColour.toLowerCase()) {
            $("#" + resetControlId).attr('disabled','disabled');
        } else {
            $("#" + resetControlId).removeAttr('disabled');
        }
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

    function edgeDeviceSelect(e) {
        port_search_device_id_1 = $("#edgedevicesearch").val();
        if (port_search_device_id_1) {
            edgePortClear();
            $("#edgePortSearchRow").show();
        } else {
            $("#edgePortSearchRow").hide();
        }
    }

    function edgeSave(event) {
        edgedata = event.data.data;

        edgeNodesUpdate(edgedata.id, $("#edgefrom").val(), $("#edgeto").val(), edgedata.edge1.from, edgedata.edge2.from);

        $("#edge-saveButton").off("click");
        edgedata.edge1.smooth.type = $("#edgestyle").val();
        edgedata.edge2.smooth.type = $("#edgestyle").val();
        edgedata.edge1.from = $("#edgefrom").val();
        edgedata.edge2.from = $("#edgeto").val();
        edgedata.edge1.font.face = edgedata.edge2.font.face = $("#edgetextface").val();
        edgedata.edge1.font.size = edgedata.edge2.font.size = $("#edgetextsize").val();
        edgedata.edge1.font.color = edgedata.edge2.font.color = $("#edgetextcolour").val();
        edgedata.edge1.font.align = edgedata.edge2.font.align = $("#edgetextalign").val();
        edgedata.edge1.font.background = edgedata.edge2.font.background = '#FFFFFF';
        edgedata.edge1.label = edgedata.edge2.label = edgeLabel($("#edgetextshow").prop('checked'), $("#edgebpsshow").prop('checked'), null);
        edgedata.edge1.width = edgedata.edge2.width = parseFloat($("#edgefixedwidth").val()) || null;
        edgedata.edge1.title = edgedata.edge2.title = $("#port_id").val();
        edgedata.edge1.arrowStrikethrough = edgedata.edge2.arrowStrikethrough = false;
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

    function edgeEditDefaults() {
        $("#edgeModalLabel").text('{{ __('map.custom.edit.edge.defaults_title') }}');

        $("#edgestyle").val(newedgeconf.smooth.type);
        $("#edgefixedwidth").val(newedgeconf.width);
        $("#edgetextface").val(newedgeconf.font.face);
        $("#edgetextsize").val(newedgeconf.font.size);
        $("#edgetextcolour").val(newedgeconf.font.color);
        $("#edgetextalign").val(newedgeconf.font.align || "horizontal");
        $("#edgetextshow").bootstrapSwitch('state', (newedgeconf.label.includes('xx%') || newedgeconf.label.includes('true')));
        $("#edgebpsshow").bootstrapSwitch('state', (newedgeconf.label.includes('bps')));
        $('#edgecolourtextreset').attr('disabled', 'disabled');

        $(".existing-edge").hide();
        $(".new-edge").show();

        $('#edgeModal').modal({backdrop: 'static', keyboard: false}, 'show');
    }

    function edgeEdit(edgedata) {
        // Hide and show buttons based on class
        $(".existing-edge").show();
        $(".new-edge").hide();

        if(edgedata.add) {
            $("#edgeModalLabel").text('{{ __('map.custom.edit.edge.add') }}');
        } else {
            $("#edgeModalLabel").text('{{ __('map.custom.edit.edge.edit') }}');
        }

        $("#edgedevicesearch").val('');
        $("#edgedevicesearch").trigger('change');
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
            if (! node.id.endsWith('_mid') && ! node.id.startsWith("legend_")) {
                $("#edgefrom").append('<option value="' + node.id + '">' + node.label+ '</option>');
                $("#edgeto").append('<option value="' + node.id + '">' + node.label+ '</option>');
            }
        });
        $("#edgefrom").val(edgedata.edge1.from);
        $("#edgeto").val(edgedata.edge2.from);

        edgePortSearchUpdate($("#edgefrom").val(), $("#edgeto").val(), edgedata.id);
        edgeCheckColourReset(edgedata.edge1.font.color, newedgeconf.font.color, "edgecolourtextreset");

        $("#edgestyle").val(edgedata.edge1.smooth.type);
        $("#edgetextface").val(edgedata.edge1.font.face);
        $("#edgetextsize").val(edgedata.edge1.font.size);
        $("#edgetextcolour").val(edgedata.edge1.font.color);
        $("#edgetextalign").val(edgedata.edge1.font.align || "horizontal");
        $("#edgetextshow").bootstrapSwitch('state', (edgedata.edge1.label != null && edgedata.edge1.label.includes('xx%')));
        $("#edgebpsshow").bootstrapSwitch('state', (edgedata.edge1.label != null && edgedata.edge1.label.includes('bps')));
        $("#edgelabel").val('label' in edgedata.mid ? edgedata.mid.label : '');
        $("#edgefixedwidth").val(edgedata.edge1.width);

        $("#edge-saveButton").on("click", {data: edgedata}, edgeSave);
        $("#edge-cancelButton").on("click", {data: edgedata}, edgeCancel);

        $('#edgeModal').modal({backdrop: 'static', keyboard: false}, 'show');
    }

    function edgePortSearchUpdate(node1_id, node2_id, edge_id) {
        node1 = network_nodes.get(node1_id);
        node2 = network_nodes.get(node2_id);

        if(! node1.device_id && ! node2.device_id) {
            // Neither node has a device - clear port config
            $("#port_id").val("");
            $("#edgePortRow").hide();
            $("#edgePortReverseRow").hide();
            $("#edgePortSearchRow").hide();
            $("#edgeDeviceSearchRow").show();
            return;
        }
        $("#edgeDeviceSearchRow").hide();
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

    function edgeCancel(event) {
        edgedata = event.data.data;
        node1_id = edgedata.edge1.from;
        node2_id = edgedata.edge2.from;
        nm_id = node1_id < node2_id ? node1_id + '.' + node2_id : node2_id + '.' + node1_id;
        edgeNodesRemove(nm_id, edgedata.id);

        $("#edge-saveButton").off("click");
    }

    function edgeDefaultsSave() {
        newedgeconf.smooth.type = $("#edgestyle").val();
        newedgeconf.font.face = $("#edgetextface").val();
        newedgeconf.font.size = $("#edgetextsize").val();
        newedgeconf.font.color = $("#edgetextcolour").val();
        newedgeconf.font.align = $("#edgetextalign").val();
        newedgeconf.label = edgeLabel($("#edgetextshow").prop('checked'), $("#edgebpsshow").prop('checked'), '');
        newedgeconf.width = parseFloat($("#edgefixedwidth").val()) || null;
        $("#map-saveDataButton").show();
    }

    $(document).ready(function () {
        init_select2('#portsearch', 'port', function(params) {
            return {
                limit: 100,
                devices: [port_search_device_id_1, port_search_device_id_2],
                term: params.term,
                page: params.page || 1
            }
        }, '', '{{ __('map.custom.edit.edge.port_select') }}', {dropdownParent: $('#edgeModal')});
        $("#portsearch").on("select2:select", edgePortSelect);

        init_select2('#edgedevicesearch', 'device', {limit: 100}, '', '{{ __('map.custom.edit.node.device_select') }}', {dropdownParent: $('#edgeModal')});
        $("#edgedevicesearch").on("select2:select", edgeDeviceSelect).on("select2:clear", edgeDeviceSelect);
   });
</script>
