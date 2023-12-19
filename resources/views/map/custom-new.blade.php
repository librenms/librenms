@extends('layouts.librenmsv1')

@section('title', __('Create New Custom Map'))

@section('content')
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
@endsection

@section('javascript')
@endsection

@section('scripts')
<script type="text/javascript">
    var bgimage = {{ $background ? "true" : "false" }};
    var network_height;
    var network_width;

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
                    window.location.href = "{{ @route('maps.custom.edit') }}/" + data['id'];
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
    // Pop up the modal to set initial settings
    $('#mapModal').modal({backdrop: 'static', keyboard: false}, 'show');
    $("#mapBackgroundClearRow").hide();

    function editMapCancel() {
        window.location.href = "{{ route("maps.custom.edit") }}/";
    }
</script>
@endsection

