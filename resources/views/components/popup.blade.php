<div {{ $attributes->merge(['class' => 'inline-block']) }} x-data="{
 popupShow: false,
 showTimeout: null,
 hideTimeout: null,
 delay: 300,
 show(timeout) {
    clearTimeout(this.hideTimeout);
    this.showTimeout = setTimeout(() => {
        this.popupShow = true;
        Popper.createPopper(this.$refs.targetRef, this.$refs.popupRef, {
            padding: 8
        })
     }, timeout);
 },
 hide(timeout) {
     clearTimeout(this.showTimeout);
     this.hideTimeout = setTimeout(() => this.popupShow = false, timeout)
 }
}"
 x-on:click.away="hide(0)"
>
    <div class="inline-block" x-ref="targetRef" x-on:mouseenter='show(100)' x-on:mouseleave="hide(delay)">
        {{ $slot }}
    </div>
    <div x-ref="popupRef"
          x-on:mouseenter="clearTimeout(hideTimeout)"
{{--          x-on:mouseleave="hide(delay)"--}}
          x-bind:class="{'hidden': !popupShow, 'block': popupShow}"
          class="hidden bg-white border-2 ml-3 z-50 font-normal leading-normal text-sm text-left no-underline rounded-lg"
    >
        @isset($title)
            <div class="opacity-90 p-3 mb-0 border-b border-solid rounded-t-lg">
                {{ $title }}
            </div>
        @endisset
        @isset($body)
        <div class="p-3">
            {{ $body }}
        </div>
        @endisset
    </div>
</div>
