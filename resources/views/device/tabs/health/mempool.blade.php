@php
    $usage = fn ($mempool) => ($mempool->mempool_total != 100
        ? \LibreNMS\Util\Number::formatBi($mempool->mempool_used) . '/' . \LibreNMS\Util\Number::formatBi($mempool->mempool_total) . ' - '
        : '') . $mempool->mempool_perc . '% ' . __('used');
    $system = $items->firstWhere('mempool_class', 'system');
@endphp

@if($items->count() > 1)
    <x-panel>
        <x-slot:heading>
            <h3 class="panel-title">{{ __('Overview') }} @if($system)<span class="pull-right">{{ $usage($system) }}</span>@endif</h3>
        </x-slot:heading>
        <x-graph-row type="device_mempool" :device="$device" columns="responsive" />
    </x-panel>
@endif

@foreach($items as $mempool)
    <x-panel>
        <x-slot:heading>
            <h3 class="panel-title">{{ $mempool->mempool_descr }} <span class="pull-right">{{ $usage($mempool) }}</span></h3>
        </x-slot:heading>
        <x-graph-row type="mempool_usage" :device="$device" :vars="['id' => $mempool->mempool_id]" columns="responsive" />
    </x-panel>
@endforeach
