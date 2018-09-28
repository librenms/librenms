@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="show_services-{{ $id }}" class="col-sm-6 control-label">@lang('Show Services')</label>
        <div class="col-sm-6">
            <select class="form-control" id="show_services-{{ $id }}" name="show_services">
                <option value="0" {{ $show_services ? '' : 'selected' }}>@lang('no')</option>
                <option value="1" {{ $show_services ? 'selected' : '' }}>@lang('yes')</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="summary_errors-{{ $id }}" class="col-sm-6 control-label">@lang('Show Port Errors')</label>
        <div class="col-sm-6">
            <select class="form-control col-sm-6" id="summary_errors-{{ $id }}" name="summary_errors">
                <option value="0" {{ $summary_errors ? '' : 'selected' }}>@lang('no')</option>
                <option value="1" {{ $summary_errors ? 'selected' : '' }}>@lang('yes')</option>
            </select>
        </div>
    </div>
@endsection
