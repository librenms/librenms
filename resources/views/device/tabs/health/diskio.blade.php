@foreach($items as $disk)
    @foreach(['diskio_bits' => 'bps', 'diskio_ops' => 'Ops/sec'] as $type => $title)
        <x-panel>
            <x-slot:heading>
                <h3 class="panel-title">{{ $disk->diskio_descr }} - {{ $title }}</h3>
            </x-slot:heading>
            <x-graph-row :type="$type" :device="$device" :vars="['id' => $disk->diskio_id]" columns="responsive" />
        </x-panel>
    @endforeach
@endforeach
