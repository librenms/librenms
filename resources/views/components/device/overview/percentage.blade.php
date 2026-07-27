@props(['percent', 'warning' => null, 'left_text' => null, 'right_text' => null, 'shadow' => null, 'colors' => null])
@php
    $value = max(0, min(100, (float) $percent));
    $colors ??= \LibreNMS\Util\Color::percentage($value, $warning, '#');

    $shadowWidth = null;
    if ($shadow !== null) {
        $shadowWidth = max(0, min(100, (float) $shadow - $value));
    }
@endphp

<div {{ $attributes->class(['tw:flex tw:w-full tw:min-w-32 tw:flex-col tw:gap-1']) }}>
    <div
        class="tw:h-3 tw:w-full tw:overflow-hidden tw:rounded tw:shadow-[inset_0_1px_2px_rgba(0,0,0,0.1)]"
        style="background-color: {{ $colors['right'] }}"
        role="progressbar"
        aria-valuenow="{{ $value }}"
        aria-valuemin="0"
        aria-valuemax="100"
    >
        <div class="tw:flex tw:h-full">
            <div class="tw:h-full tw:shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)]" style="width: {{ $value }}%; background-color: {{ $colors['left'] }}"></div>
            @if ($shadowWidth !== null)
                <div class="tw:h-full" style="width: {{ $shadowWidth }}%; background-color: {{ $colors['middle'] }}"></div>
            @endif
        </div>
    </div>
    <div class="tw:flex tw:justify-between tw:whitespace-nowrap tw:text-xs">
        <span>{{ $left_text ?? round($value) . '%' }}</span>
        <span>{{ $right_text }}</span>
    </div>
</div>
