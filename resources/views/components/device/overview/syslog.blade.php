@props(['device', 'syslogs'])

@if($syslogs->isNotEmpty())
    <x-device.overview.panel :title="__('Recent Syslog')" icon="fa fa-clone" :href="route('device.syslog', ['device' => $device->device_id])">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            @foreach($syslogs as $entry)
                <div class="tw:grid tw:grid-cols-[auto_1fr] tw:items-center tw:gap-2.5 tw:px-2 tw:py-2">
                    <span class="tw:whitespace-nowrap tw:italic">{{ \LibreNMS\Util\Time::format($entry->timestamp, 'compact') }}</span>
                    <span><strong>{{ $entry->program }}</strong>&nbsp;&nbsp;&nbsp;{{ $entry->msg }}</span>
                </div>
            @endforeach
        </div>
    </x-device.overview.panel>
@endif
