@props([
    'key',
    'label',
])

<button type="button" x-on:click="sortBy(@js($key))" class="tw:p-0 tw:bg-transparent tw:border-0 tw:uppercase tw:font-semibold">
    {{ $label }} <i class="fa" :class="sortIcon(@js($key))" aria-hidden="true"></i>
</button>
