{{-- what didn't fit of the host's subnet, $limit were shown: the interfaces on its network are listed with a filter --}}
@php($listed = $subnet->hiddenOnNetwork($limit))
@if($listed > 0)
    <a href="{{ route('search', ['q' => $subnet->network, 'filter' => ['source' => ['eq' => 'interface']]]) }}" class="tw:text-sm">{{ __('search.host.more', ['count' => $listed]) }}</a>
@else
    <span class="tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('search.host.more', ['count' => $subnet->count() - $limit]) }}</span>
@endif
