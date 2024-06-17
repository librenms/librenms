<div class="panel panel-default">
    <div class="panel-heading">
        @foreach ($menu as $header => $m)
            @if($loop->first)
                <span class="tw-font-bold">{{ $title }} »</span>
            @else
                <span class="tw-ml-4 tw-font-bold">{{ $header }}:</span>
            @endif

            @foreach($m as $sm)
                <span @if($isSelected($sm['url']))class="pagemenu-selected"@endif><a href="{{ route('device', ['device' => $device_id, 'tab' => $current_tab, 'vars' => $sm['url']]) }}">{{ $sm['name'] }}</a></span>

                @isset($sm['sub_name'])
                    (<span @if($isSelected($sm['sub_url']))class="pagemenu-selected"@endif><a href="{{ route('device', ['device' => $device_id, 'tab' => $current_tab, 'vars' => $sm['sub_url']]) }}">{{ $sm['sub_name'] }}</a></span>)
                @endisset

                @if(!$loop->last)
                    |
                @endif
            @endforeach
        @endforeach
    </div>
</div>
