@extends('layouts.librenmsv1')

@section('title', __('Edit Service'))

@section('content')
    <div class="container">
        <div class="row">
            <form action="{{ route('services.update', $service->service_id) }}" method="POST" role="form"
                  class="form-horizontal services-form col-md-10 col-md-offset-1 col-sm-12">
                <legend><h2>@lang('Edit Service'): {{ $service->service_name }}</h2></legend>
                <div class='alert alert-info'>Service will edited for the specified Device.</div>
                {{ method_field('PUT') }}
                @csrf
                <div class='well well-lg'>
                    @include('services.form')
                    <div class="form-group">
                        <center><div class="col-sm-9 col-sm-offset-3 col-md-10 col-sm-offset-2">
                            <button type="submit" class="btn btn-primary">@lang('Save')</button>
                            <a type="button" class="btn btn-danger"
                            href="{{ route('services.index') }}">@lang('Cancel')</a>
                        </div></center>
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
