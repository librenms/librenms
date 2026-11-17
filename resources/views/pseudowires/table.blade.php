<x-table striped :rows="$circuits"
    :empty="$showDevice ? __('No pseudowires found.') : __('No pseudowires found for this device.')"
    :columns="[
        __('PW ID'),
        ...($showDevice ? [__('Device')] : []),
        __('Local PW Name'),
        __('Local Port'),
        ['label' => '', 'class' => 'tw:w-10'],
        __('Remote Device/PW Name'),
        __('Remote Port'),
    ]">
    <x-slot:body>
        @foreach($circuits as $circuit)
            <tr>
                <td class="tw:text-lg tw:p-1 tw:align-middle">{{ $circuit['vcId'] }}</td>
                @foreach($circuit['endpoints'] as $side => ['device' => $endpointDevice, 'description' => $description, 'port' => $port, 'mtu' => $mtu])
                    @if($side === 'local')
                        @if($showDevice)
                            <td class="tw:align-middle"><x-device-link :device="$endpointDevice" /></td>
                        @endif
                        <td>
                            {{ $description }}
                            <br><span class="tw:text-xs">{{ $circuit['type'] }} {{ $circuit['psnType'] }}</span>
                        </td>
                    @else
                        <td class="tw:align-middle tw:text-center">
                            <i class="fa fa-arrows-alt tw:text-2xl" aria-hidden="true"></i>
                        </td>
                        <td class="tw:align-middle">
                            @if($endpointDevice)
                                <x-device-link :device="$endpointDevice" />
                            @else
                                <span class="tw:italic">{{ __('unresolved remote device') }}</span>
                            @endif
                            @if($description)
                                <br><span class="tw:text-xs">{{ $description }}</span>
                            @endif
                        </td>
                    @endif
                    <td>
                        @if($port)
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
                            @if($mtu)
                                <br><span class="tw:text-xs">PW MTU {{ $mtu }}</span>
                            @endif
                        @endif
                    </td>
                @endforeach
            </tr>

            @if($view === 'minigraphs')
                <tr>
                    <td colspan="{{ $showDevice ? 2 : 1 }}"></td>
                    @foreach($circuit['endpoints'] as $side => ['port' => $port])
                        @if($side === 'remote')
                            <td></td>
                        @endif
                        <td colspan="2">
                            @if($port)
                                @foreach(['port_bits', 'port_upkts', 'port_errors'] as $graphType)
                                    <x-port-link :port="$port" basic>
                                        <x-graph :port="$port" :type="$graphType" :link="false" from="-1d" width="150" height="30" />
                                    </x-port-link>
                                @endforeach
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endif
        @endforeach
    </x-slot:body>
</x-table>
