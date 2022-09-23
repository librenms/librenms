<div class="panel panel-default">
    <div class="panel-heading">
        @foreach ($menu as $header => $m)
            @if($loop->first)
                <b>{{ $title }}</b>
                »
            @else
                | {{ $header }}:
            @endif

            @foreach($m as $sm)
                @if($isSelected($sm['url']))<span class="pagemenu-selected">@endif
                <a href="{{ route('device', ['device' => $device_id, 'tab' => $current_tab, 'vars' => $sm['url']]) }}">{{ $sm['name'] }}</a>@if($isSelected($sm['url']))</span>@endif

                @if(!$loop->last)
                    |
                @endif
            @endforeach
        @endforeach
    </div>
</div>
