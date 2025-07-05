<a href="{{ route('device', $device->device_id) }}" class="{{ $class }}" x-data="deviceLink()">{{ $device->displayName() }}</a>
<br />
{{ $extra }}
