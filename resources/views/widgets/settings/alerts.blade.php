@extends('widgets.settings.base')

@section('form')
    <div class="form-group row">
        <label for="acknowledged-{{ $id }}" class="control-label col-sm-5">@lang('Show acknowledged'):</label>
        <div class="col-sm-7">
            <select class="form-control" name="acknowledged" id="acknowledged-{{ $id }}">
                <option value="">@lang('not filtered')</option>
                <option value="1" {{ $acknowledged === '1' ? 'selected' : '' }}>@lang('show only acknowledged')</option>
                <option value="0" {{ $acknowledged === '0' ? 'selected' : '' }}>@lang('hide acknowledged')</option>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="fired" class="control-label col-sm-5">@lang('Show only fired'):</label>
        <div class="col-sm-7">
            <select class="form-control" name="fired">
                <option value="">@lang('not filtered')</option>
                <option value="1" {{ $fired === '1' ? 'selected' : '' }}>@lang('show only fired alerts')</option>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="min_severity-{{ $id }}" class="control-label col-sm-5">@lang('Displayed severity'):</label>
        <div class="col-sm-7">
            <select class="form-control" name="min_severity" id="min_severity-{{ $id }}">
                <option value="">@lang('any severity')</option>
                @foreach($severities as $name => $val)
                    <option value="{{ $val }}" {{ $min_severity == $val ? 'selected' : '' }}>{{ $name }}{{$val > 3 ? '' : ' ' . __('or higher')}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="state-{{ $id }}" class="control-label col-sm-5">@lang('State'):</label>
        <div class="col-sm-7">
            <select class="form-control" name="state" id="state-{{ $id }}">
                <option value="">@lang('any state')</option>
                @foreach($states as $name => $val)
                    <option value="{{ $val }}" {{ $state === $val ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="device_group-{{ $id }}"
               class="col-sm-5 control-label">@lang('Device group')</label>
        <div class="col-sm-7">
            <select class="form-control" name="group" id="device_group-{{ $id }}">
                @if($device_group)
                    <option value="{{ $device_group->id }}" selected> {{ $device_group->name }} </option>
                @endif
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="proc-{{ $id }}" class="control-label col-sm-5">@lang('Show Procedure field'):</label>
        <div class="col-sm-7">
            <select class="form-control" name="proc" id="proc-{{ $id }}">
                <option value="1" {{ $proc == 1 ? 'selected' : '' }}>@lang('show')</option>
                <option value="0" {{ $proc == 0 ? 'selected' : '' }}>@lang('hide')</option>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="sort-{{ $id }}" class="control-label col-sm-5">@lang('Sort alerts by'):</label>
        <div class="col-sm-7">
            <select class="form-control" name="sort" id="sort-{{ $id }}">
                <option value="" {{ $sort == 1 ? 'selected' : '' }}>@lang('timestamp, descending')</option>
                <option value="severity" {{ $sort == 0 ? 'selected' : '' }}>@lang('severity, descending')</option>
            </select>
        </div>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        init_select2('#device_group-{{ $id }}', 'device-group', 'All Alerts', {}, {{ $group ?: 0 }});
    </script>
@endsection
