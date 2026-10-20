@props([
    'port' => 'port',
])

{{-- port up/down/admin down badge for Alpine rendered ports, port is an Alpine expression --}}
<span {{ $attributes->class(['tw:inline-block tw:px-1.5 tw:rounded-[3px] tw:font-semibold tw:text-white']) }}
      :class="{{ $port }}.ifAdminStatus === 'down' ? 'tw:bg-gray-500' : ({{ $port }}.ifOperStatus === 'up' ? 'tw:bg-green-600' : 'tw:bg-red-600')"
      x-text="{{ $port }}.ifAdminStatus === 'down' ? 'admin down' : ({{ $port }}.ifOperStatus ?? 'unknown')"></span>
