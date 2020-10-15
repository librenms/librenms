@extends('layouts.librenmsv1')

@section('title', __('Services Templates'))

@section('content')
    <div class="container-fluid">
        <x-panel id="manage-services-templates-panel">
            <x-slot name="title">
                <i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i> @lang('Services Templates')
            </x-slot>

            <div class="row">
                <div class="col-md-12">
                    <a type="button" class="btn btn-success" href="{{ route('services.templates.create') }}">
                        <i class="fa fa-plus"></i> @lang('New Service Template')
                    </a>
                </div>
            </div>
            @foreach($device_groups as $device_group)
                <x-panel id="manage-services-templates-panel-dg" title="{{ __('Device Group:') }} ({{ $device_group->name }})">
                    <div class="table-responsive">
                        <table id="manage-services-templates-table" class="table table-condensed table-hover">
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
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($service_templates as $service_template)
                                    <tr id="row_{{ $service_template->service_template_id }}">
                                        <td>{{ $service_template->service_template_name }}</td>
                                        <td>{{ $service_template->service_template_type }}</td>
                                        <td>{{ $service_template->service_template_param }}</td>
                                        <td>{{ $service_template->service_template_ip }}</td>
                                        <td>{{ $service_template->service_template_desc }}</td>
                                        <td>{{ $service_template->service_template_changed }}</td>
                                        <td>{{ $service_template->service_template_ignore }}</td>
                                        <td>{{ $service_template->service_template_disabled }}</td>
                                        <td>
                                            <button type="button" title="@lang('Apply Services for this Service Template')" class="btn btn-success btn-sm" aria-label="@lang('Apply')"
                                                    onclick="discover_st(this, '{{ $service_template->service_template_id }}')">
                                                <i
                                                    class="fa fa-plus" aria-hidden="true"></i></button>
                                            <button type="button" title="@lang('Remove Services for this Service Template')" class="btn btn-warning btn-sm" aria-label="@lang('Apply')"
                                                    onclick="remove_st(this, '{{ $service_template->service_template_id }}')">
                                                <i
                                                    class="fa fa-minus" aria-hidden="true"></i></button>
                                            <a type="button" title="@lang('edit Service Template')" class="btn btn-primary btn-sm" aria-label="@lang('Edit')"
                                            href="{{ route('services.templates.edit', $service_template->id) }}">
                                                <i class="fa fa-pencil" aria-hidden="true"></i></a>
                                            <button type="button" class="btn btn-danger btn-sm" title="@lang('delete Service Template')" aria-label="@lang('Delete')"
                                                    onclick="delete_st(this, '{{ $service_template->name }}', '{{ route('services.templates.destroy', $service_template->id) }}')">
                                                <i
                                                    class="fa fa-trash" aria-hidden="true"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-panel>
            @endforeach
        </x-panel>
    </div>
@endsection

@section('scripts')
    <script>
        function discover_st(button, service_template_id) {
            if (confirm('@lang('Are you sure you want to create Services for ')' + name + '?')) {
                $.ajax({
                    type: 'POST',
                    url: 'ajax_form.php',
                    data: { type: "discover-service-template", service_template_id: service_template_id },
                    dataType: "json",
                    success: function(data){
                        if(data['status'] == 'ok') {
                            toastr.success(data['message']);
                        } else {
                            toastr.error(data['message']);
                        }
                    },
                    error:function(){
                        toastr.error('No Services were updated when Applying this Service Template');
                    }
                });
            }
        }
        function remove_st(button, service_template_id) {
            if (confirm('@lang('Are you sure you want to remove all Services created by ')' + name + '?')) {
                $.ajax({
                    type: 'POST',
                    url: 'ajax_form.php',
                    data: { type: "remove-service-template", service_template_id: service_template_id },
                    dataType: "json",
                    success: function(data){
                        if(data['status'] == 'ok') {
                            toastr.success(data['message']);
                        } else {
                            toastr.error(data['message']);
                        }
                    },
                    error:function(){
                        toastr.error('No Services were removed for this Service Template');
                    }
                });
            }
        }
        function delete_st(button, service_template_id) {
            if (confirm('@lang('Are you sure you want to delete AND remove all Services created by ')' + name + '?')) {
                $.ajax({
                    type: 'POST',
                    url: 'ajax_form.php',
                    data: { type: "delete-service-template", service_template_id: service_template_id },
                    dataType: "json",
                    success: function(data){
                        if(data['status'] == 'ok') {
                            toastr.success(data['message']);
                        } else {
                            toastr.error(data['message']);
                        }
                    },
                    error:function(){
                        toastr.error('An error occurred deleting this Service Template');
                    }
                });
            }
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
