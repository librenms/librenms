@extends('layouts.librenmsv1')

@section('title', __('Edit Service Template'))

@section('content')
    <div class="container">
        <div class="row">
            <form action="{{ route('services.templates.update', $service_template->id) }}" method="POST" role="form"
                  class="form-horizontal services-templates-form col-md-10 col-md-offset-1 col-sm-12">
                <legend>@lang('Edit Service Template'): {{ $service_template->name }}</legend>
                {{ method_field('PUT') }}
                @csrf

                @include('service-template.resources.views.service-template.resources.views.service-templates.form')

                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-sm-offset-2">
                        <button type="submit" class="btn btn-primary">@lang('Save')</button>
                        <a type="button" class="btn btn-danger"
                           href="{{ route('services.templates.index') }}">@lang('Cancel')</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('javascript')
    <script src="{{ asset('js/sql-parser.min.js') }}"></script>
    <script src="{{ asset('js/query-builder.standalone.min.js') }}"></script>
@endsection
