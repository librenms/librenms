<div class="modal fade" id="nodeModal" tabindex="-1" role="dialog" aria-labelledby="nodeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nodeModalLabel">{{ __('map.custom.edit.node.new') }}</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="well well-lg">
                            <div class="form-group row single-node" id="nodeDeviceSearchRow">
                                <label for="devicesearch" class="col-sm-3 control-label">{{ __('map.custom.edit.node.device_select') }}</label>
                                <div class="col-sm-9">
                                    <select name="devicesearch" id="devicesearch" class="form-control"></select>
                                </div>
                            </div>
                            <div class="form-group row single-node" id="nodeDeviceRow" style="display:none">
                                <label for="deviceclear" class="col-sm-3 control-label">{{ __('Device') }}</label>
                                <div class="col-sm-7">
                                    <div id="device_name">
                                    </div>
                                    <input type="hidden" id="device_id">
                                </div>
                                <div class="col-sm-2">
                                    <button type=button class="btn btn-primary" value="save" id="deviceclear" onclick="nodeDeviceClear();">{{ __('Clear') }}</button>
                                </div>
                            </div>
                            <div class="form-group row single-node" id="nodeDeviceLabelRow">
                                <label for="nodelabel" class="col-sm-3 control-label">{{ __('map.custom.edit.node.label') }}</label>
                                <div class="col-sm-9">
                                    <input type=text id="nodelabel" class="form-control input-sm" value="Node Name" />
                                </div>
                            </div>
                            <div class="form-group row single-node" id="nodeMapLinkRow">
                                <label for="maplink" class="col-sm-3 control-label">{{ __('map.custom.edit.node.map_link') }}</label>
                                <div class="col-sm-9">
                                    <select name="maplink" id="maplink" class="form-control">
                                        <option value="" style="color:#999;">{{ __('map.custom.edit.node.map_select') }}</option>
                                        @foreach($maps as $map)
                                            <option value="{{$map->custom_map_id}}">{{$map->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="nodestyle" class="col-sm-3 control-label">{{ __('map.custom.edit.node.style') }}</label>
                                <div class="col-sm-9">
                                    <select id="nodestyle" class="form-control input-sm" onchange="nodeStyleChange();">
                                        <option value="box">{{ __('map.custom.edit.node.style_options.box') }}</option>
                                        <option value="circle">{{ __('map.custom.edit.node.style_options.circle') }}</option>
                                        <option value="database">{{ __('map.custom.edit.node.style_options.database') }}</option>
                                        <option value="ellipse">{{ __('map.custom.edit.node.style_options.ellipse') }}</option>
                                        <option value="text">{{ __('map.custom.edit.node.style_options.text') }}</option>
                                        <option value="image">{{ __('map.custom.edit.node.style_options.device_image') }}</option>
                                        <option value="circularImage">{{ __('map.custom.edit.node.style_options.device_image_circle') }}</option>
                                        <option value="diamond">{{ __('map.custom.edit.node.style_options.diamond') }}</option>
                                        <option value="dot">{{ __('map.custom.edit.node.style_options.dot') }}</option>
                                        <option value="star">{{ __('map.custom.edit.node.style_options.star') }}</option>
                                        <option value="triangle">{{ __('map.custom.edit.node.style_options.triangle') }}</option>
                                        <option value="triangleDown">{{ __('map.custom.edit.node.style_options.triangle_inverted') }}</option>
                                        <option value="hexagon">{{ __('map.custom.edit.node.style_options.hexagon') }}</option>
                                        <option value="square">{{ __('map.custom.edit.node.style_options.square') }}</option>
                                        <option value="icon">{{ __('map.custom.edit.node.style_options.icon') }}</option>
                                    </select>
                                    <input type="hidden" id="device_image">
                                </div>
                            </div>
                            <div class="form-group row" id="nodeImageRow">
                                <label for="nodeimage" class="col-sm-3 control-label">{{ __('map.custom.edit.node.image') }}</label>
                                <div class="col-sm-6">
                                    <select id="nodeimage" class="form-control input-sm" onchange="nodeSetImage();">
                                        <option value="" id="deviceiconimage">{{ __('map.custom.edit.node.style_options.device_image') }}</option>
                                        @foreach($images as $imgfile => $imglabel)
                                            <option value="{{$imgfile}}">{{$imglabel}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                    <img id="nodeimagepreview" width=28 height=28>
                                </div>
                            </div>
                            <div class="form-group row" id="nodeIconRow">
                                <label for="nodeicon" class="col-sm-3 control-label">{{ __('map.custom.edit.node.icon') }}</label>
                                <div class="col-sm-6">
                                    <select id="nodeicon" class="form-control input-sm" onchange="nodeSetIcon();">
                                        <option value="f233">{{ __('map.custom.edit.node.icon_options.server')  }}</option>
                                        <option value="f390">{{ __('map.custom.edit.node.icon_options.desktop')  }}</option>
                                        <option value="f7c0">{{ __('map.custom.edit.node.icon_options.dish')  }}</option>
                                        <option value="f7bf">{{ __('map.custom.edit.node.icon_options.satellite')  }}</option>
                                        <option value="f1eb">{{ __('map.custom.edit.node.icon_options.wifi')  }}</option>
                                        <option value="f0c2">{{ __('map.custom.edit.node.icon_options.cloud')  }}</option>
                                        <option value="f0ac">{{ __('map.custom.edit.node.icon_options.globe')  }}</option>
                                        <option value="f519">{{ __('map.custom.edit.node.icon_options.tower')  }}</option>
                                        <option value="f061">{{ __('map.custom.edit.node.icon_options.arrow_right')  }}</option>
                                        <option value="f060">{{ __('map.custom.edit.node.icon_options.arrow_left')  }}</option>
                                        <option value="f062">{{ __('map.custom.edit.node.icon_options.arrow_up')  }}</option>
                                        <option value="f063">{{ __('map.custom.edit.node.icon_options.arrow_down')  }}</option>
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                    <i class="fa" id="nodeiconpreview">&#xf233</i>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="nodesize" class="col-sm-3 control-label">{{ __('map.custom.edit.node.size') }}</label>
                                <div class="col-sm-9">
                                    <input type=number id="nodesize" class="form-control input-sm" value=50 />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="nodetextface" class="col-sm-3 control-label">{{ __('map.custom.edit.text_font') }}</label>
                                <div class="col-sm-9">
                                    <input type=text id="nodetextface" class="form-control input-sm" value="arial" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="nodetextsize" class="col-sm-3 control-label">{{ __('map.custom.edit.text_size') }}</label>
                                <div class="col-sm-9">
                                    <input type=number id="nodetextsize" class="form-control input-sm" value=14 />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="nodetextcolour" class="col-sm-3 control-label">{{ __('map.custom.edit.text_color') }}</label>
                                <div class="col-sm-2">
                                    <input type=color id="nodetextcolour" class="form-control input-sm" value="#343434" onchange="$('#nodecolourtextreset').removeAttr('disabled');" />
                                </div>
                                <div class="col-sm-5">
                                </div>
                                <div class="col-sm-2">
                                    <button type=button class="btn btn-primary" value="reset" id="nodecolourtextreset" onclick="$('#nodetextcolour').val(newnodeconf.font.color); $(this).attr('disabled','disabled');">{{ __('Reset') }}</button>
                                </div>
                            </div>
                            <div class="form-group row" id="nodeColourBgRow">
                                <label for="nodecolourbg" class="col-sm-3 control-label">{{ __('map.custom.edit.node.bg_color') }}</label>
                                <div class="col-sm-2">
                                    <input type=color id="nodecolourbg" class="form-control input-sm" value="#343434" onchange="$('#nodecolourbgreset').removeAttr('disabled');" />
                                </div>
                                <div class="col-sm-5">
                                </div>
                                <div class="col-sm-2">
                                    <button type=button class="btn btn-primary" value="reset" id="nodecolourbgreset" onclick="$('#nodecolourbg').val(newnodeconf.color.background); $(this).attr('disabled','disabled');">{{ __('Reset') }}</button>
                                </div>
                            </div>
                            <div class="form-group row" id="nodeColourBdrRow">
                                <label for="nodecolourbdr" class="col-sm-3 control-label">{{ __('map.custom.edit.node.border_color') }}</label>
                                <div class="col-sm-2">
                                    <input type=color id="nodecolourbdr" class="form-control input-sm" value="#343434" onchange="$('#nodecolourbdrreset').removeAttr('disabled');" />
                                </div>
                                <div class="col-sm-5">
                                </div>
                                <div class="col-sm-2">
                                    <button type=button class="btn btn-primary" value="reset" id="nodecolourbdrreset" onclick="$('#nodecolourbdr').val(newnodeconf.color.border); $(this).attr('disabled','disabled');">{{ __('Reset') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <center>
                    <button type=button class="btn btn-primary" value="savedefaults" id="node-saveDefaultsButton" data-dismiss="modal" style="display:none" onclick="nodeDefaultsSave();">{{ __('map.custom.edit.defaults') }}</button>
                    <button type=button class="btn btn-primary" value="save" id="node-saveButton" data-dismiss="modal">{{ __('Save') }}</button>
                    <button type=button class="btn btn-primary" value="cancel" id="node-cancelButton" data-dismiss="modal" onclick="nodeCancel();">{{ __('Cancel') }}</button>
                </center>
            </div>
        </div>
    </div>
</div>

<script>
    function nodeCheckColourReset(itemColour, defaultColour, resetControlId) {
        if(!itemColour || itemColour.toLowerCase() == defaultColour.toLowerCase()) {
            $("#" + resetControlId).attr('disabled','disabled');
        } else {
            $("#" + resetControlId).removeAttr('disabled');
        }
    }

    function nodeSetIcon() {
        var newcode = $("#nodeicon").val();
        $("#nodeiconpreview").text(String.fromCharCode(parseInt(newcode, 16)));
    }

    function nodeSetImage() {
        // If the selected option is not visible, select the top option
        if($("#nodeimage option:selected").css('display') == 'none') {
            $("#nodeimage").val($("#nodeimage option:eq(1)").val());
        }
        let imgsrc = $("#nodeimage").val();
        // Set the image preview src
        if(! imgsrc) {
            $("#nodeimagepreview").attr("src", $("#device_image").val());
        } else if (isNaN(imgsrc)) {
            $("#nodeimagepreview").attr("src", custom_image_base + imgsrc);
        } else {
            $("#nodeimagepreview").attr("src", '{{ route('maps.nodeimage.show', ['image' => '?' ]) }}'.replace("?", imgsrc));
        }
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
        if (! $("#maplink").val()) {
            // Update the node label if we are not linked to a map
            $("#nodelabel").val(name.split(".")[0].split(" ")[0]);
        }
        $("#device_image").val(e.params.data.icon);
        $("#nodeDeviceSearchRow").hide();
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

        // Reset device style if we were using the device image
        if(($("#nodestyle").val() == "image" || $("#nodestyle").val() == "circularImage") && !$("#nodeimage").val()){
            $("#nodestyle").val(newnodeconf.shape);
            $("#nodeImageRow").hide();
            nodeSetImage();
        }
    }

    function nodeCancel(event) {
        $("#node-saveButton").off("click");
    }

    function nodeSave(event) {
        node = event.data.data;

        $("#node-saveButton").off("click");

        if($("#maplink").val()) {
            node.title = "Link to map " + $("#maplink").val();
        } else if($("#device_id").val()) {
            node.title = "Device " + $("#device_id").val();
        } else {
            node.title = '';
        }
        // Update the node with the selected values on success and run the callback
        node.device_id = $("#device_id").val();
        node.linked_map_id = $("#maplink").val();
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
            let imgsrc = $("#nodeimage").val();
            if(! imgsrc) {
                node.image = {unselected: $("#device_image").val()};
            } else if(isNaN(imgsrc)) {
                node.image = {unselected: custom_image_base + imgsrc};
            } else {
                node.image = {unselected: '{{ route('maps.nodeimage.show', ['image' => '?' ]) }}'.replace("?", imgsrc)};
            }
        } else {
            node.image = undefined;
        }
        if(node.shape == "icon") {
            node.icon = {face: 'FontAwesome', code: String.fromCharCode(parseInt($("#nodeicon").val(), 16)), size: $("#nodesize").val(), color: node.color.border};
        } else {
            node.icon = {};
        }
        if(! ["ellipse", "circle", "database", "box", "text"].includes(node.style)) {
            node.font.background = "#FFFFFF";
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

    function nodeEdit(nodeconf) {
        if (!nodeconf.id) {
            $("#nodeModalLabel").text('{{ __('map.custom.edit.node.defaults_title') }}');
            $(".single-node").hide();
        } else if(nodeconf.add) {
            $("#nodeModalLabel").text('{{ __('map.custom.edit.node.add') }}');
            $(".single-node").show();
        } else {
            $("#nodeModalLabel").text('{{ __('map.custom.edit.node.edit') }}');
            $(".single-node").show();
        }

        $("#devicesearch").val('');
        $("#devicesearch").trigger('change');

        $("#device_id").val(nodeconf.device_id || '');
        if(nodeconf.device_id) {
            // Nodes is linked to a device
            $("#device_name").text(node_device_map[nodeconf.id].device_name);
            // Hide device selection row
            $("#nodeDeviceSearchRow").hide();
            // Show device image as an option
            $("#deviceiconimage").show();
            $("#device_image").val(node_device_map[nodeconf.id].device_image);
        } else {
            // Node is not linked to a device
            $("#device_name").text("");
            // Hide the selected device row
            $("#nodeDeviceRow").hide();
            // Hide device image as an option
            $("#deviceiconimage").hide();
            $("#device_image").val("");
        }
        if(nodeconf.linked_map_id) {
            // Hide device selection row
            $("#maplink").val(nodeconf.linked_map_id);
        } else {
            $("#maplink").val("");
        }
        $("#nodelabel").val(nodeconf.label);
        $("#nodestyle").val(nodeconf.shape);

        // Show or hide the image selection if the shape is an image type
        if(nodeconf.shape == "image" || nodeconf.shape == "circularImage") {
            $("#nodeImageRow").show();
            if(nodeconf.image.unselected.indexOf(custom_image_base) == 0) {
                $("#nodeimage").val(nodeconf.image.unselected.replace(custom_image_base, ""));
            } else if(nodeconf.image.unselected.indexOf(nodeimage_base) == 0) {
                $("#nodeimage").val(nodeconf.image.unselected.replace(nodeimage_base, ""));
            } else {
                $("#nodeimage").val("");
            }
        } else {
            $("#nodeImageRow").hide();
            $("#nodeimage").val("");
        }
        nodeSetImage();

        // Show or hide the icon selection if the shape is icon
        if(nodeconf.shape == "icon") {
            $("#nodeicon").val(nodeconf.icon.code.charCodeAt(0).toString(16));
            $("#nodeIconRow").show();
        } else {
            $("#nodeIconRow").hide();
        }
        nodeSetIcon();

        $("#nodesize").val(nodeconf.size);
        $("#nodetextface").val(nodeconf.font.face);
        $("#nodetextsize").val(nodeconf.font.size);
        $("#nodetextcolour").val(nodeconf.font.color);
        if(nodeconf.color && nodeconf.color.background) {
            $("#nodecolourbg").val(nodeconf.color.background);
            $("#nodecolourbdr").val(nodeconf.color.border);
        } else {
            // The background colour is blank because a device has been selected - start with defaults
            $("#nodecolourbg").val(newnodeconf.color.background);
            $("#nodecolourbdr").val(newnodeconf.color.border);
        }

        nodeCheckColourReset(nodeconf.font.color, newnodeconf.font.color, "nodecolourtextreset");
        nodeCheckColourReset(nodeconf.color.background, newnodeconf.color.background, "nodecolourbgreset");
        nodeCheckColourReset(nodeconf.color.border, newnodeconf.color.border, "nodecolourbdrreset");

        if(nodeconf.id) {
            $("#node-saveButton").on("click", {data: nodeconf}, nodeSave);
            $("#node-saveButton").show();
            $("#node-saveDefaultsButton").hide();
        } else {
            $("#node-saveButton").hide();
            $("#node-saveDefaultsButton").show();
        }
        $('#nodeModal').modal({backdrop: 'static', keyboard: false}, 'show');
    }

    function nodeDefaultsSave() {
        newnodeconf.shape = $("#nodestyle").val();
        newnodeconf.size = $("#nodesize").val();
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
            let imgsrc = $("#nodeimage").val();
            if(isNaN(imgsrc)) {
                newnodeconf.image = {unselected: custom_image_base + imgsrc};
            } else {
                newnodeconf.image = {unselected: '{{ route('maps.nodeimage.show', ['image' => '?' ]) }}'.replace("?", imgsrc)};
            }
        } else {
            delete newnodeconf.image;
        }
        $("#map-saveDataButton").show();
    }

    $(document).ready(function () {
        init_select2('#devicesearch', 'device', {limit: 100}, '', '{{ __('map.custom.edit.node.device_select') }}', {dropdownParent: $('#nodeModal')});
        $("#devicesearch").on("select2:select", nodeDeviceSelect);
    });
</script>
