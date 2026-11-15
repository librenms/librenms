@props(['pseudowire'])

@if($port = $pseudowire?->port)
    <x-port-link :port="$port" />
    @if($port->ifOperStatus === \LibreNMS\Enum\IfOperStatus::Up)
        <i class="fa fa-arrow-up tw:text-green-600 tw:dark:text-green-400" aria-hidden="true"></i>
    @elseif($port->ifOperStatus === \LibreNMS\Enum\IfOperStatus::Down)
        <i class="fa fa-arrow-down tw:text-red-600 tw:dark:text-red-400" aria-hidden="true"></i>
    @else
        <i class="fa fa-question tw:text-amber-600 tw:dark:text-amber-400" aria-hidden="true"></i>
    @endif
    <br><span class="tw:text-xs">{{ $port->ifAlias }}</span>
    @if($port->ifMtu)
        <br><span class="tw:text-xs">MTU {{ $port->ifMtu }}</span>
    @endif
    @if($pseudowire->pw_local_mtu)
        <br><span class="tw:text-xs">PW MTU {{ $pseudowire->pw_local_mtu }}</span>
    @endif
@endif
