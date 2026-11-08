@extends('layouts.librenmsv1')

@section('title', __('BGP'))

@section('content')
    <div class="container-fluid">
        <x-routing-tabs tab="bgp" />

        <div class="panel panel-default">
            <div class="panel-heading tw:flex tw:flex-wrap tw:gap-x-4">
                <x-option-bar border="none" name="{{ __('BGP') }}" :options="$type_options" :selected="$type" />
                <x-option-bar border="none" name="{{ __('Admin') }}" :options="$admin_options" :selected="$admin_status" />
                <x-option-bar border="none" name="{{ __('State') }}" :options="$state_options" :selected="$state" />
                <x-option-bar border="none" name="{{ __('Graphs') }}" :options="$graph_options" :selected="$graph ?? 'none'" />
            </div>
            <x-routing.bgp-peer-table :peers="$peers" :graph="$graph" show-device />
        </div>
    </div>
@endsection
