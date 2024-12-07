@aware(['popupTitle'])
<x-popup>
    @includeWhen($link, 'components.linked-graph')
    @includeUnless($link, 'components.graph')
    @if($popupTitle)
    <x-slot name="title">{{ $popupTitle }}</x-slot>
    @endif
    <x-slot name="body">
        <x-graph-row loading="lazy" :$type :$vars :$legend></x-graph-row>
    </x-slot>
</x-popup>
