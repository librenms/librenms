@props(['menu' => [], 'title' => '', 'base' => url()->current(), 'current' => null])

<span>
    @foreach ($menu as $header => $m)
        @php($current_value = $current ?: request()->input($header))
        @if($loop->first)
            <b>{{ $title }}</b>
            »
        @else
            | {{ $header }}:
        @endif

        @foreach($m as $sm)
            <span @if($current_value ? $current_value == $sm['key'] : isset($sm['default'])) class="pagemenu-selected" @endif><a
                        href="{{ $base . '?' . Arr::query([$header => $sm['key']] + request()->all()) }}"
                >{{ $sm['name'] }}</a></span>

            @if(!$loop->last)
                |
            @endif
        @endforeach
    @endforeach
    {{ $slot }}
</span>
