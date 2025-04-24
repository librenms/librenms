@aware(['popupTitle'])
<x-popup>
    <x-graph></x-graph>
    @if($popupTitle)
    <x-slot name="title">{{ $popupTitle }}</x-slot>
    @endif
    <x-slot name="body">
        <x-graph-row loading="lazy"></x-graph-row>
    </x-slot>
</x-popup>
