{{-- monitored ports that own the MAC, $seenOn is the port the entry was learned on --}}
@foreach(($owners ?? collect())->take(2) as $owner)
    <div>
        @if(isset($seenOn) && $owner->port_id == $seenOn)
            <x-search.badge color="blue">{{ __('search.badge.local') }}</x-search.badge>
        @else
            @include('search.partials.port', ['port' => $owner])
        @endif
    </div>
@endforeach
@if(($owners ?? collect())->count() > 2)
    <div class="tw:text-xs tw:text-gray-500">{{ __('search.locate.more', ['count' => $owners->count() - 2]) }}</div>
@endif
