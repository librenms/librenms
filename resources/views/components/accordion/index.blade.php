@props(['accordionId'])

<div id="accordion{{$accordionId}}">
    {{ $slot }}
</div>
