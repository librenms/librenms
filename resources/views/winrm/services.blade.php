@extends('layouts.librenmsv1')

@section('title', __('Services'))

@section('content')
    <x-panel id="WinRM-panel">
        <x-slot name="title">
            <i class="fa fa-windows" aria-hidden="true"></i> @lang('Services')
        </x-slot>
    </x-panel>

    <div class="container-fluid">
        <div class="table-responsive">
            <table id="winrm" class="table table-hover table-condensed table-striped">
                <thead>
                    <tr>
                        <th data-column-id="status" data-formatter="svc-status-icon"></th>
                        <th data-column-id="sysName" data-formatter="svc-sysName">@lang('Device')</th>
                        <th data-column-id="display_name" data-formatter="svc-display">@lang('Name')</th>
                        <th data-column-id="service_name" data-formatter="svc-name">@lang('Service')</th>
                        <th data-column-id="status" data-formatter="svc-status">@lang('Status')</th>
                        <th data-column-id="alerting" data-formatter="svc-alert">@lang('Alerts')</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection


@section('javascript')
    <script src="{{ url('/') }}/js/winrm.js"></script>
    <script>
        $(document).ready(function () {
            let winrm = new WinRM();
            winrm.LoadServices("{{ url('/') }}", null, "<?php echo $service_name ?>");
        });
    </script>
@endsection


@section('css')
<style>
    #users form { display:inline; }
</style>
@endsection