@props(['accordionId'])

<div {{ $attributes->merge(['class' => 'accordion']) }} id="accordion{{$accordionId}}">
    {{ $slot }}
</div>
