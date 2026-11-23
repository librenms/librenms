@foreach($items as $storage)
    <x-panel>
        <x-slot:heading>
            <h3 class="panel-title">
                {{ $storage->storage_descr }}
                <span class="pull-right">
                    {{ \LibreNMS\Util\Number::formatBi($storage->storage_used) }}/{{ \LibreNMS\Util\Number::formatBi($storage->storage_size) }}
                    - {{ round($storage->storage_perc) }}% {{ __('used') }}
                </span>
            </h3>
        </x-slot:heading>
        <x-graph-row type="storage_usage" :device="$device" :vars="['id' => $storage->storage_id]" columns="responsive" />
    </x-panel>
@endforeach
