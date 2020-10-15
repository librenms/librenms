<div class="form-group @if($errors->has('name')) has-error @endif">
    <label for="name" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Name')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="service_template_name" name="service_template_name" value="{{ old('service_template_name', $service_template->service_template_name) }}">
        <span class="help-block">{{ $errors->first('service_template_name') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('device_group_id')) has-error @endif">
    <label for="device_group_id" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Device Group')</label>
    <div class="col-sm-9 col-md-10">
        <select class="form-control" id="device_group_id" name="device_group_id">
            @foreach($device_groups as $device_group)
                <option value="{{ $device_group->id }}">{{ $device_group->name }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ $errors->first('device_group_id') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('type')) has-error @endif">
    <label for="type" class="control-label col-sm-3 col-md-2">@lang('Check Type')</label>
    <div class="col-sm-9 col-md-10">
        <select class="form-control" id="service_template_type" name="service_template_type">
            @foreach($services as $current_service)
                <option value="{{ $current_service }}">{{ $current_service }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ $errors->first('service_template_type') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('desc')) has-error @endif">
    <label for="desc" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Description')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="service_template_desc" name="service_template_desc" value="{{ old('service_template_desc', $service_template->service_template_desc) }}">
        <span class="help-block">{{ $errors->first('desc') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('ip')) has-error @endif">
    <label for="ip" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Remote Host')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="service_template_ip" name="service_template_ip" value="{{ old('ip', $service_template->service_template_ip) }}">
        <span class="help-block">{{ $errors->first('ip') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('param')) has-error @endif">
    <label for="param" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Parameters')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="service_template_param" name="service_template_param" value="{{ old('service_template_param', $service_template->service_template_param) }}">
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
        <input type="checkbox" class="form-control" id="service_template_ignore" name="service_template_ignore" value="{{ old('service_template_ignore', $service_template->service_template_ignore) }}">
        <span class="help-block">{{ $errors->first('ignore') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('ip')) has-error @endif">
    <label for="ip" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Disable polling and alerting')</label>
    <div class="col-sm-9 col-md-10">
        <input type="checkbox" class="form-control" id="service_template_disabled" name="service_template_disabled" value="{{ old('service_template_disabled', $service_template->service_template_disabled) }}">
        <span class="help-block">{{ $errors->first('disabled') }}</span>
    </div>
</div>

<script>
$("[type='checkbox']").bootstrapSwitch('offColor','danger');
</script>
