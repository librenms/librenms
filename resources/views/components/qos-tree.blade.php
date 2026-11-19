@if($listItems->count())
    @if($parentQosId)
<ul>
    @endif
    @foreach($listItems as $qosGraph)
    <li class='liOpen'>
        @if($qosGraph->qos_id == $show)
        <span title="{{ $qosGraph->tooltip }}">{{ $qosGraph->title }}</span>
        @else
        <a href="{{ $link($qosGraph->qos_id) }}" title="{{ $qosGraph->tooltip }}">{{ $qosGraph->title }}</a>
        @endif
        <x-qos-tree :qosItems="$qosItems" :deviceId="$deviceId" :portId="$portId" :parentQosId="$qosGraph->qos_id" :show="$show" />
    </li>
    @endforeach
    @if($parentQosId)
</ul>
    @endif
@endif
