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
                <table id="storage" class="table table-hover table-condensed diskio">
                    <thead>
                    <tr>
                        <th data-column-id="device_hostname">{{ __('Device') }}</th>
                        <th data-column-id="diskio_descr">{{ __('Storage') }}</th>
                        <th data-column-id="bits_graph" data-sortable="false" data-searchable="false">{{ __('Bits') }}</th>
                        <th data-column-id="ops_graph" data-sortable="false" data-searchable="false">{{ __('Ops') }}</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <script>
        var grid = $("#storage").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            post: function ()
            {
                return {
                    view: '{{ $view }}'
                };
            },
            url: "<?php echo route('table.diskio') ?>"
        });
    </script>

@endsection
