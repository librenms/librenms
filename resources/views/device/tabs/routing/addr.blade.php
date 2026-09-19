@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" :subtitle="__('Addresses')">
        <x-device.routing-tabs :device="$device" tab="addr" />

        <x-panel>
            <div class="table-responsive">
                <table class="table table-hover table-condensed">
                    <thead>
                        <tr>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Port') }}</th>
                            <th>{{ __('Address') }}</th>
                            <th>{{ __('Description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ports as $row)
                            @php
                                $label = match ($row['status']) {
                                    'admindown' => 'warning',
                                    'down' => 'danger',
                                    default => 'success',
                                };
                                $port = $row['port'];
                            @endphp
                            <tr>
                                <td class="text-left">
                                    <span class="alert-status label-{{ $label }}" style="float:left;margin-right:10px;"></span>
                                    {{ $row['status'] }}
                                </td>
                                <td class="interface-{{ $row['status'] }}">
                                    <x-port-link :port="$port" />
                                </td>
                                <td class="interface-{{ $row['status'] }}">
                                    @foreach ($port->ipv4 as $address)
                                        {{ $address->ipv4_address }}/{{ $address->ipv4_prefixlen }}<br>
                                    @endforeach
                                    @foreach ($port->ipv6 as $address)
                                        {{ $address->ipv6_compressed }}/{{ $address->ipv6_prefixlen }}<br>
                                    @endforeach
                                </td>
                                <td class="interface-{{ $row['status'] }}">
                                    {{ $port->ifAlias }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-panel>
    </x-device.page>
@endsection
