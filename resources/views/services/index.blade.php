@extends('layouts.librenmsv1')

@section('title', __('Services'))

@section('content')
<div class="container-fluid">
    <x-service.manager>
        <x-panel>
            <x-slot:heading class="tw:flex tw:items-center tw:justify-between tw:flex-wrap">
                <x-option-bar :name="__('Services')" :options="$states" :selected="$state" border="none" class="tw:inline-block" />
                @can('create', \App\Models\Service::class)
                    <button type="button" class="btn btn-success btn-sm" x-bind:disabled="busy" x-on:click="createService()">
                        <i class="fa fa-plus" aria-hidden="true"></i> {{ __('Add Service') }}
                    </button>
                @endcan
            </x-slot:heading>

            @forelse($devices as $device)
                <x-panel>
                    <x-slot:heading>
                        <h3 class="panel-title"><x-device-link :device="$device" tab="services" /></h3>
                        {{ $device->sysName }}
                    </x-slot:heading>
                    <x-slot:table>
                        <x-service.table :services="$device->services" />
                    </x-slot:table>
                </x-panel>
            @empty
                <em>{{ __('No services found.') }}</em>
            @endforelse

            @if($devices->hasPages())
                {{ $devices->links() }}
            @endif
        </x-panel>
    </x-service.manager>
</div>
@endsection
