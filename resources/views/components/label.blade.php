<span {{ $attributes->class(['label', $statusClass]) }}>
    {{ $slot }}
    @isset($badge)
        <span {{ $badge->attributes->class('badge') }}>{{ $badge }}</span>
    @endisset
</span>
