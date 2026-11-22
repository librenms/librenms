{{-- what didn't fit of the host's subnet, $limit were shown: the interfaces on its network are listed with a filter --}}
@if($subnet->hiddenOnNetwork($limit) > 0)
    @include('search.partials.more', ['hidden' => $subnet->hiddenOnNetwork($limit), 'url' => route('search', ['q' => $subnet->network, 'filter' => ['source' => ['eq' => 'interface']]])])
@else
    @include('search.partials.more', ['hidden' => $subnet->count() - $limit])
@endif
