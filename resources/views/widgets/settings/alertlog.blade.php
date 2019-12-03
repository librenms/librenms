@extends('widgets.settings.base')

@section('form')
    <div class="form-group row">
        <label for="title-{{ $id }}" class="control-label">@lang('Widget title')</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="@lang('Custom title')" value="{{ $title }}">
    </div>
    <div class="form-group row">
        <label for="state-{{ $id }}" class="control-label">@lang('State'):</label>
        <select class="form-control" name="state" id="state-{{ $id }}">
            <option value="-1">@lang('not filtered')</option>
            <option value="0" @if($state === '1') selected @endif>@lang('OK')</option>
            <option value="1" @if($state === '0') selected @endif>@lang('Alert')</option>
        </select>
    </div>
@endsection
