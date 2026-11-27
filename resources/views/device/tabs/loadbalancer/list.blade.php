@if($rowGraphOptions)
    <x-option-bar :name="__('Graphs')" :options="$rowGraphOptions" :selected="$rowGraph ?? ''" />
@endif
<x-panel>
    <x-slot:table>
        @include('device.tabs.loadbalancer.table')
    </x-slot:table>
</x-panel>
