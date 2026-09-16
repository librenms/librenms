@aware(['popupTitle'])
@php($fullWidth = \Illuminate\Support\Str::contains($attributes->get('class', ''), 'tw:w-full'))
<x-popup :class="$fullWidth ? 'tw:block tw:w-full' : null" :target-class="$fullWidth ? 'tw:block tw:w-full' : null">
    @include('components.linked-graph')
    @if($popupTitle)
    <x-slot name="title">{{ $popupTitle }}</x-slot>
    @endif
    <x-slot name="body">
        <x-graph-row loading="lazy" :type="$type" :vars="$vars" :legend="$legend"></x-graph-row>
    </x-slot>
</x-popup>
