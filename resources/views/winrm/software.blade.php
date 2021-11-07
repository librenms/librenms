@extends('layouts.librenmsv1')

@section('title', __('Software'))

@section('content')
    <x-panel id="WinRM-panel">
        <x-slot name="title">
            <i class="fa fa-windows" aria-hidden="true"></i> @lang('Software')
        </x-slot>
    </x-panel>

    <div class="container-fluid">
        <div class="table-responsive">
            <table id="winrm" class="table table-hover table-condensed table-striped">
                <thead>
                    <tr>
                        <th data-column-id="sysName" data-formatter="soft-sysName">@lang('Device')</th>
                        <th data-column-id="name" data-formatter="soft-name">@lang('Name')</th>
                        <th data-column-id="vendor" data-formatter="soft-vendor">@lang('Vendor')</th>
                        <th data-column-id="description" data-formatter="soft-description">@lang('Description')</th>
                        <th data-column-id="version" data-formatter="soft-version">@lang('Version')</th>
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
            winrm.LoadSoftware("{{ url('/') }}", null, "<?php echo $software_id ?>", "<?php echo $software_version ?>", "<?php echo $software_vendor ?>");
        });
    </script>
@endsection


@section('css')
<style>
    #users form { display:inline; }
</style>
@endsection
