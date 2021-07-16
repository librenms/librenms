@extends('services.index')

@section('title', __('Edit Service'))

@section('content')

@parent

<x-panel id="manage-edit-panel">
    <x-slot name="title">
        <i class="fa fa-pencil-square-o fa-col-primary fa-fw fa-lg" aria-hidden="true"></i> @lang('Edit Service')
    </x-slot>
    <div class="container">
        <div class="row">
            <form action="{{ route('services.update', $service->service_id) }}" method="POST" role="form"
                  class="form-horizontal services-form col-md-10 col-md-offset-1 col-sm-12">
                <div class='alert alert-info'>Service will edited for the specified Device.</div>
                {{ method_field('PUT') }}
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
</x-panel>
@endsection
