@if($alert_totals)
<div class="widget-alert-totals">
    <span>{{ __('Total alerts') }}</span>
    <span class="label label-success label-font-border label-border">{{ __('Ok') }}: {{ $alert_totals['ok'] }}</span></a>
    <span class="label label-warning label-font-border label-border">{{ __('Warning') }}: {{ $alert_totals['warning'] }}</span>
    <span class="label label-danger label-font-border label-border">{{ __('Critical') }}: {{ $alert_totals['critical'] }}</span></a>
</div>
@endif

<br style="clear:both;">

@foreach($devices as $row)
    <a href="{{ $row['link'] }}" title="{{$row['tooltip'] }}">
        @if($type == 0)
            <span class="label {{ $row['labelClass'] }} widget-alert label-font-border">{{ $row['label'] }}</span>
        @else
            <div class="{{ $row['labelClass'] }}" style="width:{{ $tile_size }}px;height:{{ $tile_size }}px;"></div>
        @endif
    </a>
@endforeach
