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
                                    <select name="maplink" id="maplink" class="form-control" onchange="nodeMapLinkChange();">
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
                                    <select id="nodeimage" class="form-control input-sm" onchange="setNodeImage();">
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
                                    <select id="nodeicon" class="form-control input-sm" onchange="setNodeIcon();">
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
                    <button type=button class="btn btn-primary" value="savedefaults" id="node-saveDefaultsButton" data-dismiss="modal" style="display:none" onclick="editNodeDefaultsSave();">{{ __('map.custom.edit.defaults') }}</button>
                    <button type=button class="btn btn-primary" value="save" id="node-saveButton" data-dismiss="modal">{{ __('Save') }}</button>
                    <button type=button class="btn btn-primary" value="cancel" id="node-cancelButton" data-dismiss="modal" onclick="editNodeCancel();">{{ __('Cancel') }}</button>
                </center>
            </div>
        </div>
    </div>
</div>
