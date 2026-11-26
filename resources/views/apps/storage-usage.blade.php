@if($storage)
    <a href="{{ route('graphs', ['type' => 'storage_usage', 'id' => $storage->storage_id]) }}">{{ $path }}</a>
    ({{ round($storage->storage_perc) }}%)
    <x-graph type="storage_usage" :vars="['id' => $storage->storage_id, 'bg' => 'ffffff00']"
        width="80" height="20" loading="lazy" popup :popup-title="$storage->storage_descr" />
@else
    {{ $path }}
@endif
