<div class="form-group @if($errors->has('service_name')) has-error @endif">
    <label for="name" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Name')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="service_name" name="service_name" value="{{ old('service_name', $service->service_name) }}">
        <span class="help-block">{{ $errors->first('service_name') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('devices')) has-error @endif" style="display: none">
    <label for="devices" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Select Devices')</label>
    <div class="col-sm-9 col-md-10">
        <select class="form-control" id="devices" name="devices[]" multiple>
            @foreach($service->devices as $device)
                <option value="{{ $device->device_id }}" selected>{{ $device->displayName() }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ $errors->first('devices') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('service_type')) has-error @endif">
    <label for="service_type" class="control-label col-sm-3 col-md-2">@lang('Check Type')</label>
    <div class="col-sm-9 col-md-10">
        <select class="form-control" id="service_type" name="service_type">
            @foreach($services_list as $current_service)
                <option value="{{ $current_service }}" @if($current_service == $service->service_type) selected @endif>{{ $current_service }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ $errors->first('service_type') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('service_desc')) has-error @endif">
    <label for="service_desc" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Description')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="service_desc" name="service_desc" value="{{ old('service_desc', $service->service_desc) }}">
        <span class="help-block">{{ $errors->first('service_desc') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('service_ip')) has-error @endif">
    <label for="service_ip" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Remote Host')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="service_ip" name="service_ip" value="{{ old('service_ip', $service->service_ip) }}">
        <span class="help-block">{{ $errors->first('service_ip') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('service_param')) has-error @endif">
    <label for="service_param" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Parameters')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="service_param" name="service_param" value="{{ old('service_param', $service->service_param) }}">
        <span class="help-block">{{ $errors->first('service_param') }}</span>
    </div>
</div>

<div class="form-group row">
    <div class="col-sm-12 alert alert-info">
        <label class='control-label text-left input-sm'>Parameters may be required and will be different depending on the service check.</label>
    </div>
</div>

<div class="form-group @if($errors->has('service_ignore')) has-error @endif">
    <label for="service_ignore" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Ignore alert tag')</label>
    <div class="col-sm-9 col-md-10">
        <input type="hidden" value="0" name="service_ignore">
        <input type="checkbox" class="form-control" id="service_ignore" name="service_ignore" data-size="small" value="{{ old('service_ignore', $service->service_ignore) }}"@if(old('service_ignore', $service->service_ignore) == 1) checked @endif>
        <span class="help-block">{{ $errors->first('service_ignore') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('service_disabled')) has-error @endif">
    <label for="service_disabled" class="control-label col-sm-3 col-md-2 text-wrap">@lang('Disable polling and alerting')</label>
    <div class="col-sm-9 col-md-10">
        <input type="hidden" value="0" name="service_disabled">
        <input type="checkbox" class="form-control" id="service_disabled" name="service_disabled" data-size="small" value="{{ old('service_disabled', $service->service_disabled) }}"@if(old('service_disabled', $service->service_disabled) == 1) checked @endif>
        <span class="help-block">{{ $errors->first('service_disabled') }}</span>
    </div>
</div>

<script>
    $("[type='checkbox']").bootstrapSwitch('offColor','danger');
    $("#service_ignore").on( 'switchChange.bootstrapSwitch', function (e, state) {
        var value = $(this).is(':checked') ? "1": "0";
        $('#service_ignore').val(value);
    });
    $("#service_disabled").on( 'switchChange.bootstrapSwitch', function (e, state) {
        var value = $(this).is(':checked') ? "1": "0";
        $('#service_disabled').val(value);
    });

    init_select2('#devices', 'device', {multiple: false});
    $('.service-form').submit(function (eventObj) {
        return true;
    });
</script>
