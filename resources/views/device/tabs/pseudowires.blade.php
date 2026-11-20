@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-option-bar name="{{ __('Pseudowires') }}" :options="$options" :selected="$view" />

    @include('pseudowires.table', ['showDevice' => false])
</x-device.page>
@endsection
