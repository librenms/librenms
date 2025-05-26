@extends('layouts.librenmsv1')

@section('title', $title)

@section('content')
    <div class="panel panel-default panel-condensed">
        <div class="panel-heading">
            <div class="row" style="padding:0px 10px 0px 10px;">
                <div class="pull-left">
                    <x-option-bar border="none" name="Health" :options="$metrics" :selected="$metric"></x-option-bar>
                </div>

                <div class="pull-right">
                    <x-option-bar border="none" :options="$views" :selected="$view"></x-option-bar>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table id="sensors" class="table table-hover table-condensed"
                   data-url="{{ route('table.sensors') }}" data-params="class={{ $metric }}">
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
    <script>
        var grid = $("#sensors").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            post: function () {
                return {
                    view: '{{ $view }}',
                    class: '{{ $metric }}'
                };
            }
        });
    </script>
@endsection
