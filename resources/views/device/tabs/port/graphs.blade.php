@foreach($graphs as $type => $title)
    <x-panel :title="__($title)">
        <x-graph-row :type="$type" :port="$port" columns="responsive" />
    </x-panel>
@endforeach
@if(empty($graphs))
    <x-panel><em>{{ __('No graphs found') }}</em></x-panel>
@endif
