<span x-data="{
 popoverShow: false,
 showTimeout: null,
 hideTimeout: null,
 show(timeout) {
    clearTimeout(this.hideTimeout);
    this.showTimeout = setTimeout(() => { this.popoverShow = true; Popper.createPopper(this.$refs.btnRef, this.$refs.popoverRef, {})}, timeout);
 },
 hide(timeout) {
     clearTimeout(this.showTimeout);
     this.hideTimeout = setTimeout(() => this.popoverShow = false, timeout)
 }
 }">
<a href="{{ route('device', ['device' => $device->device_id, 'tab' => $tab, 'section' => $section]) }}"
   x-ref="btnRef"
   x-on:mouseenter='show(100)'
   x-on:mouseleave="hide(1000)"
   class=""
>
    {{ $slot->isNotEmpty() ? $slot : $device->displayName() }}
</a>
<span x-ref="popoverRef"
      x-on:mouseenter="clearTimeout(hideTimeout)"
      x-on:mouseleave="hide(500)"
      x-bind:class="{'hidden': !popoverShow, 'block': popoverShow}"
      class="hidden border-2 ml-3 z-50 font-normal leading-normal text-sm max-w-xs text-left no-underline break-words rounded-lg"
>
    <div>
        <div class="bg-white opacity-90 text-lg font-semibold p-3 mb-0 border-b border-solid border-blueGray-100 rounded-t-lg">
            {{ $device->displayName() }}
        </div>
        <div class="p-3">
            <span class="text-lg font-bold">{{ $device->displayName() }}</span>
        </div>
    </div>
</span>
</span>
