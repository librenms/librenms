@foreach($data['ports'] as $port)
    <x-panel>
        <x-slot name="title">
            <div>
            {{-- div to allow color to override boostrap title link color --}}
            <x-port-link basic :port="$port">
                <span class="tw:text-3xl tw:font-bold">
                    <i class="fa fa-tag" aria-hidden='true'></i>
                    {{ $port->getLabel() }}
                    @if($port->getLabel() !== $port->getDescription())
                        <span class="tw:text-xl tw:font-normal">{{ $port->getDescription() }}</span>
                    @endif
                </span>
            </x-port-link>
            </div>
        </x-slot>
        <x-graph-row loading="lazy" columns="responsive" :port="$port" :type="$data['graph_type']" :graphs="[['from' => '-1d'], ['from' => '-1week'], ['from' => '-1month'], ['from' => '-1y']]" legend="no"></x-graph-row>
    </x-panel>
@endforeach
