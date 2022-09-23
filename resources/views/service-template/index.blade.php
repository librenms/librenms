@extends('layouts.librenmsv1')

@section('title', __('Services Templates'))

@section('content')
    <div class="container-fluid">
        <x-panel id="manage-services-templates-panel">
            <x-slot name="title">
                <span class="fa-stack" aria-hidden="true">
                    <i class="fa fa-square fa-stack-2x"></i>
                    <i class="fa fa-cogs fa-stack-1x fa-inverse"></i>
                </span> {{ __('Services Templates') }}
            </x-slot>
            <div class="row" style="padding-bottom: 16px;">
                <div class="col-md-12">
                    <a type="button" class="btn btn-primary" href="{{ route('services.templates.create') }}">
                        <i class="fa fa-plus"></i> {{ __('New Service Template') }}
                    </a>
                    <button type="button" title="{{ __('Apply Service Templates') }}" class="btn btn-success" aria-label="{{ __('Apply Service Templates') }}"
                            onclick="applyAll_st(this, '{{ route('services.templates.applyAll') }}')">
                        <i
                            class="fa fa-refresh" aria-hidden="true"></i> {{ __('Apply Service Templates') }}</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="manage-services-templates-table" class="table table-condensed table-hover">
                    <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Devices') }}</th>
                        <th>{{ __('Device Groups') }}</th>
                        <th>{{ __('Device Type') }}</th>
                        <th>{{ __('Device Rules') }}</th>
                        <th>{{ __('Actions') }}</th>
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
                                <button type="button" title="{{ __('Apply Services for this Service Template') }}" class="btn btn-success btn-sm" aria-label="{{ __('Apply') }}"
                                    onclick="apply_st(this, '{{ $template->name }}', '{{ $template->id }}', '{{ route('services.templates.apply', $template->id) }}')">
                                    <i class="fa fa-refresh" aria-hidden="true"></i></button>
                                <button type="button" title="{{ __('Remove Services for this Service Template') }}" class="btn btn-warning btn-sm" aria-label="{{ __('Remove') }}"
                                    onclick="remove_st(this, '{{ $template->name }}', '{{ $template->id }}', '{{ route('services.templates.remove', $template->id) }}')">
                                    <i class="fa fa-ban" aria-hidden="true"></i></button>
                                <a type="button" title="{{ __('Edit Service Template') }}" class="btn btn-primary btn-sm" aria-label="{{ __('Edit') }}"
                                    href="{{ route('services.templates.edit', $template->id) }}">
                                    <i class="fa fa-pencil" aria-hidden="true"></i></a>
                                <button type="button" class="btn btn-danger btn-sm" title="{{ __('Delete Service Template') }}" aria-label="{{ __('Delete') }}"
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
                        <i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i> {{ __($group->name) }}
                    </x-slot>
                    <div class="table-responsive">
                        <table id="manage-services-templates-table-dg-{{ $group->id }}" class="table table-condensed table-hover">
                            <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Check Type') }}</th>
                                <th>{{ __('Parameters') }}</th>
                                <th>{{ __('Remote Host') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Modified') }}</th>
                                <th>{{ __('Ignored') }}</th>
                                <th>{{ __('Disabled') }}</th>
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
                        <i class="fa fa-server fa-fw fa-lg" aria-hidden="true"></i> {{ __($device->hostname) }}
                    </x-slot>
                    <div class="table-responsive">
                        <table id="manage-services-templates-table-d-{{ $device->id }}" class="table table-condensed table-hover">
                            <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Check Type') }}</th>
                                <th>{{ __('Parameters') }}</th>
                                <th>{{ __('Remote Host') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Modified') }}</th>
                                <th>{{ __('Ignored') }}</th>
                                <th>{{ __('Disabled') }}</th>
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
            if (confirm('{{ __('Are you sure you want to create Services for ') }}' + name + '?')) {
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
            if (confirm('{{ __('Are you sure you want to Apply All Service Templates?') }}')) {
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
            if (confirm('{{ __('Are you sure you want to remove all Services created by ') }}' + name + '?')) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    success: function (msg) {
                        toastr.success(msg);
                    },
                    error: function () {
                        toastr.error('{{ __('No Services for this Service Template were removed') }}');
                    }
                });
            }

            return false;
        }
        function delete_st(button, name, id, url) {
            var index = button.parentNode.parentNode.rowIndex;
            if (confirm('{{ __('Are you sure you want to delete AND remove all Services created by ') }}' + name + '?')) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    success: function (msg) {
                        document.getElementById("manage-services-templates-table").deleteRow(index);
                        toastr.success(msg);
                    },
                    error: function () {
                        toastr.error('{{ __('The Service Template could not be deleted') }}');
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
