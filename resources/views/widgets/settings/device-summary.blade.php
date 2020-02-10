@extends('widgets.settings.base')

@section('form')
    <div class="form-group row">
        <label for="title-{{ $id }}" class="control-label">@lang('Widget title')</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="@lang('Custom title')" value="{{ $title }}">
    </div>
    <div class="form-group row">
        <label for="show_services-{{ $id }}" class="control-label">@lang('Show Services')</label>
            <select class="form-control" id="show_services-{{ $id }}" name="show_services">
                <option value="0" @unless($show_services) selected @endunless>@lang('no')</option>
                <option value="1" @if($show_services) selected @endif>@lang('yes')</option>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="summary_errors-{{ $id }}" class="control-label">@lang('Show Port Errors')</label>
        <select class="form-control" id="summary_errors-{{ $id }}" name="summary_errors">
            <option value="0" @unless($summary_errors) selected @endunless>@lang('no')</option>
            <option value="1" @if($summary_errors) selected @endif>@lang('yes')</option>
        </select>
    </div>
@endsection
