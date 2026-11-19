@if($flags || $options)
    <table class="table table-condensed">
        <thead>
            <tr><th>{{ __('Option') }}</th><th>{{ __('Value') }}</th></tr>
        </thead>
        <tbody>
            @if($flags)
                <tr><td>{{ __('Flags') }}</td><td>{{ implode(', ', $flags) }}</td></tr>
            @endif
            @foreach($options as $key => $value)
                <tr><td>{{ $key }}</td><td>{{ $value }}</td></tr>
            @endforeach
        </tbody>
    </table>
@endif
