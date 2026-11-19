@extends('layouts.librenmsv1')

@section('title', __('Apps'))

@section('content')
<div class="container-fluid">
    <x-panel>
        <x-slot:heading class="tw:flex tw:flex-wrap tw:items-baseline tw:gap-x-4 tw:gap-y-2">
            <strong>{{ __('Apps') }}</strong>
            <a href="{{ route('apps.index') }}" @if($type === null) class="pagemenu-selected" aria-current="page" @endif>{{ __('Overview') }}</a>
            @foreach($apps as $appType => $group)
                <a href="{{ route('apps.index', ['app' => $appType]) }}"
                    @class(['pagemenu-selected' => $type === $appType])
                    @if($type === $appType && $instance === null) aria-current="page" @endif>
                    <x-apps.state :state="$group->first()->app_state" />
                    {{ $group->first()->displayName() }}
                </a>
            @endforeach
        </x-slot:heading>
    </x-panel>

    @if($content)
        {{ $content }}
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
            <p class="text-muted">{{ __('No applications found.') }}</p>
        @endforelse
    @endif
</div>
@endsection
