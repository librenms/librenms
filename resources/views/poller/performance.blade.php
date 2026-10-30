@extends('poller.index')

@section('title', __('Poller Performance'))

@section('content')

@parent

<x-panel title="{{ __('Total Poller Time') }}">
    <x-graph-row type="global_poller_perf" columns="responsive" legend="yes"></x-graph-row>
</x-panel>

<x-panel title="{{ __('Total Poller Time Per Module') }}">
    <x-graph-row type="global_poller_modules_perf" columns="responsive" legend="yes"></x-graph-row>
</x-panel>

@if(\App\Queue\QueueStatus::showPages())
<x-panel title="{{ __('Queue Job Throughput') }}">
    <x-graph-row type="global_queue_throughput" columns="responsive" legend="yes"></x-graph-row>
</x-panel>

<x-panel title="{{ __('Queue Job Runtime') }}">
    <x-graph-row type="global_queue_runtime" columns="responsive" legend="yes"></x-graph-row>
</x-panel>
@endif

@endsection
