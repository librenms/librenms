@props([
    'name' => null, // string display title
    'options' => [], // array<string, array{link: string, text: string, icon?: string, title?: string, selected?: bool}>
    'selected' => null, // string selected item matching key, an option's own 'selected' takes precedence
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
            <span @if($option['selected'] ?? ($selected == $option_name))class="pagemenu-selected"@endif>
                <a href="{{ $option['link'] }}" @if($linkClass) class="{{ $linkClass }}" @endif @isset($option['title']) title="{{ $option['title'] }}" @endisset>@isset($option['icon'])<i class="fa {{ $option['icon'] }}" aria-hidden="true"></i> @endisset{{ $option['text'] }}</a></span>
        @endforeach
    </div>
</div>
