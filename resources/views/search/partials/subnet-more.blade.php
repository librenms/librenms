{{-- what didn't fit of the host's subnet, $limit were shown: the interfaces on it are listed with a filter --}}
@php($more = __('search.host.more', ['count' => $subnet->count() - $limit]))
@if($subnet->network)
    <a href="{{ route('search', ['q' => $subnet->network, 'filter' => ['source' => ['eq' => 'interface']]]) }}" class="tw:text-sm">{{ $more }}</a>
@else
    <span class="tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400">{{ $more }}</span>
@endif
