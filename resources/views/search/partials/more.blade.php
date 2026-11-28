{{-- the +N more after a shortened list, a link when $url lists them all --}}
@if($hidden > 0)
    @if($url ?? null)
        <a href="{{ $url }}" class="tw:text-sm">{{ __('search.host.more', ['count' => $hidden]) }}</a>
    @else
        <span class="tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('search.host.more', ['count' => $hidden]) }}</span>
    @endif
@endif
