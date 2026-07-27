@props(['device', 'eventlogs', 'ports'])

<x-device.overview.panel :title="__('Recent Events')" icon="fa fa-bookmark" :href="route('device.eventlog', ['device' => $device->device_id])">
    <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
        @forelse($eventlogs as $entry)
            @php
                $severityClass = match($entry->severity) {
                    \LibreNMS\Enum\Severity::Ok => 'success',
                    \LibreNMS\Enum\Severity::Warning => 'warning',
                    \LibreNMS\Enum\Severity::Error => 'danger',
                    default => 'info',
                };
                $port = $entry->type === 'interface' ? $ports->get($entry->reference) : null;
            @endphp
            <div class="tw:grid tw:grid-cols-[auto_auto_150px_1fr] tw:items-center tw:gap-2.5 tw:px-2 tw:py-2">
                <span class="alert-status {{ $severityClass }}"></span>
                <span class="tw:whitespace-nowrap">{{ \LibreNMS\Util\Time::format($entry->datetime, 'compact') }}</span>
                <span class="tw:min-w-0 tw:truncate">@if($port)<x-port-link :port="$port" />@endif</span>
                <span>{{ $entry->message }}</span>
            </div>
        @empty
            <div class="tw:p-3 tw:text-gray-500">{{ __('No recent events') }}</div>
        @endforelse
    </div>
</x-device.overview.panel>
