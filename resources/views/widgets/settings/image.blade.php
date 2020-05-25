@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">@lang('Widget title')</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="@lang('Default Title')" value="{{ $title }}">
    </div>

    <div class="form-group">
        <label for="image_url-{{ $id }}" class="control-label">@lang('Image URL')</label>
        <input type="text" class="form-control" name="image_url" id="image_url-{{ $id }}" placeholder="@lang('Image URL')" value="{{ $image_url }}" required>
    </div>

    <div class="form-group">
        <label for="target_url-{{ $id }}" class="control-label">@lang('Target URL')</label>
        <input type="text" class="form-control" name="target_url" id="target_url-{{ $id }}" placeholder="@lang('Target URL')" value="{{ $target_url }}">
    </div>
@endsection
