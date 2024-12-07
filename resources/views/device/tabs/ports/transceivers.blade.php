@foreach($data['transceivers'] as $transceiver)
    <x-panel>
        <x-slot name="heading">
            <x-transceiver :transceiver="$transceiver"></x-transceiver>
        </x-slot>
        <x-transceiver-sensors :transceiver="$transceiver"></x-transceiver-sensors>
    </x-panel>
@endforeach
