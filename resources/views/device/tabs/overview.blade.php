@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <div class="tw:grid tw:grid-cols-1 tw:gap-4 tw:md:grid-cols-2">
            <div class="tw:min-w-0">
                <x-device.overview.system :device="$device" />
                <x-device.overview.maps :maps="$device->maps" />
                <x-device.overview.groups :groups="$device->groups" />
                <x-device.overview.puppet-agent :device="$device" :application="$data['puppetAgent']" />

                {!! $data['pluginHtml'] !!}
                @foreach($data['pluginViews'] as $pluginView)
                    {{ $pluginView }}
                @endforeach

                <x-device.overview.ports :device="$device" :ports="$data['activePorts']" />
                <x-device.overview.availability :device="$device" />
                <x-device.overview.transceivers :device="$device" />
                <x-device.overview.ping :device="$device" :visible="$data['pingGraph']" />
            </div>
            <div class="tw:min-w-0">
                <x-device.overview.processors :device="$device" />
                <x-device.overview.memory :device="$device" />
                <x-device.overview.storage :device="$device" />
                <x-device.overview.printer-supplies :device="$device" />
                <x-device.overview.sensors :device="$device" :sensor-groups="$data['sensorGroups']" />
                <x-device.overview.eventlog :device="$device" :eventlogs="$data['eventlogs']" :ports="$data['eventPorts']" />
                <x-device.overview.services :device="$device" />
                <x-device.overview.syslog :device="$device" :syslogs="$data['syslogs']" />
                <x-device.overview.graylog :device="$device" :config="$data['graylog']" />
            </div>
        </div>
    </x-device.page>
@endsection
