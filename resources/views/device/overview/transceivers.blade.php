<div class="row">
    <div class="col-md-12">
        <x-panel class="device-overview panel-condensed">
            <x-slot name="heading" class="tw-mb-6">
                <x-icons.transceiver></x-icons.transceiver>
                <strong><a href="{{ $transceivers_link }}">{{ __('port.tabs.transceivers') }}</a></strong>
            </x-slot>

            @foreach($transceivers as $transceiver)
                <x-panel body-class="!tw-p-0">
                    <x-slot name="heading">
                        @if($transceiver->port)
                        <x-port-link :port="$transceiver->port"></x-port-link>
                        @endif
                        <x-icons.transceiver></x-icons.transceiver> {{ $transceiver->vendor }} {{ $transceiver->type }}
                    </x-slot>
                    <table class="table table-hover table-condensed table-striped !tw-mb-0">
                        @foreach($filterMetrics($transceiver->metrics) as $metric)
                            <tr>
                                <td>{{ trans_choice('port.transceivers.metrics.' . $metric->type, $transceiver->channels, ['channel' => $metric->channel]) }}</td>
                                <td><x-graph loading="lazy" :port="$transceiver->port" type="port_transceiver_{{ $metric->type }}" width="100" height="24" :vars="['channel' => $metric->channel]"></x-graph></td>
                                <td><x-label :status="$metric->status->asSeverity()">{{ $metric->value }} {{ __('port.transceivers.units.' . $metric->type) }}</x-label></td>
                            </tr>
                        @endforeach
                    </table>
                </x-panel>
            @endforeach
        </x-panel>
    </div>
</div>
