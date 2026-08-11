@props(['maps'])

@if($maps->isNotEmpty())
    <x-device.overview.panel :title="__('Custom Maps')" icon="fa fa-map-marked">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            @foreach($maps as $map)
                <div class="tw:px-2 tw:py-1"><a href="{{ route('maps.custom.show', $map->custom_map_id) }}">{{ $map->name }}</a></div>
            @endforeach
        </div>
    </x-device.overview.panel>
@endif
