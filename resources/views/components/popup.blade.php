<div {{ $attributes->merge(['class' => 'tw:inline-block']) }}
 x-data="popup('', { showDelay: {{ $attributes->get('show-delay', 100) }}, hideDelay: {{ $attributes->get('hide-delay', 300) }} })"
 x-on:click.away="hide(0)"
 x-on:librenms-popup-shown.window="() => hide(0)"
>
    <span x-ref="targetRef" x-on:mouseenter='show(showDelay)' x-on:mouseleave="hide(hideDelay)">
        {{ $slot }}
    </span>
    <div x-cloak
          x-ref="popupRef"
          x-on:mouseenter="clearTimeout(hideTimeout)"
          x-on:mouseleave="hide(hideDelay)"
          x-bind:class="{'tw:hidden': !popupShow, 'tw:block': popupShow}"
          class="tw:hidden tw:bg-white! tw:dark:bg-dark-gray-300! tw:dark:text-white tw:border-2 tw:border-gray-200 tw:dark:border-dark-gray-200 tw:ml-3 tw:z-50 tw:font-normal tw:leading-normal tw:text-sm tw:text-left tw:no-underline tw:rounded-lg"
          style="max-width:95vw;"
    >
        @isset($title)
            <div class="tw:opacity-90 tw:p-3 tw:mb-0 tw:border-b-2 tw:border-solid tw:border-gray-200 tw:dark:border-dark-gray-200 tw:rounded-t-lg">
                {{ $title }}
            </div>
        @endisset
        @isset($body)
        <div {{ $body->attributes->class(['tw:p-3' => $body->attributes->isEmpty()]) }}>
            {{ $body }}
        </div>
        @endisset
    </div>
</div>
