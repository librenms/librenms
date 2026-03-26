<div {{ $attributes->class(['panel panel-default' => $attributes->get('border') !== 'none']) }}>
    <div class="panel-heading" style="border-bottom: 0">
        @if($name)
        <span style="font-weight: bold">{{ $name }}</span> »
        @endif
        @foreach($options as $option_name => $option)
            @if (! $loop->first) | @endif
            <span @if($selected == $option_name)class="pagemenu-selected"@endif>
                <a href="{{ $option['link'] }}">@isset($option['icon'])<i class="fa {{ $option['icon'] }}"></i> @endisset{{ $option['text'] }}</a></span>
        @endforeach
    </div>
</div>
