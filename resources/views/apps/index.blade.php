@extends('layouts.librenmsv1')

@section('title', __('Apps'))

@section('content')
<div class="container-fluid">
    <x-option-bar :name="__('Apps')" :options="$appOptions" :selected="$type ?? ''" />

    @if($content)
        {{-- a View in view data arrives here already rendered to a string, so it must not be escaped again --}}
        {!! $content !!}
    @elseif($type !== null)
        @if($instances)
            <x-option-bar :name="__('Instance')" :options="$instances" :selected="$instance ?? ''" />
        @endif
        @include('apps.instances')
    @else
        @forelse($groups as $appType => $group)
            <x-panel>
                <x-slot:heading>
                    <h3 class="panel-title">
                        <a href="{{ route('apps.index', ['app' => $appType]) }}">{{ $group->first()->displayName() }}</a>
                    </h3>
                </x-slot:heading>
                <div class="tw:flex tw:flex-wrap tw:gap-4">
                    @foreach($group as $application)
                        <div class="tw:max-w-full">
                            <a class="tw:block tw:font-semibold" href="{{ $application->deviceAppsUrl() }}">
                                <x-apps.state :state="$application->app_state" />
                                {{ $application->device->shortDisplayName() }}
                                @if(filled($application->app_instance))
                                    ({{ $application->app_instance }})
                                @endif
                            </a>
                            @if($previewGraphs[$appType])
                                <x-graph :type="$previewGraphs[$appType]" :vars="['id' => $application->app_id]" width="218" height="100"
                                    loading="lazy" popup :link="$application->deviceAppsUrl()" />
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-panel>
        @empty
            <x-panel><em>{{ __('No applications found.') }}</em></x-panel>
        @endforelse
    @endif
</div>
@endsection
