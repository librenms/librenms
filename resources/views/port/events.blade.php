@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" subtitle="{{ __('Eventlog') }}">
        <x-panel title="{{ $device->display }} - {{ $port->getLabel() }} / {{ __('Eventlog') }}">
            <div class="table-responsive">
                <table class="table table-hover table-condensed table-striped">
                    <thead>
                    <tr>
                        <th>{{ __('Timestamp') }}</th>
                        <th>{{ __('device.attributes.hostname') }}</th>
                        <th>{{ __('Port') }}</th>
                        <th>{{ __('Message') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($entries as $entry)
                        <tr>
                            <td>
                                <span class="alert-status {{ $entry->severity->labelClass() }}"></span>
                                {{ \LibreNMS\Util\Time::format($entry->datetime, 'compact') }}
                            </td>
                            <td>
                                @if($device->exists)
                                    <x-device-link :device="$device">{{ $device->shortDisplayName() }}</x-device-link>
                                @endif
                            </td>
                            <td><strong><x-port-link :port="$port" /></strong></td>
                            <td>{{ $entry->message }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-panel>
    </x-device.page>
@endsection
