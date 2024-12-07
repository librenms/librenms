<div class="panel panel-default">
    <div class="panel-body">
        <div class='col-md-6'>
            <ul class='mktree' id='ingress'>
            <div><strong><i class="fa fa-sign-in"></i>&nbsp;Ingress Policy:</strong></div>
@php $qosIngress = $qosItems->where('ingress', '1') @endphp
@if($qosIngress->count() == 0)
            <div><i>No Policies</i></div>
@else
            <x-qos-tree :qosItems="$qosIngress" :parentPortId="$portId" :show="$show" />
@endif
            </ul>
</div>

<div class='col-md-6'>
            <ul class='mktree' id='egress'>
            <div><strong><i class="fa fa-sign-out"></i>&nbsp;Egress Policy:</strong></div>
@php $qosEgress = $qosItems->where('egress', '1') @endphp
@if($qosEgress->count() == 0)
            <div><i>No Policies</i></div>
@else
            <x-qos-tree :qosItems="$qosEgress" :parentPortId="$portId" :show="$show" />
@endif
            </ul>
        </div>
    </div>
</div>

@if($qosGraph)
@php
$graphType = $qosGraph->type;
if ($qosGraph->type == 'routeros_tree') {
    $graphs = ['_traffic' => __('Traffic'), '_drop' => __('Dropped Packets')];
} elseif ($qosGraph->type == 'routeros_simple') {
    $graphs = ['_traffic' => __('Traffic'), '_drop' => __('Dropped Packets')];
} elseif ($qosGraph->type == 'cisco_cbqos_policymap' || $qosGraph->type == 'cisco_cbqos_classmap') {
    $graphs = ['_prebits' => __('Traffic (pre policy)'), '_postbits' => __('Traffic (post policy)'), '_qosdrops' => __('Traffic Drops'), '_prepkts' => __('Packets (pre policy)'), '_droppkts' => __('Packet Drops'), '_bufferdrops' => __('No Buffer Drops', )];
    $graphType = 'cisco_cbqos';
} else {
    $graphs = [];
}
@endphp
<div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">{{ $qosGraph->title }}</h3>
    </div>
    <div class="panel-body">
@if(count($graphs) == 0)
    Graphs for type {{ $qosGraph->type }} need to be defined in resources/views/components/qos.blade.php
@endif
@foreach($graphs as $graphSuffix => $graphTitle)
    <x-graph-row loading="lazy" :device="$portId ? null : $qosGraph->device_id" :port="$portId" :type="$typePrefix . $graphType . $graphSuffix" :title="__($graphTitle)" :columns=4 :graphs="[['from' => '-1d'], ['from' => '-1week'], ['from' => '-1month'], ['from' => '-1y']]" :vars="['qos_id' => $qosGraph->qos_id]"></x-graph-row>
@endforeach
    </div>
</div>
@endif
