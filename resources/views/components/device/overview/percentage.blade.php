@props(['percent', 'warning' => null, 'label' => null])

@php
    $value = max(0, min(100, (float) $percent));
    $barColor = $warning !== null && $value >= $warning ? 'tw:bg-red-500' : ($value >= 75 ? 'tw:bg-orange-400' : 'tw:bg-green-500');
@endphp

<div {{ $attributes->class(['tw:flex tw:min-w-32 tw:items-center tw:gap-2']) }}>
    <div class="tw:h-2.5 tw:flex-1 tw:overflow-hidden tw:rounded-full tw:bg-gray-200 tw:dark:bg-dark-gray-200"
         role="progressbar" aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="100">
        <div class="tw:h-full {{ $barColor }}" style="width: {{ $value }}%"></div>
    </div>
    <span class="tw:whitespace-nowrap tw:text-xs">{{ $label ?? round($value) . '%' }}</span>
</div>
