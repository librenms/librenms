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
                            <div class="form-group row" id="divEdgeFrom">
                                <label for="edgefrom" class="col-sm-3 control-label">{{ __('map.custom.edit.edge.from') }}</label>
                                <div class="col-sm-9">
                                    <select id="edgefrom" class="form-control input-sm">
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row" id="divEdgeTo">
                                <label for="edgeto" class="col-sm-3 control-label">{{ __('map.custom.edit.edge.to') }}</label>
                                <div class="col-sm-9">
                                    <select id="edgeto" class="form-control input-sm">
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row single-node" id="edgePortSearchRow" style="display:none">
                                <label for="portsearch" class="col-sm-3 control-label">{{ __('map.custom.edit.edge.port_select') }}</label>
                                <div class="col-sm-9">
                                    <select name="portsearch" id="portsearch" class="form-control"></select>
                                </div>
                            </div>
                            <div class="form-group row" id="edgePortRow" style="display:none">
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
                            <div class="form-group row" id="edgePortReverseRow" style="display:none">
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
                            <div class="form-group row single-node">
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
                            <div class="form-group row">
                                <label for="edgelabel" class="col-sm-3 control-label">{{ __('map.custom.edit.edge.label') }}</label>
                                <div class="col-sm-9">
                                    <input type=text id="edgelabel" class="form-control input-sm" value="" />
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
                            <div class="form-group row" id="edgeRecenterRow">
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
                    <button type=button class="btn btn-primary" value="savedefaults" id="edge-saveDefaultsButton" data-dismiss="modal" style="display:none" onclick="editEdgeDefaultsSave();">{{ __('map.custom.edit.defaults') }}</button>
                    <button type=button class="btn btn-primary" value="save" id="edge-saveButton" data-dismiss="modal">{{ __('Save') }}</button>
                    <button type=button class="btn btn-primary" value="cancel" id="edge-cancelButton" data-dismiss="modal" onclick="editEdgeCancel();">{{ __('Cancel') }}</button>
                </center>
            </div>
        </div>
    </div>
</div>
