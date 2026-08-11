@props(['device', 'syslogs'])

@if($syslogs->isNotEmpty())
    <x-device.overview.panel :title="__('Recent Syslog')" icon="fa fa-clone" :href="route('device.syslog', ['device' => $device->device_id])">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            @foreach($syslogs as $entry)
                <div class="tw:flex tw:items-center tw:gap-2.5 tw:px-2 tw:py-1 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                    <span class="tw:shrink-0 tw:whitespace-nowrap tw:italic">{{ \LibreNMS\Util\Time::format($entry->timestamp, 'compact') }}</span>
                    <span class="tw:min-w-0 tw:flex-1"><strong>{{ $entry->program }}</strong>&nbsp;&nbsp;&nbsp;{{ $entry->msg }}</span>
                </div>
            @endforeach
        </div>
    </x-device.overview.panel>
@endif
