<span x-data="{
 popupShow: false,
 showTimeout: null,
 hideTimeout: null,
 show(timeout) {
    clearTimeout(this.hideTimeout);
    this.showTimeout = setTimeout(() => {
        this.popupShow = true;
        Popper.createPopper(this.$refs.targetRef, this.$refs.popupRef, {})
     }, timeout);
 },
 hide(timeout) {
     clearTimeout(this.showTimeout);
     this.hideTimeout = setTimeout(() => this.popupShow = false, timeout)
 }
}">
    <span x-ref="targetRef" x-on:mouseenter='show(100)' x-on:mouseleave="hide(1000)">
        {{ $slot }}
    </span>
    <span x-ref="popupRef"
          x-on:mouseenter="clearTimeout(hideTimeout)"
          x-on:mouseleave="hide(500)"
          x-bind:class="{'hidden': !popupShow, 'block': popupShow}"
          class="hidden border-2 ml-3 z-50 font-normal leading-normal text-sm max-w-xs text-left no-underline break-words rounded-lg"
    >
        <div>
            <div class="bg-white opacity-90 text-lg font-semibold p-3 mb-0 border-b border-solid border-blueGray-100 rounded-t-lg">
                {{ $title }}
            </div>
            <div class="p-3">
                {{ $body }}
            </div>
        </div>
    </span>
</span>
