@foreach($data['ports'] as $port)
    <div class="minigraph-div">
        <x-port-link :port="$port">
            <div class="tw-font-bold">{{ $port->getShortLabel() }}</div>
            <x-graph :port="$port" :type="$data['graph_type']" :from="$request->input('from', '-1d')" width="180" height="55" legend="no"></x-graph>
        </x-port-link>
    </div>
@endforeach
