<div
    x-data="{overflowed: false, expand() { this.overflowed=false; $refs.container.style.height = 'auto' }}"
    x-init="$nextTick(() => {overflowed = $refs.container.offsetHeight < $refs.container.scrollHeight})">
    <div x-ref="container" class="tw:overflow-y-hidden"
         style="height:{{ $attributes->get('height') }}">{{ $slot }}</div>
    <div
        x-cloak
        x-on:click="expand()" x-show="overflowed"
        x-transition:enter="tw:transition tw:ease-out tw:duration-700"
        x-transition:enter-start="tw:opacity-0"
        x-transition:enter-end="tw:opacity-100"
        class="tw:cursor-pointer tw:leading-6"
        title="{{ __('More') }}">...
    </div>
</div>
