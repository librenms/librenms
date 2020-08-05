<x-panel body-class="{{ $alert_class }}">
    <img src="{{ url($device->logo()) }}" title="{{ $device->logo() }}" class="device-icon-header pull-left" style="max-height: 100px">
    <div class="pull-left" style="margin-top: 5px;">
        @if($parent_id)
            <a href="{{ route('device', $parent_id) }}" title="@lang('VM Host')"><i class="fa fa-server fa-fw fa-lg"></i></a>
        @endif
        @if($device->isUnderMaintenance())
            <span title="@lang('Scheduled Maintenance')" class="fa fa-wrench fa-fw fa-lg"></span>
        @endif
        <span style="font-size: 20px;">@deviceLink($device)</span><br/>
        <a href="{{ url('/devices/location=' . $device->location) }}">{{ $device->location }}</a>
    </div>
    <div class="pull-right">
        @foreach($overview_graphs as $graph)
            <div style='float: right; text-align: center; padding: 1px 5px; margin: 0 1px; ' class='rounded-5px'>
                <div style="width: {{ $graph['width'] }}px; height: {{ $graph['height'] }}px;">{!! \LibreNMS\Util\Url::graphPopup($graph) !!}</div>
                <div style='font-weight: bold; font-size: 7pt; margin: -3px;'>{{ $graph['popup_title'] }}</div>
            </div>
        @endforeach
        <br style="clear: both;"/>
    </div>
</x-panel>
