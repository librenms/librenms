@extends('layouts.librenmsv1')

@section('title', __('Processes'))

@section('content')
        <x-panel id="WinRM-panel">
            <x-slot name="title">
                <i class="fa fa-windows" aria-hidden="true"></i> @lang('Processes')
            </x-slot>
        </x-panel>

    <div class="container-fluid">
        <div class="table-responsive">
            <table id="winrm" class="table table-hover table-condensed table-striped">
                <thead>
                    <tr>
                        <th data-column-id="sysName" data-formatter="process-sysName">@lang('Device')</th>
                        <th data-column-id="name" data-formatter="process-name">@lang('Name')</th>
                        <th data-column-id="username" data-formatter="process-username">@lang('username')</th>
                        <th data-column-id="ws" data-formatter="process-ws">@lang('Physical Memory')</th>
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
            winrm.LoadProcesses("{{ url('/') }}", null, "<?php echo $process_name ?>");
        });
    </script>
@endsection


@section('css')
<style>
    #users form { display:inline; }
</style>
@endsection
