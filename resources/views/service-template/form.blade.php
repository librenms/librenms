<div class="form-group @if($errors->has('name')) has-error @endif">
    <label for="name" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Name')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $template->name) }}">
        <span class="help-block">{{ $errors->first('name') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('device_group_id')) has-error @endif">
    <label for="device_group_id" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Device Group')</label>
    <div class="col-sm-9 col-md-10">
        <select class="form-control" id="device_group_id" name="device_group_id[]" multiple>
            @foreach($device_groups as $device_group)
                <option value="{{ $device_group->id }}" selected>{{ $device_group->name }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ $errors->first('device_group_id') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('type')) has-error @endif">
    <label for="type" class="control-label col-sm-3 col-md-2">@lang('Check Type')</label>
    <div class="col-sm-9 col-md-10">
        <select class="form-control" id="type" name="type">
            @foreach($services as $current_service)
                <option value="{{ $current_service }}" @if($current_service == $template->type) selected @endif>{{ $current_service }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ $errors->first('type') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('desc')) has-error @endif">
    <label for="desc" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Description')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="desc" name="desc" value="{{ old('desc', $template->desc) }}">
        <span class="help-block">{{ $errors->first('desc') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('ip')) has-error @endif">
    <label for="ip" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Remote Host')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="ip" name="ip" value="{{ old('ip', $template->ip) }}">
        <span class="help-block">{{ $errors->first('ip') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('param')) has-error @endif">
    <label for="param" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Parameters')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="param" name="param" value="{{ old('param', $template->param) }}">
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
        <input type="checkbox" class="form-control" id="ignore" name="ignore" data-size="small" value="{{ old('ignore', $template->ignore) }}"@if(old('ignore', $template->ignore) == 1) checked @endif>
        <span class="help-block">{{ $errors->first('ignore') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('disabled')) has-error @endif">
    <label for="disabled" class="control-label col-sm-3 col-md-2 text-wrap">@lang('Disable polling and alerting')</label>
    <div class="col-sm-9 col-md-10">
        <input type="hidden" value="0" name="disabled">
        <input type="checkbox" class="form-control" id="disabled" name="disabled" data-size="small" value="{{ old('disabled', $template->disabled) }}"@if(old('disabled', $template->disabled) == 1) checked @endif>
        <span class="help-block">{{ $errors->first('disabled') }}</span>
    </div>
</div>

<script>
init_select2('#device_group_id', 'device-group', {multiple: true});
$("[type='checkbox']").bootstrapSwitch('offColor','danger');
$("#ignore").on( 'switchChange.bootstrapSwitch', function (e, state) {
    var value = $(this).is(':checked') ? "1": "0";
    $('#ignore').val(value);
});
$("#disabled").on( 'switchChange.bootstrapSwitch', function (e, state) {
    var value = $(this).is(':checked') ? "1": "0";
    $('#disabled').val(value);
});
</script>
