@extends('layouts.librenmsv1')

@section('title', __('Overview'))

@section('content')

<div class="row">
@if (Config::get('vertical_summary'))
    <div class="col-md-9">
@else
    <div class="col-md-8">
@endif

<div class="row">
    <div class="col-md-12">
        <div class=front-page>
            <div class="status-boxes">

    @foreach ($devices_down as $device)
        <div class="front-box device-down">
            {!! \LibreNMS\Util\Url::deviceLink($device, $device->shortDisplayName()) !!}
            <br />
            <span class=list-device-down>@lang('Device Down')</span>
            <br />
            <span class=body-date-1>{{ \LibreNMS\Util\StringHelpers::shortenText($device->location, 20) }}</span>
        </div>
    @endforeach

    @foreach ($ports_down as $port)
        <div class="front-box alert alert-danger">
            {!! \LibreNMS\Util\Url::deviceLink($port->device, $port->device->shortDisplayName()) !!}
            <br />
            <span class="interface-updown">@lang('Port Down')</span>
            <br />
            {!! \LibreNMS\Util\Url::PortLink($port) !!}
            @if($port->ifAlias)
                <br />
                <span class="body-date-1">{{ \LibreNMS\Util\StringHelpers::shortenText($port->getLabel(), 20) }}</span>
            @endif
        </div>
    @endforeach

    @foreach ($services_down as $service)
        <div class="front-box service-down">
            {!! \LibreNMS\Util\Url::deviceLink($service->device, $service->device->shortDisplayName()) !!}
            <span class=service-down>@lang('Service Down')</span>
            {{ $service->service_type }}
        </div>
    @endforeach

    @foreach ($bgp_down as $bgp)
        <div class="front-box bgp-down">
            {!! \LibreNMS\Util\Url::deviceLink($bgp->device, $bgp->device->shortDisplayName()) !!}
            <span class="bgp-down">@lang('BGP Down')</span>
            <span class="{{ (strstr($bgp->bgpPeerIdentifier, ':') ? 'front-page-bgp-small' : 'front-page-bgp-normal') }}">
                {{ $bgp->bgpPeerIdentifier }}
            </span>
            <br />
            <span class="body-date-1">AS{{ \LibreNMS\Util\StringHelpers::shortenText($bgp->bgpPeerRemoteAs . ' ' . $bgp->astext, 14) }}</span>
        </div>
    @endforeach

    @foreach ($devices_uptime as $device)
        <div class="front-box device-rebooted">
            {!! \LibreNMS\Util\Url::deviceLink($device, $device->shortDisplayName()) !!}
            <span class="device-rebooted">@lang('Device Rebooted')</span>
            <br />
            <span class="body-date-1">{{ $device->formatDownUptime(true) }}</span>
        </div>
    @endforeach

@if(
    empty($devices_down) &&
    empty($ports_down) &&
    empty($services_down) &&
    empty($bgp_down) &&
    empty($devices_uptime)
)
    <h5>Nothing here yet</h5>
    <p class=welcome>
        This is where status notifications about devices and services would normally go.
        You might have none because you run such a great network, or perhaps you've just started using {{ Config::get('project_name') }}
        If you're new to {{ Config::get('project_name') }}, you might
        want to start by adding one or more devices in the Devices menu.
    </p>
@endif


@if (count($syslog))
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            &nbsp;
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <x-panel title="{{ __('Syslog entries') }}">
                <x-slot name="table">
                    <table class="table table-hover table-condensed table-striped">
                    @foreach ($syslog as $entry)
                        <tr>
                            <td>{{ $entry->date }}</td>
                            <td><strong>{!! \LibreNMS\Util\Url::deviceLink($entry->device) !!}</strong></td>
                            <td><strong>{{ $entry->program }} : </strong> {{ $entry->msg }}</td>
                        </tr>
                    @endforeach
                    </table>
                </x-slot>
            </x-panel>
        </div>
   </div>
</div>
@endif

</div>
</div>
</div>
</div>
</div>
</div>

@endsection
