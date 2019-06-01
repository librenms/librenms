<div class="form-group @if($errors->has('name')) has-error @endif">
    <label for="name" class="control-label col-sm-3 text-nowrap">@lang('Name')</label>
    <div class="col-sm-9">
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $device_group->name) }}">
        <span class="help-block">{{ $errors->first('name') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('desc')) has-error @endif">
    <label for="desc" class="control-label col-sm-3 text-nowrap">@lang('Description')</label>
    <div class="col-sm-9">
        <input type="text" class="form-control" id="desc" name="desc" value="{{ old('desc', $device_group->desc) }}">
        <span class="help-block">{{ $errors->first('desc') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('type')) has-error @endif">
    <label for="level" class="control-label col-sm-3">@lang('Type')</label>
    <div class="col-sm-9">
        <select class="form-control" id="type" name="type" onchange="change_dg_type(this)">
            <option value="dynamic"
                    @if(old('type', $device_group->type) == 'dynamic') selected @endif>@lang('Dynamic')</option>
            <option value="static"
                    @if(old('type', $device_group->type) == 'static') selected @endif>@lang('Static')</option>
        </select>
        <span class="help-block">{{ $errors->first('type') }}</span>
    </div>
</div>

<div id="dynamic-dg-form" class="form-group @if($errors->has('pattern')) has-error @endif">
    <label for="pattern" class="control-label col-sm-3 text-nowrap">@lang('Define Rules')</label>
    <div class="col-sm-9">
        <input type="text" class="form-control" id="pattern" name="pattern"
               value="{{ old('pattern', $device_group->pattern) }}">
        <span class="help-block">{{ $errors->first('pattern') }}</span>
    </div>
</div>

<div id="static-dg-form" class="form-group @if($errors->has('devices')) has-error @endif" style="display: none">
    <label for="devices" class="control-label col-sm-3 text-nowrap">@lang('Select Devices')</label>
    <div class="col-sm-9">
        <select class="form-control" id="devices" name="devices[]" multiple>
            @foreach($device_group->devices as $device)
                <option value="{{ $device->device_id }}" selected>{{ $device->displayName() }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ $errors->first('devices') }}</span>
    </div>
</div>

<script>
    function change_dg_type(select) {
        var type = select.options[select.selectedIndex].value;
        document.getElementById("dynamic-dg-form").style.display = (type === 'dynamic' ? 'block' : 'none');
        document.getElementById("static-dg-form").style.display = (type === 'dynamic' ? 'none' : 'block');
    }

    change_dg_type(document.getElementById('type'));

    init_select2('#devices', 'device', {multiple: true});
</script>
