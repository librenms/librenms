@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-panel>
        <x-slot:heading class="tw:flex tw:items-center tw:justify-between tw:flex-wrap">
            <x-option-bar :name="__('Services')" :options="$data['options']" :selected="$data['view']" border="none" class="tw:inline-block" />
            @can('create', \App\Models\Service::class)
                <button type="button" class="btn btn-success btn-sm" x-data x-on:click="$dispatch('service-create')">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('Add Service') }}
                </button>
            @endcan
        </x-slot:heading>
        <x-slot:table>
            <x-service.table :services="$data['services']" :graphs="$data['view'] === 'details'" />
        </x-slot:table>
    </x-panel>

    <x-service.modals :device="$device" />
</x-device.page>
@endsection
