@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" :subtitle="__('IPSEC Tunnels')">
        <x-device.routing-tabs :device="$device" tab="ipsec-tunnels" />

        <x-option-bar name="{{ __('IPSEC Tunnels') }}" :options="$ipsec_options" :selected="$selected_option" />

        @if($view === 'graphs')
            @forelse($tunnels as $entry)
                <x-panel title="{{ $entry['local_addr'] }} » {{ $entry['peer_addr'] }}">
                    <div class="row">
                        <x-graph-row :type="'ipsectunnel_' . $graph" :vars="['id' => $entry['id']]" />
                    </div>
                </x-panel>
            @empty
                <div class="alert alert-info">{{ __('No IPsec tunnels found for this device.') }}</div>
            @endforelse
        @else
            <x-panel>
                <div class="table-responsive">
                    <table class="table table-condensed table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Local Identity') }}</th>
                                <th>{{ __('Remote Identity') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($tunnels as $entry)
                            <tr>
                                <td>{{ $entry['local_addr'] }}</td>
                                <td>{{ $entry['peer_addr'] }}</td>
                                <td>{{ $entry['tunnel_name'] }}</td>
                                <td>
                                    <span class="label label-{{ $entry['status_label'] }}">{{ $entry['tunnel_status'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="tw:text-center tw:p-5">
                                    <em>{{ __('No IPsec tunnels found for this device.') }}</em>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-panel>
        @endif
    </x-device.page>
@endsection
