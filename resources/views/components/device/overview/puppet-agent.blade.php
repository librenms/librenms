@if($application)
    <x-device.overview.panel :title="__('Puppet Agent')" icon="fa fa-cogs"
        :href="route('device', ['device' => $device->device_id, 'tab' => 'apps', 'vars' => 'app=puppet-agent'])">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            <div class="tw:grid tw:grid-cols-3 tw:gap-3 tw:p-3 tw:text-center tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                @foreach([
                    'last_run' => [__('Last run'), 'application_puppet-agent_last_run', ($metrics['last_run_last_run'] ?? 0) . 'min'],
                    'runtime' => [__('Runtime'), 'application_puppet-agent_time', ($metrics['time_total'] ?? 0) . 's'],
                    'resources' => [__('Resources'), 'application_puppet-agent_resources', $metrics['resources_total'] ?? 0],
                ] as [$title, $type, $value])
                    <div><x-graph :type="$type" :vars="['id' => $application->app_id]" width="100" height="24" popup :popup-title="$title" /><div>{{ $title }}: {{ $value }}</div></div>
                @endforeach
            </div>
            <div class="tw:grid tw:grid-cols-3 tw:gap-3 tw:p-3 tw:text-center tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                <x-graph type="application_puppet-agent_events" :vars="['id' => $application->app_id]" width="100" height="24" popup
                         :popup-title="$device->display . ' - ' . __('Change Events')" />
                <span class="tw:text-blue-600">{{ __('Success') }}: {{ $metrics['events_success'] ?? 0 }}</span>
                <span class="tw:text-red-500">{{ __('Failure') }}: {{ $metrics['events_failure'] ?? 0 }}</span>
                <span class="tw:col-start-2">{{ __('Total') }}: {{ $metrics['events_total'] ?? 0 }}</span>
            </div>
        </div>
    </x-device.overview.panel>
@endif
