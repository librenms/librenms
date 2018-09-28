@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="col-sm-4 control-label">@lang('Widget title')</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="@lang('Custom title for widget')"
                   value="{{ $title }}">
        </div>
    </div>
@endsection
