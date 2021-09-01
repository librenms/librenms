@props([
'loading' => 'eager',
'width' => 340,
'height' => 100,
])

<img loading="{{ $loading }}" src="{{ $link }}" {{ $attributes }}>
