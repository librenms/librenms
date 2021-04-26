@extends('poller.index')

@section('title', __('Poller Performance'))

@section('content')

@parent

<x-panel title="{{ __('Total Poller Time') }}">
    <?php \LibreNMS\Util\Html::graphRow(['type' => 'global_poller_perf',
                                       'legend' => 'yes', 'height' => 100], true); ?>
</x-panel>

<x-panel title="{{ __('Total Poller Time Per Module') }}">
    <?php \LibreNMS\Util\Html::graphRow(['type' => 'global_poller_modules_perf',
                                        'legend' => 'yes', 'height' => 100], true); ?>
</x-panel>

@endsection
