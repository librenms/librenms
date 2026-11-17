<x-panel :title="__('Plugins')">
    @foreach($plugins as $plugin)
        {!! $plugin !!}
    @endforeach
</x-panel>
