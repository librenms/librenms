@if($device && Gate::allows('view', $device))
    <x-device-link :device="$device">{{ $label }}</x-device-link>
@else
    {{ $label }}
@endif
