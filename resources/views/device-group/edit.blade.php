@extends('layouts.librenmsv1')

@section('title', __('Edit Device Group'))

@section('content')
    <div class="container">
        <div class="row">
            <form action="{{ route('device-group.update', $device_group->id) }}" method="POST" role="form"
                  class="form-horizontal col-md-8 col-md-offset-2">
                <legend>@lang('Edit Device Group'): {{ $device_group->name }}</legend>
                {{ method_field('PUT') }}

                @include('device-group.form')

                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-3">
                        <button type="submit" class="btn btn-primary">@lang('Save')</button>
                        <a type="button" class="btn btn-danger"
                           href="{{ route('device-group.index') }}">@lang('Cancel')</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
