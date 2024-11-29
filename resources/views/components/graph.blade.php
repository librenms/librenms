<img width="{{ $width }}" height="{{ $height }}" src="{{ $src }}" alt="{{ $type }}" {{ $attributes->filter($filterAttributes)->merge(['class' => 'graph-image']) }} {{ $attributes->only('loading') }}>
