@if($flags || $options)
    <x-table :columns="[__('Option'), __('Value')]" :responsive="false" :hover="false">
        <x-slot:body>
            @if($flags)
                <tr><td>{{ __('Flags') }}</td><td>{{ implode(', ', $flags) }}</td></tr>
            @endif
            @foreach($options as $key => $value)
                <tr><td>{{ $key }}</td><td>{{ $value }}</td></tr>
            @endforeach
        </x-slot:body>
    </x-table>
@endif
