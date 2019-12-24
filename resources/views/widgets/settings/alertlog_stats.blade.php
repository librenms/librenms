@extends('widgets.settings.base')

@section('form')
    <div class="form-group row">
        <label for="title-{{ $id }}" class="control-label">@lang('Widget title')</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="@lang('Custom title')" value="{{ $title }}">
    </div>
    <div class="form-group row">
        <label for="min_severity-{{ $id }}" class="control-label">@lang('Displayed severity'):</label>
        <select class="form-control" name="min_severity" id="min_severity-{{ $id }}">
            <option value="">@lang('any severity')</option>
            @foreach($severities as $name => $val)
                <option value="{{ $val }}" @if($min_severity == $val) selected @endif>{{ $name }}{{$val > 3 ? '' : ' ' . __('or higher')}}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="time_interval-{{ $id }}" class="control-label">@lang('Last days')</label>
        <input class="form-control" name="time_interval" id="time_interval-{{ $id }}" value="{{ $time_interval }}">
    </div>
@endsection
