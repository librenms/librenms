@extends('layouts.librenmsv1')

@section('title', $title)

@section('content')
    <div class="lnms-wireless">
        <div class="panel panel-default panel-condensed lnms-wireless-panel">
            <div class="panel-heading">
                <div class="lnms-wireless-toolbar">
                    <div class="lnms-wireless-toolbar__left">
                        <x-option-bar border="none" name="{{ __('Health') }}" :options="$metrics" :selected="$metric"></x-option-bar>
                    </div>
                    <div class="lnms-wireless-toolbar__right">
                        <x-option-bar border="none" :options="$views" :selected="$view"></x-option-bar>
                    </div>
                </div>
            </div>
            <div class="table-responsive lnms-wireless-list">
                <table id="sensors" class="table table-hover table-condensed">
                    <thead>
                    <tr>
                        <th data-column-id="device_hostname">{{ __('Device') }}</th>
                        <th data-column-id="sensor_descr">{{ __('Sensor') }}</th>
                        <th data-column-id="graph" data-sortable="false" data-searchable="false"></th>
                        <th data-column-id="alert" data-sortable="false" data-searchable="false"></th>
                        <th data-column-id="sensor_current">{{ __('Current') }}</th>
                        <th data-column-id="sensor_limit_low" data-searchable="false">{{ __('Low Limit') }}</th>
                        <th data-column-id="sensor_limit" data-searchable="false">{{ __('High Limit') }}</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <script>
        var grid = $("#sensors").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            post: function ()
            {
                return {
                    view: '{{ $view }}',
                    class: '{{ $metric }}'
                };
            },
            url: "{{ route('table.wireless') }}"
        });
    </script>
@endsection
