@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="show_services-{{ $id }}" class="control-label">{{ __('Show Services') }}</label>
            <select class="form-control" id="show_services-{{ $id }}" name="show_services">
                <option value="0" @unless($show_services) selected @endunless>{{ __('no') }}</option>
                <option value="1" @if($show_services) selected @endif>{{ __('yes') }}</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="summary_errors-{{ $id }}" class="control-label">{{ __('Show Port Errors') }}</label>
        <select class="form-control" id="summary_errors-{{ $id }}" name="summary_errors">
            <option value="0" @unless($summary_errors) selected @endunless>{{ __('no') }}</option>
            <option value="1" @if($summary_errors) selected @endif>{{ __('yes') }}</option>
        </select>
    </div>
@endsection
