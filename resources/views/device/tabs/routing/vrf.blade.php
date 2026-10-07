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
                                <x-routing.vrf-ports :ports="$vrf->ports" :graph="$view === 'graphs' ? $graph : null" />
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
