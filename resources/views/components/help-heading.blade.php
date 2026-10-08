@props([
    'id',
    'label',
])

{{-- a heading with a help button that shows or hides the slot text, the heading id is "{id}-heading" for aria-labelledby --}}
<div {{ $attributes }} x-data="{ help: false }">
    <div class="tw:flex tw:items-center tw:gap-2">
        <span id="{{ $id }}-heading" class="tw:font-semibold tw:text-gray-900 tw:dark:text-dark-white-100">{{ $label }}</span>
        <button type="button"
                class="tw:p-0 tw:border-0 tw:bg-transparent tw:text-gray-400 tw:hover:text-blue-600 tw:dark:hover:text-blue-400"
                :class="help && 'tw:text-blue-600! tw:dark:text-blue-400!'"
                x-on:click="help = ! help"
                :aria-expanded="help"
                aria-controls="{{ $id }}-help"
                aria-label="{{ __('Help') }}">
            <i class="fa fa-circle-question" aria-hidden="true"></i>
        </button>
    </div>
    <p id="{{ $id }}-help" x-show="help" x-cloak class="tw:m-0 tw:mt-1 tw:text-gray-500 tw:dark:text-dark-white-400 tw:text-pretty">{{ $slot }}</p>
</div>
