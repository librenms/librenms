@extends('device.submenu')

@section('tabcontent')
    <div class="table-responsive">
        <x-panel id="WinRM-panel">
            <x-slot name="title">
                <i class="fa fa-windows" aria-hidden="true"></i> @lang('WinRM')
            </x-slot>

            <div class="container-fluid">
                <ul class="nav nav-tabs">
                    <li role="presentation" @if( $data['page_id'] == 'software' ) class="active" @endif>
                        <a href="{{ route('device', [36, 'winrm', 'software']) }}">
                            <i class="fa fa-laptop fa-lg icon-theme" aria-hidden="true"></i>&nbsp;Software&nbsp;</a>
                    </li>
                    <li role="presentation" @if( $data['page_id'] == 'processes' ) class="active" @endif>
                        <a href="{{ route('device', [36, 'winrm', 'processes']) }}">
                            <i class="fa fa-microchip  fa-lg icon-theme" aria-hidden="true"></i>&nbsp;Processes&nbsp;</a>
                    </li>
                    <li role="presentation" @if( $data['page_id'] == 'services' ) class="active" @endif>
                        <a href="{{ route('device', [36, 'winrm', 'services']) }}">
                            <i class="fa fa-cogs fa-lg icon-theme" aria-hidden="true"></i>&nbsp;Services&nbsp;</a>
                    </li>
                </ul>

                <table id="winrm" class="table table-hover table-condensed table-striped">
                    @switch($data['page_id'])
                        @case('processes')
                            <thead>
                                <tr>
                                    <th data-column-id="name" data-formatter="process-name">@lang('Name')</th>
                                    <th data-column-id="username" data-formatter="process-username">@lang('username')</th>
                                    <th data-column-id="ws" data-formatter="process-ws">@lang('Physical Memory')</th>
                                </tr>
                            </thead>
                        @break
            
                        @case('services')
                            <thead>
                                <tr>
                                    <th data-column-id="status" data-formatter="svc-status-icon"></th>
                                    <th data-column-id="display_name" data-formatter="svc-display">@lang('Name')</th>
                                    <th data-column-id="service_name" data-formatter="svc-name">@lang('Service')</th>
                                    <th data-column-id="status" data-formatter="svc-status">@lang('Status')</th>
                                    <th data-column-id="alerting" data-formatter="svc-alert">@lang('Alerts')</th>
                                </tr>
                            </thead>
                        @break
            
                        @case('software')
                        @default
                            <thead>
                                <tr>
                                    <th data-column-id="name" data-formatter="soft-name">@lang('Name')</th>
                                    <th data-column-id="vendor" data-formatter="soft-vendor">@lang('Vendor')</th>
                                    <th data-column-id="description" data-formatter="soft-description">@lang('Description')</th>
                                    <th data-column-id="version" data-formatter="soft-version">@lang('Version')</th>
                                </tr>
                            </thead>
                        @break
                    @endswitch
                </table>
            </div>
        </x-panel>
    </div>
@endsection


@section('javascript')
    <script src="{{ url('/') }}/js/winrm.js"></script>
    <script>
        $(document).ready(function () {
            let winrm = new WinRM();
            @switch($data['page_id'])
                @case('processes')
                    winrm.LoadProcesses("{{ url('/') }}", "<?php echo $data['device_id'] ?>");
                @break

                @case('services')
                    winrm.LoadServices("{{ url('/') }}", "<?php echo $data['device_id'] ?>");
                @break
                
                @case('software')
                @default
                    winrm.LoadSoftware("{{ url('/') }}", "<?php echo $data['device_id'] ?>");
                @break
            @endswitch
        });
    </script>
@endsection
