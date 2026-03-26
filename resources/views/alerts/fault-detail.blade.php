@foreach($details['sections'] ?? [] as $section)
    @if(isset($section['title']))
        <b>{{ $section['title'] }}:</b><br>
    @endif
    <div style="display: grid; grid-template-columns: auto 1fr; gap: 0 0.5em;">
        @foreach($section['items'] ?? [] as $item)
            <div style="grid-column: 1;">
                @isset($item['type'])
                    {{ ucfirst($item['type']) }}
                @endisset#{{ $item['row'] + 1 }}:
            </div>
            <div style="grid-column: 2; white-space: nowrap;">
                @foreach($item['fields'] ?? [] as $field)
                    <b>{{ $field['label'] }}</b>:
                    @isset($field['url'])
                        <a href="{{ $field['url'] }}">{{ $field['value'] }}</a>
                    @else
                        {{ $field['value'] }}
                    @endisset
                    @if(! $loop->last)<br>@endif
                @endforeach
            </div>
        @endforeach
    </div>
    @if(isset($section['title']))
        <br>
    @endif
@endforeach
