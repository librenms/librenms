@props([
    'adjacencies',
    'showDevice' => false,
])
<x-table striped :rows="$adjacencies" :empty="__('No IS-IS adjacencies found.')">
    <x-slot:head>
        <tr>
            @if($showDevice)
                <th>{{ __('Local Device') }}</th>
            @endif
            <th>{{ __('Local Interface') }}</th>
            <th>{{ __('Adjacent IP') }}</th>
            <th>{{ __('System ID') }}</th>
            <th>{{ __('Area') }}</th>
            <th>{{ __('System Type') }}</th>
            <th>{{ __('Admin') }}</th>
            <th>{{ __('State') }}</th>
            <th>{{ __('Last Uptime') }}</th>
        </tr>
    </x-slot:head>
    <x-slot:body>
        @foreach($adjacencies as $adj)
            <tr>
                @if($showDevice)
                    <td><x-device-link :device="$adj->device" tab="routing" section="isis" /></td>
                @endif
                <td>
                    @if($adj->port)
                        <x-port-link :port="$adj->port" />
                    @else
                        <span class="text-muted">{{ __('Port') }} #{{ $adj->port_id }}</span>
                    @endif
                </td>
                <td>{{ $adj->isisISAdjIPAddrAddress }}</td>
                <td>{{ $adj->isisISAdjNeighSysID }}</td>
                <td>{{ $adj->isisISAdjAreaAddress }}</td>
                <td>{{ $adj->isisISAdjNeighSysType }}</td>
                <td>{{ $adj->isisCircAdminState }}</td>
                <td>
                    <span @class(['label', 'label-success' => $adj->isisISAdjState === 'up', 'label-danger' => $adj->isisISAdjState === 'down', 'label-warning' => ! in_array($adj->isisISAdjState, ['up', 'down'])])>{{ $adj->isisISAdjState }}</span>
                </td>
                <td>{{ \LibreNMS\Util\Time::formatInterval($adj->isisISAdjLastUpTime) }}</td>
            </tr>
        @endforeach
    </x-slot:body>
</x-table>
