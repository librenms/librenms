<div class="modal fade" id="mapModal" role="dialog" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapModalLabel">{{ __('map.custom.edit.map.settings_title') }}</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="well well-lg">
                            <input type="hidden" id="mapid" name="mapid" />
                            <div class="form-group row">
                                <label for="mapname" class="col-sm-3 control-label">{{ __('map.custom.edit.map.name') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" id="mapname" name="mapname" class="form-control input-sm">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="mapmenugroup" class="col-sm-3 control-label">{{ __('map.custom.edit.map.menu_group') }}</label>
                                <div class="col-sm-9">
                                    <select id="mapmenugroup" name="mapmenugroup" class="form-control input-sm"></select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="mapwidth" class="col-sm-3 control-label">{{ __('map.custom.edit.map.width') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" id="mapwidth" name="mapwidth" class="form-control input-sm">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="mapheight" class="col-sm-3 control-label">{{ __('map.custom.edit.map.height') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" id="mapheight" name="mapheight" class="form-control input-sm">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="mapnodealign" class="col-sm-3 control-label">{{ __('map.custom.edit.map.alignment') }}</label>
                                <div class="col-sm-9">
                                    <input type="number" id="mapnodealign" name="mapnodealign" class="form-control input-sm">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="mapedgesep" class="col-sm-3 control-label">{{ __('map.custom.edit.map.edgeseparation') }}</label>
                                <div class="col-sm-9">
                                    <input type="number" id="mapedgesep" name="mapedgesep" class="form-control input-sm">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="mapreversearrows" class="col-sm-3 control-label">{{ __('map.custom.edit.map.reverse') }}</label>
                                <div class="col-sm-9">
                                    <input class="form-check-input" type="checkbox" role="switch" id="mapreversearrows">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="maplegend" class="col-sm-3 control-label">{{ __('map.custom.edit.map.enable_legend') }}</label>
                                <div class="col-sm-9">
                                    <input class="form-check-input" type="checkbox" role="switch" id="maplegend" onChange="toggleMapLegend()">
                                </div>
                            </div>
                            <div class="form-group row maplegend">
                                <label for="maplegendfontsize" class="col-sm-4 control-label">{{ __('map.custom.edit.map.legend.font_size') }}</label>
                                <div class="col-sm-8">
                                    <input type=number id="maplegendfontsize" class="form-control input-sm" />
                                </div>
                            </div>
                            <div class="form-group row maplegend">
                                <label for="maplegendsteps" class="col-sm-4 control-label">{{ __('map.custom.edit.map.legend.steps') }}</label>
                                <div class="col-sm-8">
                                    <input type=number id="maplegendsteps" class="form-control input-sm" />
                                </div>
                            </div>
                            <div class="form-group row maplegend">
                                <label for="maplegendhideinvalid" class="col-sm-4 control-label">{{ __('map.custom.edit.map.legend.hideinvalid') }}</label>
                                <div class="col-sm-8">
                                    <input class="form-check-input" type="checkbox" role="switch" id="maplegendhideinvalid">
                                </div>
                            </div>
                            <div class="form-group row maplegend">
                                <label for="maplegendhideoverspeed" class="col-sm-4 control-label">{{ __('map.custom.edit.map.legend.hideoverspeed') }}</label>
                                <div class="col-sm-8">
                                    <input class="form-check-input" type="checkbox" role="switch" id="maplegendhideoverspeed">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="mapopt-zoom" class="col-sm-3 control-label">{{ __('map.custom.edit.map.zoom') }}</label>
                                <div class="col-sm-9">
                                    <input class="form-check-input" type="checkbox" role="switch" id="mapopt-zoom">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="mapopt-dragnodes" class="col-sm-3 control-label">{{ __('map.custom.edit.map.dragnodes') }}</label>
                                <div class="col-sm-9">
                                    <input class="form-check-input" type="checkbox" role="switch" id="mapopt-dragnodes">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="mapopt-physics" class="col-sm-3 control-label">{{ __('map.custom.edit.map.physics') }}</label>
                                <div class="col-sm-9">
                                    <input class="form-check-input" type="checkbox" role="switch" id="mapopt-physics">
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
                    <button type=button value="save" id="map-saveButton" class="btn btn-primary" onclick="saveMapSettings()">{{ __('Save') }}</button>
                    <button type=button value="cancel" id="map-cancelButton" class="btn btn-primary" onclick="editMapCancel()">{{ __('Cancel') }}</button>
                </center>
            </div>
        </div>
    </div>
</div>

<script>
    var node_align = {{$node_align}};
    var edge_sep = {{$edge_separation}};
    var reverse_arrows = {{$reverse_arrows}};
    var legend = @json($legend);
    var map_name = "{{ $name ?? '' }}";
    var map_node_align = {{ $node_align ?? 10 }};
    var map_edge_separation = {{ $edge_separation ?? 10 }};
    var map_options = {{ Js::from($map_options) }};

    function saveMapSettings() {
        $("#map-saveButton").attr('disabled','disabled');
        $("#savemap-alert").text('{{ __('map.custom.edit.map.saving') }}');
        $("#savemap-alert").attr("class", "col-sm-12 alert alert-info");

        var name = $("#mapname").val();
        var group = $("#mapmenugroup").val();
        var width = $("#mapwidth").val();
        var height = $("#mapheight").val();
        var node_align = $("#mapnodealign").val();
        var post_options = structuredClone(map_options);
        post_options.interaction.zoomView = post_options.interaction.dragView = $("#mapopt-zoom").prop('checked');
        post_options.interaction.dragNodes = $("#mapopt-dragnodes").prop('checked');
        post_options.physics.enabled = $("#mapopt-physics").prop('checked');

        var mapwdith = 100;
        if (!isNaN(width)) {
            mapwidth = width;
        } else if (width.includes("px")) {
            mapwidth = width.replace("px", "");
        } else if (width.includes("%")) {
            mapwidth = window.innerWidth * width.replace("%", "") / 100;
        }

        // Update the x and y coordinates
        if ($("#maplegend").prop('checked')) {
            if (legend.x < 0) {
                legend.x = mapwidth - 50;
                legend.y = 100;
            }
        } else {
            legend.x = -1;
            legend.y = -1;
        }

        legend.font_size = parseInt($("#maplegendfontsize").val());
        legend.steps = parseInt($("#maplegendsteps").val());
        legend.hide_invalid = $("#maplegendhideinvalid").prop('checked') ? 1 : 0;
        legend.hide_overspeed = $("#maplegendhideoverspeed").prop('checked') ? 1 : 0;

        var map_reverse_arrows = $("#mapreversearrows").prop('checked') ? 1 : 0;
        var map_edge_sep = $("#mapedgesep").val();

        if(!isNaN(width)) {
            width = width + "px";
        }
        if(!isNaN(height)) {
            height = height + "px";
        }

        @if(isset($map_id))
            var url = '{{ route('maps.custom.update', ['map' => $map_id]) }}';
            var method = 'PUT';
        @else
            var url = '{{ route('maps.custom.store') }}';
            var method = 'POST';
        @endif

        $.ajax({
            url: url,
            data: {
                name: name,
                menu_group: group,
                width: width,
                height: height,
                node_align: node_align,
                reverse_arrows: map_reverse_arrows,
                edge_separation: map_edge_sep,
                legend_x: legend.x,
                legend_y: legend.y,
                legend_steps: legend.steps,
                legend_font_size: legend.font_size,
                legend_hide_invalid: legend.hide_invalid,
                legend_hide_overspeed: legend.hide_overspeed,
                options: JSON.stringify(post_options),
            },
            dataType: 'json',
            type: method
        }).done(function (data, status, resp) {
            editMapSuccess(data);
        }).fail(function (resp, status, error) {
            var data = resp.responseJSON;
            if (data['message']) {
                let alert_content = $("#savemap-alert");
                alert_content.text(data['message']);
                alert_content.attr("class", "col-sm-12 alert alert-danger");
            } else {
                let alert_content = $("#savemap-alert");
                alert_content.text('{{ __('map.custom.edit.map.save_error', ['code' => '?']) }}'.replace('?', resp.status));
                alert_content.attr("class", "col-sm-12 alert alert-danger");
            }
        }).always(function (resp, status, error) {
            $("#map-saveButton").removeAttr('disabled');
        });
    }

    function toggleMapLegend() {
        if($("#maplegend").prop('checked')) {
            $(".maplegend").show();
        } else {
            $(".maplegend").hide();
        }
    }

    function mapSettingsReset() {
        $("#mapreversearrows").bootstrapSwitch('state', Boolean(reverse_arrows));
        $("#maplegend").bootstrapSwitch('state', (legend.x >= 0 && legend.y >= 0));
        $("#maplegendhideinvalid").bootstrapSwitch('state', Boolean(legend.hide_invalid));
        $("#maplegendhideoverspeed").bootstrapSwitch('state', Boolean(legend.hide_overspeed));
        $("#mapopt-zoom").bootstrapSwitch('state', Boolean(map_options.interaction.zoomView));
        $("#mapopt-dragnodes").bootstrapSwitch('state', Boolean(map_options.interaction.dragNodes));
        $("#mapopt-physics").bootstrapSwitch('state', Boolean(map_options.physics.enabled));
        if(legend.x < 0 || legend.y < 0) {
            $(".maplegend").hide();
        } else {
            $(".maplegend").show();
        }
        $("#mapname").val(map_name);
        $("#mapwidth").val(network_options.width);
        $("#mapheight").val(network_options.height);
        $("#mapnodealign").val(map_node_align);
        $("#mapedgesep").val(map_edge_separation);
        $("#maplegendfontsize").val(legend.font_size);
        $("#maplegendsteps").val(legend.steps);
    }

    $(document).ready(function () {
        mapSettingsReset();
        init_select2("#mapmenugroup", "custom-map-menu-group", {}, @json($menu_group ?? null), "{{ __('map.custom.edit.map.no_group') }}", {
            tags: true,
            createTag: function (params) {
                var term = $.trim(params.term);

                if (term === '') {
                    return null;
                }

                return {
                    id: term,
                    text: term
                };
            }
        });
    });
</script>
