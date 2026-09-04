@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" :subtitle="__('VRFs')">
        <x-device.routing-tabs :device="$device" tab="vrf" />

        <x-option-bar name="{{ __('VRFs') }}" :options="$vrf_options" :selected="$selected_option" />

        <x-panel>
            <div class="table-responsive">
                <table class="table table-condensed table-hover tw:border-collapse">
                    <thead>
                        <tr>
                            <th class="tw:w-[200px]">{{ __('VRF') }}</th>
                            <th class="tw:w-[150px]">{{ __('Description') }}</th>
                            <th class="tw:w-[100px]">{{ __('RD') }}</th>
                            <th>{{ __('Interfaces') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($vrfs as $vrf)
                        <tr>
                            <td class="tw:font-bold">
                                {{ $vrf->vrf_name }}
                            </td>
                            <td>{{ $vrf->mplsVpnVrfDescription }}</td>
                            <td>{{ $vrf->mplsVpnVrfRouteDistinguisher }}</td>
                            <td>
                                @if($view === 'graphs')
                                    <div class="tw:flex tw:flex-wrap tw:gap-1">
                                        @foreach($vrf->ports as $port)
                                            <div class="tw:block tw:p-0.5 tw:m-0.5 tw:w-[139px] tw:min-w-[139px] tw:max-w-[139px] tw:h-[85px] tw:min-h-[85px] tw:max-h-[85px] tw:text-center tw:bg-[#e9e9e9] dark:tw:bg-dark-gray-300 tw:rounded">
                                                <div class="tw:font-bold">{{ $port->getShortLabel() }}</div>
                                                <x-port-link :port="$port">
                                                    <x-graph :port="$port" :type="'port_' . $graph" from="-2d" width="132" height="40" legend="no" />
                                                </x-port-link>
                                                <div class="tw:text-[9px] tw:truncate">{{ $port->ifAlias }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    @foreach($vrf->ports as $port)
                                        <x-port-link :port="$port">{{ $port->getShortLabel() }}</x-port-link>@if(! $loop->last), @endif
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="tw:text-center tw:p-5">
                                <em>{{ __('No VRFs found for this device.') }}</em>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-panel>
    </x-device.page>
@endsection
