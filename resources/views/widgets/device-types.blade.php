<x-panel class="table-responsive">
    <x-slot name="table">
     <table class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th>&nbsp;</th>
                @foreach ($device_types as $device_type)
                    @if ($device_type['visible'])
                    <th>{{ ucfirst($device_type['type']) }}</th>
                    @endif
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ __('Summary') }}</td>
                @foreach ($device_types as $device_type)
                    @if ($device_type['visible'])
                    <td>{{ $device_type['count'] }}</td>
                    @endif
                @endforeach
            </tr>
        </tbody>
    </table>
    </x-slot>
</x-panel>
