@extends('layouts.librenmsv1')

@section('title')
    @lang('Public Devices')
@append

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">

    <div id="public-status">
        <div class="well">
            <div class="status-header">@lang('System Status')
            <button class="btn btn-default pull-right" type="submit" id="ToggleLogon">@lang('Logon')</button>
            </div>
        </div>
        <x-panel>
            <div class="table-responsive">
                <table class="table table-condensed">
                    <tr>
                        <th></th>
                        <th id="icon-header"></th>
                        <th>@lang('Device')</th>
                        <th>@lang('Platform')</th>
                        <th>@lang('Uptime')/@lang('Location')</th>
                    </tr>

                    @foreach($devices as $device)
                        <tr>
                            <td><span class="alert-status {{ $device->status ? 'label-success' : 'label-danger' }}"></span></td>
                            <td><img src="{{ asset($device->icon) }}" width="32px" height="32px"></td>
                            <td class="device-name">{{ $device->displayName() }}</td>
                            <td>{{ $device->hardware }} {{ $device->features }}</td>
                            <td>{{ $device->formatDownUptime(true) }}<br>{{ substr($device->location, 0, 32) }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </x-panel>
    </div>

    <div id="public-logon" style="display:none;">
        <div class="well">
            <div class="status-header">@lang('Logon')
                <button class="btn btn-default pull-right" type="submit" id="ToggleStatus">@lang('Status')</button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-offset-4 col-md-4">
                @include('auth.login-form')
            </div>
        </div>
    </div>
        </div>
    </div>
</div>
@endsection

@section('css')
    <style>
        body {
            padding-top: 0;
        }

        .status-header {
            font-size: 1.7em;
            line-height: 34px;
        }

        #icon-header {
            width: 32px;
        }

        .device-name {
            font-size: 1.2em;
        }
    </style>
@endsection

@section('javascript')
            <script class="code" type="text/javascript">
                $(document).ready(function () {
                    $("#ToggleLogon").on("click", function () {
                        document.getElementById('public-logon').style.display = "block";
                        document.getElementById('public-status').style.display = "none";
                    });
                    $("#ToggleStatus").on("click", function () {
                        document.getElementById('public-logon').style.display = "none";
                        document.getElementById('public-status').style.display = "block";
                    });
                });
            </script>
@endsection
