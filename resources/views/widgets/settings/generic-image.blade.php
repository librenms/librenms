@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">{{ __('Widget title') }}</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="{{ __('Default Title') }}" value="{{ $title }}">
    </div>

    <div class="form-group">
        <label for="image_url-{{ $id }}" class="control-label">{{ __('Image URL') }}</label>
        <input type="text" class="form-control" name="image_url" id="image_url-{{ $id }}" placeholder="{{ __('Image URL') }}" value="{{ $image_url }}" required>
    </div>

    <div class="form-group">
        <label for="target_url-{{ $id }}" class="control-label">{{ __('Target URL') }}</label>
        <input type="text" class="form-control" name="target_url" id="target_url-{{ $id }}" placeholder="{{ __('Target URL') }}" value="{{ $target_url }}">
    </div>
@endsection
