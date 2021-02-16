<div class="form-group @if($errors->has('name')) has-error @endif">
    <label for="name" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Name')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $service->name) }}">
        <span class="help-block">{{ $errors->first('name') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('device')) has-error @endif">
    <label for="device" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Select Device')</label>
    <div class="col-sm-9 col-md-10">
        <select class="form-control" id="device" name="device">
            @foreach($device as $device)
                <option value="{{ $device->device_id }}" selected>{{ $device->displayName() }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ $errors->first('device') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('type')) has-error @endif">
    <label for="type" class="control-label col-sm-3 col-md-2">@lang('Check Type')</label>
    <div class="col-sm-9 col-md-10">
        <select class="form-control" id="type" name="type">
            @foreach($services as $current_service)
                <option value="{{ $current_service }}" @if($current_service == $service->type) selected @endif>{{ $current_service }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ $errors->first('type') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('desc')) has-error @endif">
    <label for="desc" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Description')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="desc" name="desc" value="{{ old('desc', $service->desc) }}">
        <span class="help-block">{{ $errors->first('desc') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('ip')) has-error @endif">
    <label for="ip" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Remote Host')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="ip" name="ip" value="{{ old('ip', $service->ip) }}">
        <span class="help-block">{{ $errors->first('ip') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('param')) has-error @endif">
    <label for="param" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Parameters')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="param" name="param" value="{{ old('param', $service->param) }}">
        <span class="help-block">{{ $errors->first('param') }}</span>
    </div>
</div>

<div class="form-group row">
    <div class="col-sm-12 alert alert-info">
        <label class='control-label text-left input-sm'>Parameters may be required and will be different depending on the service check.</label>
    </div>
</div>

<div class="form-group @if($errors->has('ignore')) has-error @endif">
    <label for="ignore" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Ignore alert tag')</label>
    <div class="col-sm-9 col-md-10">
        <input type="hidden" value="0" name="ignore">
        <input type="checkbox" class="form-control" id="ignore" name="ignore" data-size="small" value="{{ old('ignore', $service->ignore) }}"@if(old('ignore', $service->ignore) == 1) checked @endif>
        <span class="help-block">{{ $errors->first('ignore') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('disabled')) has-error @endif">
    <label for="disabled" class="control-label col-sm-3 col-md-2 text-wrap">@lang('Disable polling and alerting')</label>
    <div class="col-sm-9 col-md-10">
        <input type="hidden" value="0" name="disabled">
        <input type="checkbox" class="form-control" id="disabled" name="disabled" data-size="small" value="{{ old('disabled', $service->disabled) }}"@if(old('disabled', $service->disabled) == 1) checked @endif>
        <span class="help-block">{{ $errors->first('disabled') }}</span>
    </div>
</div>

<script>
    $("[type='checkbox']").bootstrapSwitch('offColor','danger');
    $("#ignore").on( 'switchChange.bootstrapSwitch', function (e, state) {
        var value = $(this).is(':checked') ? "1": "0";
        $('#ignore').val(value);
    });
    $("#disabled").on( 'switchChange.bootstrapSwitch', function (e, state) {
        var value = $(this).is(':checked') ? "1": "0";
        $('#disabled').val(value);
    });

    init_select2('#devices', 'device', {multiple: false});
    $('.service-form').submit(function (eventObj) {
        return true;
    });
</script>
