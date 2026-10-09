@extends('layouts.librenmsv1')

@section('title', $title)

@section('content')
    <div class="container-fluid">
        <x-routing-tabs :tab="$tab" />

        <x-panel>
            <x-table striped :rows="$instances" :empty="__('No :protocol instances found.', ['protocol' => $title])">
                <x-slot:head>
                    <tr>
                        <th>{{ __('Device') }}</th>
                        <th>{{ __('Router ID') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('ABR') }}</th>
                        <th>{{ __('ASBR') }}</th>
                        <th>{{ __('Areas') }}</th>
                        <th>{{ __('Ports (Enabled)') }}</th>
                        <th>{{ __('Neighbours') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @foreach($instances as $instance)
                        <tr>
                            <td><x-device-link :device="$instance['device']" tab="routing" :section="$tab" /></td>
                            <td><strong>{{ $instance['router_id'] }}</strong></td>
                            <td><span @class(['label', 'label-success' => $instance['admin_status'] === 'enabled', 'label-default' => $instance['admin_status'] !== 'enabled'])>{{ $instance['admin_status'] }}</span></td>
                            <td><span @class(['label', 'label-success' => $instance['abr_status'] === 'true', 'label-default' => $instance['abr_status'] !== 'true'])>{{ $instance['abr_status'] }}</span></td>
                            <td><span @class(['label', 'label-success' => $instance['asbr_status'] === 'true', 'label-default' => $instance['asbr_status'] !== 'true'])>{{ $instance['asbr_status'] }}</span></td>
                            <td>{{ $instance['area_count'] }}</td>
                            <td>{{ $instance['port_count'] }} ({{ $instance['port_enabled_count'] }})</td>
                            <td>{{ $instance['nbr_count'] }}</td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-table>
        </x-panel>
    </div>
@endsection
