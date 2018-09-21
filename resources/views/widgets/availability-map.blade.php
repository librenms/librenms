<div class="widget-availability-host">
    <span>Total hosts</span>
    <span class="label label-success label-font-border label-border">up: {{ $totals['up'] }}</span>
    <span class="label label-warning label-font-border label-border">warn: {{ $totals['warn'] }}</span>
    <span class="label label-danger label-font-border label-border">down: {{ $totals['down'] }}</span>

</div>
<br style="clear:both;">

@foreach($devices as $device)
    <a href="device/device={{ $device->device_id }}" title="{{ $device->displayName() }} - {{ $device->formatUptime(true) }}">
        <span class="label label-{{ $device->statusColour() }} widget-availability-fixed widget-availability label-font-border"> </span>
    </a>
@endforeach
