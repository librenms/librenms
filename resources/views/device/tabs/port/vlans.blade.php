<x-panel>
    <x-slot:table>
        <table class="table table-hover table-striped table-condensed">
            <thead>
            <tr>
                <th>{{ __('VLAN') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Cost') }}</th>
                <th>{{ __('Priority') }}</th>
                <th>{{ __('State') }}</th>
                <th>{{ __('Other Ports') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($vlans as $vlan)
                <tr>
                    <td>{{ __('Vlan') }} {{ $vlan['vlan']->vlan }}</td>
                    <td>{{ $vlan['name'] }}</td>
                    <td>{{ $vlan['vlan']->cost }}</td>
                    <td>{{ $vlan['vlan']->priority }}</td>
                    <td @class(['red' => $vlan['vlan']->state == 'blocking', 'green' => $vlan['vlan']->state == 'forwarding'])>{{ $vlan['vlan']->state }}</td>
                    <td>
                        @foreach($vlan['ports'] as $member)
                            <x-port-link :port="$member['port']">{{ \LibreNMS\Util\Rewrite::shortenIfName($member['port']->ifDescr) }}</x-port-link>{{ $member['untagged'] ? '(U)' : '' }}{{ $loop->last ? '' : ',' }}
                        @endforeach
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-slot:table>
</x-panel>
