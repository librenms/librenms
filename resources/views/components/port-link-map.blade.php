<div>
    <div class="list-large">
        {{ $port->device?->displayName() }} - {{ $port->getLabel() }}
    </div>
    {{ $port->getDescription() }}
    <br>
    @foreach($graphs as $graph)
        <x-graph-row loading="lazy" :port="$port" :type="$graph['type']" :title="$graph['title']" :graphs="[['from' => '-1d']]" />
    @endforeach
</div>
