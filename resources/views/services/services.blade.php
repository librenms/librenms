@extends('services.index')

@section('title', __('Services'))

@section('content')

@parent

<x-panel id="manage-services-panel" title="{{ __('Services') }}">
    <x-slot name="title">
        <i class="fa fa-cogs fa-fw fa-lg" aria-hidden="true"></i> @lang('Services')
    </x-slot>
    <div class="table-responsive">
        <table id="manage-services-table" class="table table-condensed table-hover">
            <thead>
            <tr>
                <th>@lang('Name')</th>
                <th>@lang('Device')</th>
                <th>@lang('Plugin')</th>
                <th>@lang('Message')</th>
                <th>@lang('Remote Host')</th>
                <th>@lang('Description')</th>
                <th>@lang('Modified')</th>
                <th>@lang('Status')</th>
                <th>@lang('Disabled')</th>
                <th>@lang('Actions')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($services as $service)
                <tr id="row_{{ $service->service_id }}">
                    <td>{{ $service->service_name }}</td>
                    <td>{{ $service->device_id }}</td>
                    <td>{{ $service->service_type }}</td>
                    <td>{{ $service->service_message }}</td>
                    <td>{{ $service->service_ip }}</td>
                    <td>{{ $service->service_desc }}</td>
                    <td>{{ $service->service_changed }}</td>
                    <td>{{ $service->service_status }}</td>
                    <td>
                        <a type="button" title="@lang('Edit Service')" class="btn btn-primary btn-sm" aria-label="@lang('Edit')"
                            href="{{ route('services.edit', $service->service_id) }}">
                            <i class="fa fa-pencil" aria-hidden="true"></i></a>
                        <button type="button" class="btn btn-danger btn-sm" title="@lang('Delete Service')" aria-label="@lang('Delete')"
                            onclick="delete_st(this, '{{ $service->service_name }}', '{{ $service->service_id }}', '{{ route('services.destroy', $service->service_id) }}')">
                            <i class="fa fa-trash" aria-hidden="true"></i></button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</x-panel>
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
