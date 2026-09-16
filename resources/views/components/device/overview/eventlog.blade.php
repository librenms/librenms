<x-device.overview.panel :title="__('Recent Events')" icon="fa fa-bookmark" :href="route('device.eventlog', ['device' => $device->device_id])">
    <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
        @forelse($rows as $row)
            <div class="tw:flex tw:items-center tw:gap-2.5 tw:px-2 tw:py-1 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                <span class="alert-status {{ $row['severityClass'] }}"></span>
                <span class="tw:shrink-0 tw:whitespace-nowrap">{{ \LibreNMS\Util\Time::format($row['entry']->datetime, 'compact') }}</span>
                <span class="tw:w-36 tw:shrink-0 tw:truncate" title="{{ $row['port']?->getLabel() }}">
                    @if($row['port'])<strong><x-port-link :port="$row['port']" /></strong>@endif
                </span>
                <span class="tw:min-w-0 tw:flex-1">{{ $row['entry']->message }}</span>
            </div>
        @empty
            <div class="tw:p-3 tw:text-gray-500">{{ __('No recent events') }}</div>
        @endforelse
    </div>
</x-device.overview.panel>
