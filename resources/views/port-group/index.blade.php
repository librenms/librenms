@extends('layouts.librenmsv1')

@section('title', __('Port Groups'))

@section('content')
    <div class="container-fluid">
        <x-panel id="manage-port-groups-panel">
            <x-slot name="title">
                <i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i> {{ __('Port Groups') }}
            </x-slot>

            <div class="row">
                <div class="col-md-12">
                    <a type="button" class="btn btn-primary" href="{{ route('port-groups.create') }}">
                        <i class="fa fa-plus"></i> {{ __('New Port Group') }}
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table id="manage-port-groups-table" class="table table-condensed table-hover">
                    <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Ports') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($port_groups as $port_group)
                        <tr id="row_{{ $port_group->id }}">
                            <td>{{ $port_group->name }}</td>
                            <td>{{ $port_group->desc }}</td>
                            <td>{{ $port_group->ports_count }}</td>
                            <td>
                                <a type="button" title="{{ __('edit Port Group') }}" class="btn btn-primary btn-sm" aria-label="{{ __('Edit') }}"
                                   href="{{ route('port-groups.edit', $port_group->id) }}">
                                    <i class="fa fa-pencil" aria-hidden="true"></i></a>
                                <button type="button" class="btn btn-danger btn-sm" title="{{ __('delete Port Group') }}" aria-label="{{ __('Delete') }}"
                                        onclick="delete_pg(this, '{{ $port_group->name }}', '{{ route('port-groups.destroy', $port_group->id) }}')">
                                    <i
                                        class="fa fa-trash" aria-hidden="true"></i></button>
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
        function delete_pg(button, name, url) {
            var index = button.parentNode.parentNode.rowIndex;

            if (confirm('{{ __('Are you sure you want to delete ') }}' + name + '?')) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    success: function (msg) {
                        document.getElementById("manage-port-groups-table").deleteRow(index);
                        toastr.success(msg);
                    },
                    error: function () {
                        toastr.error('{{ __('The port group could not be deleted') }}');
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
