{{-- copy a value to the clipboard, $js is a javascript expression evaluated when clicked --}}
<button type="button" @click="copy($event, {{ $js }})" title="{{ __('search.copy') }}" aria-label="{{ __('search.copy') }}"
        class="tw:px-1 tw:text-gray-400 tw:hover:text-gray-700 tw:dark:hover:text-dark-white-100">
    <i class="fa fa-copy fa-fw" aria-hidden="true"></i>
</button>
