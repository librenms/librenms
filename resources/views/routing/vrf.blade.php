@extends('layouts.librenmsv1')

@section('title', __('VRFs'))

@section('content')
    <div class="container-fluid">
        <x-routing-tabs tab="vrf" />

        <x-option-bar name="{{ $name ? __('VRF') . ' ' . $name : __('VRFs') }}" :options="$options" :selected="$selected_option" />

        <x-panel>
            <x-table :rows="$vrfs" :empty="__('No VRFs found.')">
                <x-slot:head>
                    <tr>
                        <th class="tw:w-64">{{ __('VRF') }}</th>
                        <th class="tw:w-32">{{ __('RD') }}</th>
                        <th>{{ __('Interfaces') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @foreach($vrfs as $group)
                        @php($first = $group->first())
                        <tr>
                            <td>
                                <a href="{{ route('routing.vrf', ['vrf' => $first->vrf_name]) }}" class="list-large">{{ $first->vrf_name }}</a>
                                <br>
                                <span class="box-desc">{{ $first->mplsVpnVrfDescription }}</span>
                            </td>
                            <td class="box-desc">{{ $first->mplsVpnVrfRouteDistinguisher }}</td>
                            <td>
                                <table class="table table-hover table-striped table-condensed tw:mb-0">
                                    @foreach($group as $vrf)
                                        <tr>
                                            <td class="tw:w-48">
                                                <x-device-link :device="$vrf->device" tab="routing" section="vrf" />
                                            </td>
                                            <td>
                                                <x-routing.vrf-ports :ports="$vrf->ports" :graph="$graph" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-table>
        </x-panel>
    </div>
@endsection
