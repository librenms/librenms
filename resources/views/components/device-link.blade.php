<x-popup>
    <a href="{{ route('device', ['device' => $device->device_id, 'tab' => $tab, 'section' => $section]) }}">
        {{ $slot->isNotEmpty() ? $slot : $device->displayName() }}
    </a>
    <x-slot name="title">
        {{ $device->displayName() }}
    </x-slot>
    <x-slot name="body">
        Some body
    </x-slot>
</x-popup>
