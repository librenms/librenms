@forelse($applications as $application)
    <x-panel>
        <x-slot:heading class="tw:flex tw:flex-wrap tw:items-baseline tw:justify-between tw:gap-2">
            <h3 class="panel-title">
                <a href="{{ $application->deviceAppsUrl() }}">
                    <x-apps.state :state="$application->app_state" />
                    {{ $application->device->display }}
                </a>
            </h3>
            <small class="text-muted">{{ $application->app_instance }} {{ $application->app_status }}</small>
        </x-slot:heading>
        @if($graphs = $definition->overviewGraphs($application))
            <div class="tw:flex tw:flex-wrap tw:gap-2">
                @foreach($graphs as $graphType)
                    <x-graph :type="$graphType" :vars="['id' => $application->app_id]" width="220" height="100"
                        loading="lazy" popup :link="$application->deviceAppsUrl()" />
                @endforeach
            </div>
        @endif
    </x-panel>
@empty
    <p class="text-muted">{{ __('No applications found.') }}</p>
@endforelse
