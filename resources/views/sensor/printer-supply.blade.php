@extends('layouts.librenmsv1')

@section('title', $title)

@section('content')
    <div class="lnms-health">
        <div class="panel panel-default panel-condensed lnms-health-panel">
            <div class="panel-heading">
                <div class="lnms-health-toolbar">
                    <div class="lnms-health-toolbar__left">
                        <x-option-bar border="none" name="{{ __('Health') }}" :options="$metrics" :selected="$metric"></x-option-bar>
                    </div>
                    <div class="lnms-health-toolbar__right">
                        <x-option-bar border="none" :options="$views" :selected="$view"></x-option-bar>
                    </div>
                </div>
            </div>
            <div class="table-responsive lnms-health-list">
                <table id="printer" class="table table-hover table-condensed"
                       data-url="{{ route('table.printer-supply') }}">
                    <thead>
                    <tr>
                        <th data-column-id="device_hostname">{{ __('Device') }}</th>
                        <th data-column-id="supply_descr">{{ __('Descr') }}</th>
                        <th data-column-id="supply_type">{{ __('Type') }}</th>
                        <th data-column-id="graph" data-sortable="false" data-searchable="false"></th>
                        <th data-column-id="supply_current" data-searchable="false">{{ __('Supply') }}</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <script>
        var grid = $("#printer").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            post: function ()
            {
                return {
                    view: '{{ $view }}'
                };
            }
        });
    </script>

@endsection
