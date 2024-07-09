@foreach($data['transceivers'] as $transceiver)
    <x-panel>
        <x-slot name="heading">
            <x-transceiver :transceiver="$transceiver"></x-transceiver>
        </x-slot>
        <x-transceiver-metrics :transceiver="$transceiver"></x-transceiver-metrics>
    </x-panel>
@endforeach
