@props([
    'name' => null,
    'model' => null,
    'label' => null,
    'size' => 'md',
    'bordered' => false,
])

@php
    [$trackClass, $knobClass, $translateClass] = match ($size) {
        'sm' => ['tw:w-9 tw:h-5', 'tw:w-4 tw:h-4', 'tw:peer-checked:translate-x-4'],
        default => ['tw:w-12 tw:h-7', 'tw:w-6 tw:h-6', 'tw:peer-checked:translate-x-5'],
    };
@endphp

<label @class([
    'tw:inline-flex tw:items-center tw:gap-3 tw:cursor-pointer tw:mb-0 tw:font-normal',
    'tw:w-full tw:px-4 tw:py-3 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:bg-white tw:dark:bg-dark-gray-500' => $bordered,
])>
    <span class="tw:relative tw:inline-block tw:shrink-0">
        @if ($name)
            <input type="hidden" name="{{ $name }}" value="0">
        @endif
        <input type="checkbox"
               @if ($name) name="{{ $name }}" value="1" @endif
               @if ($model) x-model="{{ $model }}" @endif
               {{ $attributes->class(['tw:sr-only tw:peer']) }}>
        <span class="tw:block {{ $trackClass }} tw:rounded-full tw:bg-gray-300 tw:dark:bg-dark-gray-100 tw:transition-colors tw:duration-200 tw:peer-checked:bg-blue-600 tw:peer-disabled:opacity-50 tw:peer-disabled:cursor-not-allowed tw:peer-focus-visible:ring-2 tw:peer-focus-visible:ring-blue-400 tw:peer-focus-visible:ring-offset-2 tw:dark:peer-focus-visible:ring-offset-dark-gray-500"></span>
        <span class="tw:absolute tw:left-0.5 tw:top-0.5 {{ $knobClass }} tw:rounded-full tw:bg-white tw:shadow-sm tw:transition-transform tw:duration-200 {{ $translateClass }}"></span>
    </span>
    @if ($label || $slot->isNotEmpty())
        <span class="tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200">{{ $label ?? $slot }}</span>
    @endif
</label>
