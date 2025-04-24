<x-panel body-class="tw:p-0!">
    <table class="table table-hover table-condensed tw:mt-1 tw:mb-0!">
        <thead>
            <tr>
                <th>Local Port</th>
                <th>Remote Device</th>
                <th>Remote Port</th>
                <th>Protocol</th>
            </tr>
        </thead>
        @foreach($data['links'] as $link)
            <tr>
                <td>
                    @if($link->port)
                        <x-port-link :port="$link->port"></x-port-link>
                        @if($link->port->getLabel() !== $link->port->getDescription() )
                            <br />{{ $link->port->getDescription() }}
                        @endif
                    @else
                        {{ __('port.unknown_port') }}
                    @endif
                </td>
                <td>
                    @if($link->remoteDevice)
                        <x-device-link :device="$link->remoteDevice"></x-device-link>
                    @else
                        {{ $link->remote_hostname }}
                    @endif
                     <br />
                        {{ $link->remote_platform }}
                </td>
                <td>
                    @if($link->remotePort)
                        <x-port-link :port="$link->remotePort"></x-port-link>
                        @if($link->remotePort->getLabel() !== $link->remotePort->getDescription() )
                            <br />{{ $link->remotePort->getDescription() }}
                        @endif
                    @else
                        {{ $link->remote_port }}
                    @endif
                </td>
                <td>{{ strtoupper($link->protocol) }}</td>
            </tr>
        @endforeach
    </table>
</x-panel>
