@extends('layouts.librenmsv1')

@section('title', __('OTV'))

@section('content')
    <div class="container-fluid">
        <x-routing-tabs tab="cisco-otv" />

        @forelse($devices as $device_id => $components)
            <x-panel>
                <x-slot:title>
                    <x-device-link :device="$components->first()->device" tab="routing" section="cisco-otv" /> - {{ __('Overlays & Adjacencies') }}
                </x-slot:title>
                <x-routing.cisco-otv-overlays :components="$components" :id="'overlays-' . $device_id" />
            </x-panel>
        @empty
            <x-panel>
                <em class="text-muted">{{ __('No OTV overlays found.') }}</em>
            </x-panel>
        @endforelse
    </div>
@endsection
