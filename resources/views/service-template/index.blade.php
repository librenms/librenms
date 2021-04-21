@extends('layouts.librenmsv1')

@section('title', __('Services Templates'))

@section('content')
    <div class="container-fluid">
        <x-panel id="manage-services-templates-panel">
            <x-slot name="title">
                <span class="fa-stack" aria-hidden="true">
                    <i class="fa fa-square fa-stack-2x"></i>
                    <i class="fa fa-cogs fa-stack-1x fa-inverse"></i>
                </span> @lang('Services Templates')
            </x-slot>
            <div class="row" style="padding-bottom: 16px;">
                <div class="col-md-12">
                    <a type="button" class="btn btn-primary" href="{{ route('services.templates.create') }}">
                        <i class="fa fa-plus"></i> @lang('New Service Template')
                    </a>
                    <button type="button" title="@lang('Apply Service Templates')" class="btn btn-success" aria-label="@lang('Apply Service Templates')"
                            onclick="applyAll_st(this, '{{ route('services.templates.applyAll') }}')">
                        <i
                            class="fa fa-refresh" aria-hidden="true"></i> @lang('Apply Service Templates')</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="manage-services-templates-table" class="table table-condensed table-hover">
                    <thead>
                    <tr>
                        <th>@lang('Name')</th>
                        <th>@lang('Description')</th>
                        <th>@lang('Devices')</th>
                        <th>@lang('Device Groups')</th>
                        <th>@lang('Device Type')</th>
                        <th>@lang('Device Rules')</th>
                        <th>@lang('Actions')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($service_templates as $template)
                        <tr id="row_{{ $template->id }}">
                            <td>{{ $template->name }}</td>
                            <td>{{ $template->desc }}</td>
                            <td>
                                <a href="{{ url("/devices/serviceTemplates=$template->id") }}">{{ $template->devices_count }}</a>
                            </td>
                            <td>
                                <a href="{{ url("/device-groups/serviceTemplates=$template->id") }}">{{ $template->groups_count }}</a>
                            </td>
                            <td>{{ __(ucfirst($template->type)) }}</td>
                            <td>{{ $template->type == 'dynamic' ? $template->getDeviceParser()->toSql(false) : '' }}</td>
                            <td>
                                <button type="button" title="@lang('Apply Services for this Service Template')" class="btn btn-success btn-sm" aria-label="@lang('Apply')"
                                    onclick="apply_st(this, '{{ $template->name }}', '{{ $template->id }}', '{{ route('services.templates.apply', $template->id) }}')">
                                    <i class="fa fa-refresh" aria-hidden="true"></i></button>
                                <button type="button" title="@lang('Remove Services for this Service Template')" class="btn btn-warning btn-sm" aria-label="@lang('Remove')"
                                    onclick="remove_st(this, '{{ $template->name }}', '{{ $template->id }}', '{{ route('services.templates.remove', $template->id) }}')">
                                    <i class="fa fa-ban" aria-hidden="true"></i></button>
                                <a type="button" title="@lang('Edit Service Template')" class="btn btn-primary btn-sm" aria-label="@lang('Edit')"
                                    href="{{ route('services.templates.edit', $template->id) }}">
                                    <i class="fa fa-pencil" aria-hidden="true"></i></a>
                                <button type="button" class="btn btn-danger btn-sm" title="@lang('Delete Service Template')" aria-label="@lang('Delete')"
                                    onclick="delete_st(this, '{{ $template->name }}', '{{ $template->id }}', '{{ route('services.templates.destroy', $template->id) }}')">
                                    <i class="fa fa-trash" aria-hidden="true"></i></button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @foreach($groups as $group)
                <x-panel id="manage-services-templates-panel-dg">
                    <x-slot name="title">
                        <i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i> @lang($group->name)
                    </x-slot>
                    <div class="table-responsive">
                        <table id="manage-services-templates-table-dg-{{ $group->id }}" class="table table-condensed table-hover">
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
                                @foreach($group->serviceTemplates as $template)
                                    <tr id="row_{{ $template->id }}">
                                        <td>{{ $template->name }}</td>
                                        <td>{{ $template->check }}</td>
                                        <td>{{ $template->param }}</td>
                                        <td>{{ $template->ip }}</td>
                                        <td>{{ $template->desc }}</td>
                                        <td>{{ $template->changed }}</td>
                                        <td>{{ $template->ignore }}</td>
                                        <td>{{ $template->disabled }}</td>
                                        <td>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-panel>
            @endforeach
            @foreach($devices as $device)
                <x-panel id="manage-services-templates-panel-d">
                    <x-slot name="title">
                        <i class="fa fa-server fa-fw fa-lg" aria-hidden="true"></i> @lang($device->hostname)
                    </x-slot>
                    <div class="table-responsive">
                        <table id="manage-services-templates-table-d-{{ $device->id }}" class="table table-condensed table-hover">
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
                                @foreach($device->serviceTemplates as $template)
                                    <tr id="row_{{ $template->id }}">
                                        <td>{{ $template->name }}</td>
                                        <td>{{ $template->check }}</td>
                                        <td>{{ $template->param }}</td>
                                        <td>{{ $template->ip }}</td>
                                        <td>{{ $template->desc }}</td>
                                        <td>{{ $template->changed }}</td>
                                        <td>{{ $template->ignore }}</td>
                                        <td>{{ $template->disabled }}</td>
                                        <td>
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
        function apply_st(button, name, id, url) {
            if (confirm('@lang('Are you sure you want to create Services for ')' + name + '?')) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    success: function (msg) {
                        toastr.success(msg);
                    },
                    error:function(){
                        toastr.error('No Services were updated when Applying this Service Template');
                    }
                });
            }
        }
        function applyAll_st(button, url) {
            if (confirm('@lang('Are you sure you want to Apply All Service Templates?')')) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    success: function (msg) {
                        toastr.success(msg);
                    },
                    error:function(){
                        toastr.error('No Services were updated');
                    }
                });
            }
        }
        function remove_st(button, name, id, url) {
            if (confirm('@lang('Are you sure you want to remove all Services created by ')' + name + '?')) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    success: function (msg) {
                        toastr.success(msg);
                    },
                    error: function () {
                        toastr.error('@lang('No Services for this Service Template were removed')');
                    }
                });
            }

            return false;
        }
        function delete_st(button, name, id, url) {
            var index = button.parentNode.parentNode.rowIndex;
            if (confirm('@lang('Are you sure you want to delete AND remove all Services created by ')' + name + '?')) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    success: function (msg) {
                        document.getElementById("manage-services-templates-table").deleteRow(index);
                        toastr.success(msg);
                    },
                    error: function () {
                        toastr.error('@lang('The Service Template could not be deleted')');
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
