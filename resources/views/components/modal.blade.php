@props(['id', 'maxWidth'])

@php
    $id = $id ?? md5('FIXME');
    $maxWidth = [
        'sm' => 'sm:tw-max-w-sm',
        'md' => 'sm:tw-max-w-md',
        'lg' => 'sm:tw-max-w-lg',
        'xl' => 'sm:tw-max-w-xl',
        '2xl' => 'sm:tw-max-w-2xl',
    ][$maxWidth ?? '2xl'];
@endphp

<div
        x-modelable="show"
        x-data="{
        show: false,
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                // All non-disabled elements...
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
    }"
        x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('tw-overflow-y-hidden');
            {{ $attributes->has('focusable') ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
                } else {
                    document.body.classList.remove('tw-overflow-y-hidden');
                }
            })"
        x-on:close.stop="show = false"
        x-on:keydown.escape.window="show = false"
        x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
        x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
        x-show="show"
        id="{{ $id }}"
        class="tw-fixed tw-inset-0 tw-overflow-y-auto tw-px-4 tw-py-6 sm:tw-px-0 tw-z-50"
        style="display: none;"
        {{ $attributes }}
>
    <div x-show="show" class="tw-fixed tw-inset-0 tw-transform tw-transition-all" x-on:click="show = false" x-transition:enter="tw-ease-out tw-duration-300"
         x-transition:enter-start="tw-opacity-0"
         x-transition:enter-end="tw-opacity-100"
         x-transition:leave="tw-ease-in tw-duration-200"
         x-transition:leave-start="tw-opacity-100"
         x-transition:leave-end="tw-opacity-0">
        <div class="tw-absolute tw-inset-0 tw-bg-gray-500 tw-opacity-75"></div>
    </div>

    <div x-show="show" class="tw-mt-16 tw-mb-6 tw-bg-white tw-rounded-lg tw-overflow-hidden tw-shadow-xl tw-transform tw-transition-all sm:tw-w-full {{ $maxWidth }} sm:tw-mx-auto"
         x-transition:enter="tw-ease-out tw-duration-300"
         x-transition:enter-start="tw-opacity-0 tw-translate-y-4 sm:tw-translate-y-0 sm:tw-scale-95"
         x-transition:enter-end="tw-opacity-100 tw-translate-y-0 sm:tw-scale-100"
         x-transition:leave="tw-ease-in tw-duration-200"
         x-transition:leave-start="tw-opacity-100 tw-translate-y-0 sm:tw-scale-100"
         x-transition:leave-end="tw-opacity-0 tw-translate-y-4 sm:tw-translate-y-0 sm:tw-scale-95">
        {{ $slot }}
    </div>
</div>
