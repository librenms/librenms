@extends('layouts.librenmsv1')

@section('title', __('Create Service Template'))

@section('content')
    <div class="container">
        <div class="row">
            <form action="{{ route('services.templates.store') }}" method="POST" role="form"
                  class="form-horizontal service-template-form col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2 col-sm-12">
                <legend><h2>@lang('Create Service Template')</h2></legend>
                <div class='alert alert-info'>Service Template will created for the specified Device Group.</div>
                @csrf
                <div class='well well-lg'>
                    @include('service-template.form')
                    <div class="form-group">
                    <hr>
                        <center><button type="submit" class="btn btn-primary">@lang('Save')</button>
                            <a type="button" class="btn btn-danger"
                            href="{{ route('services.templates.index') }}">@lang('Cancel')</a>
                        </center>
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
