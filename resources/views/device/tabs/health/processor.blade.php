@foreach($items as $processor)
    <x-panel>
        <x-slot:heading>
            <h3 class="panel-title">
                {{ $processor->getFormattedDescription() }}
                <span class="pull-right">{{ $processor->processor_usage === null ? '?' : round($processor->processor_usage) . '%' }} {{ __('used') }}</span>
            </h3>
        </x-slot:heading>
        <x-graph-row type="processor_usage" :device="$device" :vars="['id' => $processor->processor_id]" columns="responsive" />
    </x-panel>
@endforeach
