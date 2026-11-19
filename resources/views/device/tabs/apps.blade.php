@extends('layouts.librenmsv1')

@section('title', __('Apps'))

@section('content')
<x-device.page :device="$device">
    @if($selected)
        <x-panel>
            <x-slot:heading class="tw:flex tw:flex-wrap tw:items-baseline tw:gap-x-4 tw:gap-y-2">
                <strong>{{ __('Apps') }}</strong>
                @foreach($applications as $application)
                    <a href="{{ $application->deviceAppsUrl() }}"
                        @class(['pagemenu-selected' => $application->is($selected)])
                        @if($application->is($selected)) aria-current="page" @endif>
                        <x-apps.state :state="$application->app_state" />
                        {{ $application->displayName() }}
                        @if(filled($application->app_instance))
                            ({{ $application->app_instance }})
                        @endif
                    </a>
                @endforeach
            </x-slot:heading>
        </x-panel>

        @include('apps.device-content')
    @else
        <p class="text-muted">{{ __('No applications found.') }}</p>
    @endif
</x-device.page>
@endsection
