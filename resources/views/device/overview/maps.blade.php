<div class="row">
    <div class="col-md-12">
        <x-panel class="device-overview panel-condensed overview-panel">
            <x-slot name="heading">
                <i class="fa fa-map-marked fa-lg icon-theme" aria-hidden="true"></i>
                <strong>{{ __('Custom Maps') }}</strong>
            </x-slot>
            @foreach($maps as $map)
                <p><a href="{{ route('maps.custom.show', $map->custom_map_id) }}">{{ $map->name }}</a></p>
            @endforeach
        </x-panel>
    </div>
</div>
