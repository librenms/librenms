<div {{ $attributes->merge(['class' => 'tw-inline-block']) }} x-data="{
 popupShow: false,
 showTimeout: null,
 hideTimeout: null,
 ignoreShownEvent: false,
 delay: 300,
 show(timeout) {
    clearTimeout(this.hideTimeout);
    this.showTimeout = setTimeout(() => {
        this.popupShow = true;
        Popper.createPopper(this.$refs.targetRef, this.$refs.popupRef, {
            padding: 8
        });
        this.ignoreShownEvent = true;
        this.$dispatch('librenms-popup-shown', this.$el);
     }, timeout);
 },
 hide(timeout) {
    if (this.ignoreShownEvent) {
        this.ignoreShownEvent = false;
        return;
    }

     clearTimeout(this.showTimeout);
     this.hideTimeout = setTimeout(() => this.popupShow = false, timeout)
 }
}"
 x-on:click.away="hide(0)"
 x-on:librenms-popup-shown.window="() => hide(0)"
>
    <div class="tw-inline-block" x-ref="targetRef" x-on:mouseenter='show(100)' x-on:mouseleave="hide(delay)">
        {{ $slot }}
    </div>
    <div x-ref="popupRef"
          x-on:mouseenter="clearTimeout(hideTimeout)"
{{--          x-on:mouseleave="hide(delay)"--}}
          x-bind:class="{'tw-hidden': !popupShow, 'tw-block': popupShow}"
          class="tw-hidden tw-absolute tw-left-0 tw-bg-white dark:tw-bg-gray-800 dark:tw-text-white tw-border-2 tw-border-gray-200 dark:tw-border-gray-600 tw-ml-3 tw-z-50 tw-font-normal tw-leading-normal tw-text-sm tw-text-left tw-no-underline tw-rounded-lg"
          style="max-width:95vw;"
    >
        @isset($title)
            <div class="tw-opacity-90 tw-p-3 tw-mb-0 tw-border-b-2 tw-border-solid tw-border-gray-200 dark:tw-border-gray-600 tw-rounded-t-lg">
                {{ $title }}
            </div>
        @endisset
        @isset($body)
        <div class="tw-p-3">
            {{ $body }}
        </div>
        @endisset
    </div>
</div>
