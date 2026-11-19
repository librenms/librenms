@extends('layouts.librenmsv1')

@section('title', __('MPLS'))

@section('content')
    <div class="container-fluid">
        <x-routing-tabs tab="mpls" />

        <x-option-bar name="{{ __('MPLS') }}" :options="$mpls_options" :selected="$view" />

        <x-panel>
            <x-routing.mpls-table :view="$view" :items="$items" show-device />
        </x-panel>
    </div>
@endsection
