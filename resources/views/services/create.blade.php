@extends('services.index')

@section('title', __('Add Service'))

@section('content')

@parent
<x-panel id="manage-log-panel">
    <x-slot name="title">
        <i class="fa fa-stack-overflow fa-fw fa-lg" aria-hidden="true"></i> @lang('Add Service')
    </x-slot>
    <div class="container">
        <div class="row">
            <form action="{{ route('services.store') }}" method="POST" role="form"
                  class="form-horizontal services-form col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2 col-sm-12">
                <legend><h2>@lang('Create Service')</h2></legend>
                <div class='alert alert-info'>Service will created for the specified Device.</div>
                @csrf
                <div class='well well-lg'>
                    @include('services.form')
                    <div class="form-group">
                    <hr>
                        <center><button type="submit" class="btn btn-primary">@lang('Save')</button>
                            <a type="button" class="btn btn-danger"
                            href="{{ route('services.index') }}">@lang('Cancel')</a>
                        </center>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-panel>
@endsection
