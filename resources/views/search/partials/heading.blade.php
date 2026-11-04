{{-- which host this is, the title of the results --}}
@php($heading = $name ?? ($host->ips[0] ?? null)?->compressed() ?? \LibreNMS\Util\Mac::parse(($host->macs[0] ?? null)?->hex)->readable())
<h2 class="tw:my-0! tw:text-lg! tw:font-semibold! tw:wrap-anywhere" @if($nameUrl) x-init="showName($el, @js($nameUrl))" @elseif(! $name && ! $device && ! $host->ips) x-text="formatMac(@js($host->macs[0]->hex))" @endif>@if($device)<a href="{{ route('device', ['device' => $device->device_id]) }}" x-data="deviceLink({device_id: {{ $device->device_id }}})">{{ $name ?? $device->display }}</a>@else{{ $heading }}@endif</h2>
@if($host->severalMacs)
    <span class="label label-default tw:cursor-help" title="{{ __('search.host.several_macs_help') }}">{{ trans_choice('search.macs_count', count($host->macs)) }}</span>
@endif
