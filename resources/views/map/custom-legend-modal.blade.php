<div class="modal fade" id="mapLegendModal" role="dialog" aria-labelledby="mapLegendModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapLegendModalLabel">{{ __('map.custom.edit.map.legend_title') }}</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="well well-lg">
                            <div class="form-group row">
                                <label for="maplegendfontsize" class="col-sm-4 control-label">{{ __('map.custom.edit.map.legend.font_size') }}</label>
                                <div class="col-sm-8">
                                    <input type=number id="maplegendfontsize" class="form-control input-sm" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="maplegendsteps" class="col-sm-4 control-label">{{ __('map.custom.edit.map.legend.steps') }}</label>
                                <div class="col-sm-8">
                                    <input type=number id="maplegendsteps" class="form-control input-sm" onChange=mapLegendChangeSteps() />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="maplegendhideinvalid" class="col-sm-4 control-label">{{ __('map.custom.edit.map.legend.hideinvalid') }}</label>
                                <div class="col-sm-8">
                                    <input class="form-check-input" type="checkbox" role="switch" id="maplegendhideinvalid">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="maplegendhideoverspeed" class="col-sm-4 control-label">{{ __('map.custom.edit.map.legend.hideoverspeed') }}</label>
                                <div class="col-sm-8">
                                    <input class="form-check-input" type="checkbox" role="switch" id="maplegendhideoverspeed">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="maplegendcustomcolours" class="col-sm-4 control-label">{{ __('map.custom.edit.map.legend.customcolours') }}</label>
                                <div class="col-sm-8">
                                    <input class="form-check-input" type="checkbox" role="switch" id="maplegendcustomcolours" onChange="mapLegendCustomColourToggle();">
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row customcolours">
                                <label for="maplegendcolourinvalid" class="col-sm-8 control-label">{{ __('map.custom.edit.map.legend.colour_invalid') }}</label>
                                <div class="col-sm-2">
                                    <input type=color class="form-control input-sm" id="maplegendcolourinvalid">
                                </div>
                                <div class="col-sm-2">
                                </div>
                            </div>
                            <div class="form-group row customcolours">
                                <label for="maplegendcolourdown" class="col-sm-8 control-label">{{ __('map.custom.edit.map.legend.colour_down') }}</label>
                                <div class="col-sm-2">
                                    <input type=color class="form-control input-sm" id="maplegendcolourdown">
                                </div>
                                <div class="col-sm-2">
                                </div>
                            </div>
                            <div class="form-group row customcolours" id="customcolourrow0">
                                <label for="maplegendcolourpct0" class="col-sm-4 control-label">{{ __('map.custom.edit.map.legend.colour_lower_pct') }} 1</label>
                                <div class="col-sm-2">
                                    <input type=number disabled class="form-control input-sm" id="maplegendcolourpct0" value="0">
                                </div>
                                <label for="maplegendcolour0" class="col-sm-2 control-label">{{ __('map.custom.edit.map.legend.colour') }} 1</label>
                                <div class="col-sm-2">
                                    <input type=color class="form-control input-sm" id="maplegendcolour0" value="#00FF00">
                                </div>
                                <div class="col-sm-4">
                                </div>
                            </div>
                            <div class="form-group row customcolours">
                                <div class="col-md-12" id="maplegendcolours">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <center>
                    <button type=button value="save" id="mapLegend-saveButton" class="btn btn-primary" onclick="saveMapLegend()">{{ __('Save') }}</button>
                    <button type=button value="cancel" id="mapLegend-cancelButton" class="btn btn-primary" onclick="cancelMapLegend()">{{ __('Cancel') }}</button>
                </center>
            </div>
        </div>
    </div>
</div>

<script>
    var legend = @json($legend);

    function saveMapLegend() {
        legend.font_size = parseInt($("#maplegendfontsize").val());
        legend.steps = parseInt($("#maplegendsteps").val());
        legend.hide_invalid = $("#maplegendhideinvalid").prop('checked') ? 1 : 0;
        legend.hide_overspeed = $("#maplegendhideoverspeed").prop('checked') ? 1 : 0;
        if($("#maplegendcustomcolours").prop('checked')) {
            legend.colours = {};
            legend.colours["-1"] = $("#maplegendcolourinvalid").val();
            legend.colours["-2"] = $("#maplegendcolourdown").val();
            if(legend.steps > 0) {
                for(let i = 0; i < legend.steps; i++) {
                    let this_pct = parseFloat($("#maplegendcolourpct" + i).val());
                    let this_colour = $("#maplegendcolour" + i).val();
                    if(!isNaN(this_pct) && this_colour) {
                        legend.colours[this_pct] = this_colour;
                    }
                }
            }
        } else {
            legend.colours = null;
        }

        redrawLegend();
        mapLegendResetColours();
        $("#mapLegendModal").modal('hide');
        $("#map-saveDataButton").show();
    }

    function cancelMapLegend() {
        mapLegendSettingsReset();

        $("#mapLegendModal").modal('hide');
    }

    function mapLegendDefaultColours() {
        let ret = {"-1": '#000000', "-2": '#8B0000'};
        for(let i = 0; i < legend.steps; i++) {
            let this_node = network_nodes.get("legend_" + i);
            if(this_node) {
                let pct = parseInt(this_node.label.replace('%', ''));
                ret[pct] = this_node.color.background;
            } else {
                ret[maxpct+=10] = "#000000";
            }
        }
        return ret;
    }

    function mapLegendAddColour(rownum, pct, colour) {
        $("#maplegendcolours").append('' +
'                            <div class="form-group row customcolours" id="customcolourrow' + rownum + '">' +
'                                <label for="maplegendcolourpct' + rownum + '" class="col-sm-4 control-label">{{ __('map.custom.edit.map.legend.colour_lower_pct') }} ' + (rownum + 1) + '</label>' +
'                                <div class="col-sm-2">' +
'                                    <input type=number step="0.01" class="form-control input-sm" id="maplegendcolourpct' + rownum + '" value="' + pct + '">' +
'                                </div>' +
'                                <label for="maplegendcolour' + rownum + '" class="col-sm-2 control-label">{{ __('map.custom.edit.map.legend.colour') }} ' + (rownum + 1) + '</label>' +
'                                <div class="col-sm-2">' +
'                                    <input type=color class="form-control input-sm" id="maplegendcolour' + rownum + '" value="' + colour + '">' +
'                                </div>' +
'                                <div class="col-sm-4">' +
'                                </div>' +
'                            </div>' +
        '');
    }

    function mapLegendResetColours() {
        let colours = legend.colours || mapLegendDefaultColours();
        $("#maplegendcolours").html('');

        $("#maplegendcolourinvalid").val(colours["-1"]);
        $("#maplegendcolourdown").val(colours["-2"]);
        $("#maplegendcolour0").val(colours["0"]);
        let colournum = 1;
        let max_pct = 0;
        Object.keys(colours).sort((a,b) => parseInt(a) > parseInt(b)).forEach((pct_key) => {
            let pct = parseFloat(pct_key);
            if(!isNaN(pct) && pct > 0.0) {
                mapLegendAddColour(colournum++, pct, colours[pct_key]);
                max_pct = pct;
            }
        });
        while(colournum < legend.steps) {
            mapLegendAddColour(colournum++, max_pct+=10, "#000000");
        }
    }

    function mapLegendChangeSteps() {
        let steps = parseInt($("#maplegendsteps").val());
        if(steps < 1) {
            $("#maplegendsteps").val(1);
            steps = 1;
        }

        let i = 0;
        let maxpct = 0;
        for(i = 1; i < steps; i++) {
            // First check if there is an existing row
            let this_pct = $("#maplegendcolourpct" + i);
            if(this_pct.length) {
                maxpct = parseFloat(this_pct.val());
            } else {
                // Next check if we are re-adding a row that was removed without saving
                let this_node = network_nodes.get("legend_" + i);
                if(this_node) {
                    let pct = parseFloat(this_node.label.replace('%', ''));
                    mapLegendAddColour(i, pct, this_node.color.background);
                    maxpct = pct;
                } else {
                    // Default to percentage based colour
                    maxpct+=10;
                    mapLegendAddColour(i, maxpct, legendPctColour(maxpct));
                }
            }
        }

        // Remove any rows that are not needed any more
        let this_row = $("#customcolourrow" + i);
        while(this_row.length) {
            this_row.remove();
            this_row = $("#customcolourrow" + ++i);
        }
    }

    function mapLegendSettingsReset() {
        $("#maplegendcustomcolours").bootstrapSwitch('state', Boolean(legend.colours));
        $("#maplegendhideinvalid").bootstrapSwitch('state', Boolean(legend.hide_invalid));
        $("#maplegendhideoverspeed").bootstrapSwitch('state', Boolean(legend.hide_overspeed));
        $("#maplegendfontsize").val(legend.font_size);
        $("#maplegendsteps").val(legend.steps);

        if(legend.colours) {
            mapLegendResetColours();
            $(".customcolours").show();
        } else {
            $(".customcolours").hide();
        }
    }

    function mapLegendCustomColourToggle() {
        if($("#maplegendcustomcolours").prop('checked')) {
            if($("#maplegendcolours").children().length == 0) {
                mapLegendResetColours();
            }
            $(".customcolours").show();
        } else {
            $(".customcolours").hide();
        }
    }

    $(document).ready(function () {
        mapLegendSettingsReset();
    });
</script>
