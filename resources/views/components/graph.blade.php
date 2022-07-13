<img width="{{ $width }}" height="{{ $height }}" src="{{ $src }}" alt="{{ $type }}" {{ $attributes->merge(['class' => 'graph-image'])->filter($filterAttributes) }}>
