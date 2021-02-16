@extends('layouts.librenmsv1')

@section('title', __('Services'))

@section('content')
    <div class="container-fluid">
        <x-panel id="manage-services-panel">
            <x-slot name="title">
                <span class="fa-stack" aria-hidden="true">
                    <i class="fa fa-square fa-stack-2x"></i>
                    <i class="fa fa-cogs fa-stack-1x fa-inverse"></i>
                </span> @lang('Services')
            </x-slot>
            <div class="row" style="padding-bottom: 16px;">
                <div class="col-md-12">
                    <a type="button" class="btn btn-primary" href="{{ route('services.create') }}">
                        <i class="fa fa-plus"></i> @lang('New Service')
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table id="manage-services-table" class="table table-condensed table-hover">
                    <thead>
                    <tr>
                        <th>@lang('Name')</th>
                        <th>@lang('Check Type')</th>
                        <th>@lang('Parameters')</th>
                        <th>@lang('Remote Host')</th>
                        <th>@lang('Description')</th>
                        <th>@lang('Modified')</th>
                        <th>@lang('Ignored')</th>
                        <th>@lang('Disabled')</th>
                        <th>@lang('Actions')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($services as $service)
                        <tr id="row_{{ $service->id }}">
                            <td>{{ $service->name }}</td>
                            <td>{{ $service->type }}</td>
                            <td>{{ $service->param }}</td>
                            <td>{{ $service->ip }}</td>
                            <td>{{ $service->desc }}</td>
                            <td>{{ $service->changed }}</td>
                            <td>{{ $service->ignore }}</td>
                            <td>{{ $service->disabled }}</td>
                            <td>
                                <a type="button" title="@lang('Edit Service')" class="btn btn-primary btn-sm" aria-label="@lang('Edit')"
                                    href="{{ route('services.edit', $service->id) }}">
                                    <i class="fa fa-pencil" aria-hidden="true"></i></a>
                                <button type="button" class="btn btn-danger btn-sm" title="@lang('Delete Service')" aria-label="@lang('Delete')"
                                    onclick="delete_st(this, '{{ $service->name }}', '{{ $service->id }}', '{{ route('services.destroy', $service->id) }}')">
                                    <i class="fa fa-trash" aria-hidden="true"></i></button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-panel>
    </div>
@endsection

@section('scripts')
    <script>
        function delete_st(button, name, id, url) {
            var index = button.parentNode.parentNode.rowIndex;
            if (confirm('@lang('Are you sure you want to delete AND remove all Services created by ')' + name + '?')) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    success: function (msg) {
                        document.getElementById("manage-services-table").deleteRow(index);
                        toastr.success(msg);
                    },
                    error: function () {
                        toastr.error('@lang('The Service could not be deleted')');
                    }
                });
            }

            return false;
        }
    </script>
@endsection

@section('css')
    <style>
        .table-responsive {
            padding-top: 16px
        }
    </style>
@endsection
