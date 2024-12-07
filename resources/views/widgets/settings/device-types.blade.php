@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="sort_order-{{ $id }}" class="control-label">{{ __('Sort Order') }}</label>
        <select class="form-control" id="sort_order-{{ $id }}" name="sort_order">
            <option value="name" @if($sort_order == 'name') selected @endif>{{ __('Device Type') }}</option>
            <option value="count" @if($sort_order == 'count') selected @endif>{{ __('Count') }}</option>
        </select>
    </div>
    <div class="form-group">
        <label for="top_device_group_count-{{ $id }}" class="control-label">{{ __('Top Count of Devices Types') }}</label>
        <input class="form-control" name="top_device_group_count" id="top_device_group_count-{{ $id }}" value="{{ $top_device_group_count }}">
    </div>
@endsection
