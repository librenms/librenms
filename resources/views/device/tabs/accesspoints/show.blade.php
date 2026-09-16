@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <p>
            <a href="{{ route('device', ['device' => $device, 'tab' => 'accesspoints']) }}">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                {{ __('All access points') }}
            </a>
        </p>

        <x-panel title="{{ __('Access Point') }}">
            <div class="table-responsive">
                <table class="table table-condensed table-striped">
                    <tbody>
                    <tr>
                        <th scope="row">{{ __('Name') }}</th>
                        <td>{{ $accessPoint->name }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('MAC Address') }}</th>
                        <td>{{ $accessPoint->mac_addr }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('Radio') }}</th>
                        <td>{{ $accessPoint->type }} ({{ $accessPoint->radio_number }})</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('Channel') }}</th>
                        <td>{{ $accessPoint->channel }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('Clients') }}</th>
                        <td>{{ $accessPoint->numasoclients }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('Utilization') }}</th>
                        <td>{{ $accessPoint->radioutil }}%</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('Interference Index') }}</th>
                        <td>{{ $accessPoint->interference }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('Transmit Power') }}</th>
                        <td>{{ $accessPoint->txpow }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </x-panel>

        @foreach($graphs as $graph)
            <x-graph-row
                loading="lazy"
                columns="responsive"
                :type="$graph['type']"
                :title="$graph['title']"
                :vars="['id' => $accessPoint->accesspoint_id]"
            ></x-graph-row>
        @endforeach
    </x-device.page>
@endsection
