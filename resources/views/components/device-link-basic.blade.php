<a href="{{ route('device', ['device' => $device->device_id]) }}" >{{ $slot->isNotEmpty() ? $slot : $device->displayName() }}</a>
