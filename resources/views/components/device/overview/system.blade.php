@props(['device'])

<x-device.overview.panel :title="__('System')" icon="fa fa-id-card">
    <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
        @foreach([
            __('System Name') => $device->sysName,
            __('Assigned IP') => $device->overwrite_ip ?: $device->ip,
            __('Description') => $device->purpose,
            __('Hardware') => $device->hardware,
            __('Operating System') => trim(\App\Facades\LibrenmsConfig::getOsSetting($device->os, 'text') . ' ' . $device->version . ' ' . $device->features),
            __('Serial') => $device->serial,
            __('Object ID') => $device->sysObjectID,
            __('Contact') => $device->getAttrib('override_sysContact_bool') ? $device->getAttrib('override_sysContact_string') : $device->sysContact,
        ] as $label => $value)
            @if(filled($value))
                <div class="tw:grid tw:grid-cols-[1fr_2fr] tw:items-center tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                    <div class="tw:font-medium">{{ $label }}</div>
                    <div class="tw:min-w-0 tw:break-words">{{ $value }}</div>
                </div>
            @endif
        @endforeach
        @if($device->inserted)
            <div class="tw:grid tw:grid-cols-[1fr_2fr] tw:gap-2.5 tw:px-2 tw:py-2">
                <div class="tw:font-medium">{{ __('Device Added') }}</div>
                <div title="{{ $device->inserted }}">{{ $device->inserted->diffForHumans() }}</div>
            </div>
        @endif
        @if($device->last_discovered)
            <div class="tw:grid tw:grid-cols-[1fr_2fr] tw:gap-2.5 tw:px-2 tw:py-2">
                <div class="tw:font-medium">{{ __('Last Discovered') }}</div>
                <div title="{{ $device->last_discovered }}">{{ $device->last_discovered->diffForHumans() }}</div>
            </div>
        @endif
        @if($device->location)
            <div class="tw:grid tw:grid-cols-[1fr_2fr] tw:gap-2.5 tw:px-2 tw:py-2">
                <div class="tw:font-medium">{{ __('Location') }}</div>
                <div>{{ $device->location->display() }}</div>
            </div>
            @if($device->location->coordinatesValid())
                <x-geo-map class="tw:w-full!" height="180px" :lat="$device->location->lat" :lng="$device->location->lng" :zoom="14" readonly />
            @endif
        @endif
    </div>
</x-device.overview.panel>
