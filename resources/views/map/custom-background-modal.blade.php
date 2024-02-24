<div class="modal fade" id="bgModal" tabindex="-1" role="dialog" aria-labelledby="bgModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bgModalLabel">{{ __('map.custom.edit.bg.title') }}</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="well well-lg">
                            <div class="form-group row" id="mapBackgroundRow">
                                <label for="selectbackground" class="col-sm-3 control-label">{{ __('map.custom.edit.bg.background') }}</label>
                                <div class="col-sm-9">
                                    <input id="mapBackgroundSelect" type="file" name="selectbackground" accept="image/png,image/jpeg,image/svg+xml,image/gif" class="form-control" onchange="mapChangeBackground();">
                                    <button id="mapBackgroundCancel" type="button" name="cancelbackground" class="btn btn-primary" onclick="mapChangeBackgroundCancel();" style="display:none">{{ __('Cancel') }}</button>
                                </div>
                            </div>
                            <div class="form-group row" id="mapBackgroundClearRow">
                                <label for="clearbackground" class="col-sm-3 control-label">{{ __('map.custom.edit.bg.clear_bg') }}</label>
                                <div class="col-sm-9">
                                    <input type="hidden" id="mapBackgroundClearVal">
                                    <button id="mapBackgroundClear" type="button" name="clearbackground" class="btn btn-primary" onclick="mapClearBackground();">{{ __('map.custom.edit.bg.clear_background') }}</button>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-12" id="savebg-alert">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <center>
                    <button type=button value="save" id="map-savebgButton" class="btn btn-primary" onclick="saveMapBackground()">{{ __('Save') }}</button>
                    <button type=button value="cancel" id="map-cancelbgButton" class="btn btn-primary" onclick="editMapBackgroundCancel()">{{ __('Cancel') }}</button>
                </center>
            </div>
        </div>
    </div>
</div>

<script>
    function mapChangeBackground() {
        $("#mapBackgroundCancel").show();
    }

    function mapChangeBackgroundCancel() {
        $("#mapBackgroundCancel").hide();
        $("#mapBackgroundSelect").val(null);
    }

    function mapClearBackground() {
        if($('#mapBackgroundClearVal').val()) {
            $('#mapBackgroundClear').text('{{ __('map.custom.edit.bg.clear_background') }}');
            $('#mapBackgroundClearVal').val('');
        } else {
            $('#mapBackgroundClear').text('{{ __('map.custom.edit.bg.keep_background') }}');
            $('#mapBackgroundClearVal').val('clear');
        }
    }

    function editMapBackgroundCancel() {
        $('#mapBackgroundClear').text('{{ __('map.custom.edit.bg.clear_background') }}');
        $('#mapBackgroundClearVal').val('');
        $("#mapBackgroundCancel").hide();
        $("#mapBackgroundSelect").val(null);
        $('#bgModal').modal('hide');
    }

    function saveMapBackground() {
        $("#map-savebgButton").attr('disabled','disabled');
        $("#savebg-alert").text('{{ __('map.custom.edit.bg.saving') }}');
        $("#savebg-alert").attr("class", "col-sm-12 alert alert-info");

        var clearbackground = $('#mapBackgroundClearVal').val() ? 1 : 0;
        var newbackground = $('#mapBackgroundSelect').prop('files').length ? $('#mapBackgroundSelect').prop('files')[0] : '';

        var url = '{{ route('maps.custom.background.save', ['map' => $map_id]) }}';
        var fd = new FormData();
        fd.append('bgclear', clearbackground);
        fd.append('bgimage', newbackground);

        $.ajax({
            url: url,
            data: fd,
            processData: false,
            contentType: false,
            type: 'POST'
        }).done(function (data, status, resp) {
            canvas = $("#custom-map").children()[0].canvas;
            if(data['bgimage']) {
                $(canvas).css('background-image','url({{ route('maps.custom.background', ['map' => $map_id]) }}?ver=' + data['bgversion'] + ')').css('background-size', 'cover');
                bgimage = true;
            } else {
                $(canvas).css('background-image','');
                bgimage = false;
            }
            $("#savebg-alert").attr("class", "col-sm-12");
            $("#savebg-alert").text("");
            editMapBackgroundCancel();
        }).fail(function (resp, status, error) {
            var data = resp.responseJSON;
            if (data['message']) {
                let alert_content = $("#savebg-alert");
                alert_content.text(data['message']);
                alert_content.attr("class", "col-sm-12 alert alert-danger");
            } else {
                let alert_content = $("#savebg-alert");
                alert_content.text('{{ __('map.custom.edit.bg.save_error', ['code' => '?']) }}'.replace('?', resp.status));
                alert_content.attr("class", "col-sm-12 alert alert-danger");
            }
        }).always(function (resp, status, error) {
            $("#map-savebgButton").removeAttr('disabled');
        });
    }
</script>
