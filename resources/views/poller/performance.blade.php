@extends('poller.index')

@section('title', __('Poller Performance'))

@section('content')

@parent

<br />
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Total Poller Time</h3>
    </div>
    <div class="panel-body">
        <?php \LibreNMS\Util\Html::graphRow(['type' => 'global_poller_perf',
                                           'legend' => 'yes', 'height' => 100], true); ?>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Total Poller Time Per Module</h3>
    </div>
    <div class="panel-body">
        <?php \LibreNMS\Util\Html::graphRow(['type' => 'global_poller_modules_perf',
                                           'legend' => 'yes', 'height' => 100], true); ?>
    </div>
</div>

@endsection
