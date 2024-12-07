<div {{ $attributes->merge(['class' => 'tw-inline-block']) }}
 x-data="popup"
 x-on:click.away="hide(0)"
 x-on:librenms-popup-shown.window="() => hide(0)"
>
    <span x-ref="targetRef" x-on:mouseenter='show(100)' x-on:mouseleave="hide(delay)">
        {{ $slot }}
    </span>
    <div x-cloak
          x-ref="popupRef"
          x-on:mouseenter="clearTimeout(hideTimeout)"
          x-on:mouseleave="hide(delay)"
          x-bind:class="{'tw-hidden': !popupShow, 'tw-block': popupShow}"
          class="tw-hidden tw-bg-white dark:tw-bg-dark-gray-300 dark:tw-text-white tw-border-2 tw-border-gray-200 dark:tw-border-dark-gray-200 tw-ml-3 tw-z-50 tw-font-normal tw-leading-normal tw-text-sm tw-text-left tw-no-underline tw-rounded-lg"
          style="max-width:95vw;"
    >
        @isset($title)
            <div class="tw-opacity-90 tw-p-3 tw-mb-0 tw-border-b-2 tw-border-solid tw-border-gray-200 dark:tw-border-dark-gray-200 tw-rounded-t-lg">
                {{ $title }}
            </div>
        @endisset
        @isset($body)
        <div {{ $body->attributes->class(['tw-p-3' => $body->attributes->isEmpty()]) }}>
            {{ $body }}
        </div>
        @endisset
    </div>
</div>
