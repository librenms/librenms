@props([
    'name' => null, // string display title
    'options' => [], // array<string, array{link: string, text: string, icon?: string}>
    'selected' => null, // string selected item matching key
    'border' => null, // bool disable boder
    'linkClass' => null, // string
])
<div {{ $attributes->class(['panel panel-default' => $border !== 'none']) }}>
    <div {{ $attributes->class(['panel-heading' => $border !== 'none']) }} style="border-bottom: 0">
        @if($name)
        <span style="font-weight: bold">{{ $name }}</span> »
        @endif
        @foreach($options as $option_name => $option)
            @if (! $loop->first) | @endif
            <span @if($selected == $option_name)class="pagemenu-selected"@endif>
                <a href="{{ $option['link'] }}" @class([$linkClass => $linkClass])>@isset($option['icon'])<i class="fa {{ $option['icon'] }}"></i> @endisset{{ $option['text'] }}</a></span>
        @endforeach
    </div>
</div>
