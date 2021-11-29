@if($device_totals)
<div class="widget-availability-host">
    <span>{{ __('Total hosts') }}</span>
    @if($show_disabled_and_ignored)
        <a href="{{ url('devices/disable_notify=1') }}"><span class="label label-default label-font-border label-border">{{ __('alert-disabled') }}: {{ $device_totals['ignored'] }}</span></a>
        <a href="{{ url('devices/disabled=1') }}"><span class="label blackbg label-font-border label-border">{{ __('disabled') }}: {{ $device_totals['disabled'] }}</span></a>
    @endif
    <a href="{{ url('devices/state=up') }}@if($device_group){{ '/group='.$device_group }}@endif"><span class="label label-success label-font-border label-border">{{ __('up') }}: {{ $device_totals['up'] }}</span></a>
    <span class="label label-warning label-font-border label-border">{{ __('warn') }}: {{ $device_totals['warn'] }}</span>
    <a href="{{ url('devices/state=down') }}@if($device_group){{ '/group='.$device_group }}@endif"><span class="label label-danger label-font-border label-border">{{ __('down') }}: {{ $device_totals['down'] }}</span></a>
    @if($device_totals['maintenance'])
    <span class="label label-default label-font-border label-border">{{ __('maintenance') }}: {{ $device_totals['maintenance'] }}</span>
    @endif
</div>
@endif

@if($services_totals)
<div class="widget-availability-service">
    <span>{{ __('Total services') }}</span>
    <span class="label label-success label-font-border label-border">{{ __('up') }}: {{ $services_totals['up'] }}</span>
    <span class="label label-warning label-font-border label-border">{{ __('warn') }}: {{ $services_totals['warn'] }}</span>
    <span class="label label-danger label-font-border label-border">{{ __('down') }}: {{ $services_totals['down'] }}</span>
</div>
@endif

<br style="clear:both;">

@foreach($devices as $row)
    <a href="@deviceUrl($row['device'])" title="{{$row['device']->displayName() }}@if($row['stateName'] == 'up' or $row['stateName'] == 'warn')@if($row['device']->formatDownUptime(true)) - @endif{{ $row['device']->formatDownUptime(true) }}@elseif($row['stateName'] == 'down')@if($row['device']->formatDownUptime(true)) - downtime @endif{{$row['device']->formatDownUptime(true)}}@endif">
        @if($type == 0)
            @if($color_only_select == 1)
                <span class="label {{ $row['labelClass'] }} widget-availability-fixed widget-availability label-font-border"> </span>
            @else
            @if($color_only_select == 2)
                <span class="label {{ $row['labelClass'] }} widget-availability label-font-border">{{ __($row['device']->hostname) }}</span>
            @else
            @if($color_only_select == 3)
                <span class="label {{ $row['labelClass'] }} widget-availability label-font-border">{{ __($row['device']->sysName) }}</span>
            @else
                <span class="label {{ $row['labelClass'] }} widget-availability label-font-border">{{ __($row['stateName']) }}</span>
            @endif
            @endif
            @endif
        @else
            <div class="availability-map-oldview-box-{{ $row['stateName'] }}" style="width:{{ $tile_size }}px;height:{{ $tile_size }}px;"></div>
        @endif
    </a>
@endforeach

@foreach($services as $row)
    <a href="@deviceUrl($row['service']->device, ['tab' => 'services'])" title="{{ $row['service']->device->displayName() }} - {{ $row['service']->service_type }} - {{ $row['service']->service_desc }}">
        @if($type == 0)
            @if($color_only_select)
                <span class="label {{ $row['labelClass'] }} widget-availability-fixed widget-availability label-font-border"> </span>
            @else
                <span class="label {{ $row['labelClass'] }} widget-availability label-font-border">{{ $row['service']->service_type }} - {{ $row['stateName'] }}</span>
            @endif
        @else
            <div class="availability-map-oldview-box-{{ $row['stateName'] }}" style="width:{{ $tile_size }}px;height:{{ $tile_size }}px;"></div>
        @endif
    </a>
@endforeach
