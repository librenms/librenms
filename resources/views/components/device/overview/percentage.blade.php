@if($graphType)
<x-popup {{ $attributes->class(['tw:block tw:w-full']) }} target-class="tw:block tw:w-full">
    <a href="{{ $graphUrl }}" class="tw:block tw:w-full tw:text-inherit! tw:no-underline!">
@endif
<div @if($graphType) class="tw:flex tw:w-full tw:min-w-0 tw:flex-col tw:gap-1" @else {{ $attributes->class(['tw:flex tw:w-full tw:min-w-0 tw:flex-col tw:gap-1']) }} @endif>
    <div
        class="tw:h-3 tw:w-full tw:overflow-hidden tw:rounded tw:inset-shadow-sm/10"
        style="background-color: {{ $colors['right'] }}"
        role="progressbar"
        aria-valuenow="{{ $value }}"
        aria-valuemin="0"
        aria-valuemax="100"
    >
        <div class="tw:flex tw:h-full">
            <div class="tw:h-full tw:inset-shadow-sm/10" style="width: {{ $value }}%; background-color: {{ $colors['left'] }}"></div>
            @if ($shadowWidth !== null)
                <div class="tw:h-full" style="width: {{ $shadowWidth }}%; background-color: {{ $colors['middle'] }}"></div>
            @endif
        </div>
    </div>
    <div class="tw:flex tw:min-w-0 tw:justify-between tw:gap-2 tw:whitespace-nowrap">
        <span class="tw:min-w-0 tw:truncate">{{ $leftText ?? round($value) . '%' }}</span>
        <span class="tw:min-w-0 tw:truncate tw:text-right">{{ $rightText }}</span>
    </div>
</div>
@if($graphType)
    </a>
    @if($graphTitle)
        <x-slot name="title">{{ $graphTitle }}</x-slot>
    @endif
    <x-slot name="body">
        <x-graph-row loading="lazy" :type="$graphType" :vars="$graphVars"></x-graph-row>
    </x-slot>
</x-popup>
@endif
