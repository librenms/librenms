<div>
    <div class="panel panel-default">
        <div class="panel-heading" style="border-bottom: 0">
            <span style="font-weight: bold">{{ $name }}</span> »
            @foreach($options as $option_name => $option)
                @if (! $loop->first) | @endif
                <span @if($selected == $option_name)class="pagemenu-selected"@endif>
                    <a href="{{ $option['link'] }}">{{ $option['text'] }}</a>
                </span>
            @endforeach
        </div>
    </div>
</div>
