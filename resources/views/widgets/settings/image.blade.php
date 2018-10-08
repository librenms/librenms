@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="image_title-{{ $id }}" class="control-label">@lang('Widget title')</label>
        <input type="text" class="form-control" name="image_title" id="image_title-{{ $id }}" placeholder="@lang('Custom title for widget')" value="{{ $image_title }}">
    </div>

    <div class="form-group">
        <label for="image_url-{{ $id }}" class="control-label">@lang('Image URL')</label>
        <input type="text" class="form-control" name="image_url" id="image_url-{{ $id }}" placeholder="@lang('Image URL')" value="{{ $image_url }}">
    </div>

    <div class="form-group">
        <label for="target_url-{{ $id }}" class="control-label">@lang('Target URL')</label>
        <input type="text" class="form-control" name="target_url" id="target_url-{{ $id }}" placeholder="@lang('Target URL')" value="{{ $target_url }}">
    </div>
@endsection
