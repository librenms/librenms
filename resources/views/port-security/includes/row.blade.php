<tr>
    @if($showDevice ?? false)
        <td>
            <x-device-link :device="$entry->device"/>
        </td>
    @endif
    <td>
        @if($entry->port)
            <x-port-link :port="$entry->port">{{ $entry->port->getShortLabel() }}</x-port-link>
        @else
            N/A
        @endif
    </td>
    <td>{{ $entry->port->ifAlias ?? 'N/A' }}</td>
    <td>
        @if($entry->port_security_enable === null)
            N/A
        @else
            {{ $entry->port_security_enable ? __('Yes') : __('No') }}
        @endif
    </td>
    <td>
        <i class="fa {{ \LibreNMS\Enum\PortSecurityStatus::getIconClass($entry->status ?? '') }}" aria-hidden="true" title="{{ $entry->status }}"></i>
        {{ $entry->status ?? 'N/A' }}
    </td>
    <td>{{ $entry->address_count ?? 'N/A' }}</td>
    <td>{{ $entry->max_addresses ?? 'N/A' }}</td>
    <td>{{ $entry->violation_action ?? 'N/A' }}</td>
    <td>{{ $entry->violation_count ?? 'N/A' }}</td>
    <td>{{ $entry->last_mac_address ?? 'N/A' }}</td>
    <td>
        @if($entry->sticky_enable === null)
            N/A
        @else
            {{ $entry->sticky_enable ? __('Yes') : __('No') }}
        @endif
    </td>
</tr>
