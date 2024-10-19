@foreach($qosGraphs as $qosGraph)
@php($graphId = $qosGraph->prefs->where('attribute', $idPrefName)->pluck('value')->first())
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">{{ $qosGraph->prefs->where('attribute', $titlePrefName)->pluck('value')->first() }}</h3>
    </div>
        <x-graph-row loading="lazy" :device="$device" :type="$graphType . '_sent'" :title="__('Transmitted')" :columns=4 :graphs="[['from' => '-1d'], ['from' => '-1week'], ['from' => '-1month'], ['from' => '-1y']]" :vars="['rrd_id' => $graphId]"></x-graph-row>
       <x-graph-row loading="lazy" :device="$device" :type="$graphType . '_drop'" :title="__('Dropped')" :columns=4 :graphs="[['from' => '-1d'], ['from' => '-1week'], ['from' => '-1month'], ['from' => '-1y']]" :vars="['rrd_id' => $graphId]"></x-graph-row>
    </div>
  </div>
@endforeach
