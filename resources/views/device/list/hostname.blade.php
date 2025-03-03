<x-device-link :device="$device"></x-device-link>
@if($detailed)
    <br />
    {{ $device->name() }}
@endif
