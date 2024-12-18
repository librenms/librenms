@if($listItems->count())
    @php
    $this_page_params = Route::current()->parameters();
    if (array_key_exists('vars', $this_page_params)) {
        $this_page_params['vars'] = implode('/', array_filter(explode('/', $this_page_params['vars']), function($v) { return ! str_starts_with($v, 'show='); }));
    }
    @endphp

    @if($parentQosId)
<ul>
    @endif
    @foreach($listItems as $qosGraph)
    <li class='liOpen'>
        @if($qosGraph->qos_id == $show)
        <span title="{{ $qosGraph->tooltip }}">{{ $qosGraph->title }}</span>
        @else
        <a href="{{ route('device', $this_page_params) }}/show={{$qosGraph->qos_id}}" title="{{ $qosGraph->tooltip }}">{{ $qosGraph->title }}</a>
        @endif
        <x-qos-tree :qosItems="$qosItems" :parentQosId="$qosGraph->qos_id" :show="$show" />
    </li>
    @endforeach
    @if($parentQosId)
</ul>
    @endif
@endif
