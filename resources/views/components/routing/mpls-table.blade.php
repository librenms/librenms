@php
    $labelColors = [
        'admin_state' => 'admin_color',
        'admin_status' => 'admin_color',
        'oper_state' => 'oper_color',
        'oper_status' => 'oper_color',
        'paths' => 'path_color',
        'fail_code' => 'fail_code_color',
        'fdb_num_entries' => 'fdb_color',
    ];
    $deviceLinks = [
        'destination' => 'destination_device',
        'fail_node' => 'fail_node_device',
    ];
@endphp
<x-table :columns="$columns" :rows="$rows" :empty="$empty" striped>
    <x-slot:body>
        @foreach($rows as $row)
            <tr>
                @foreach($columns as $key => $column)
                    <td>
                        @if($key === 'device')
                            <x-device-link :device="$row['device']" tab="routing" section="mpls" />
                        @elseif(isset($deviceLinks[$key]) && $row[$deviceLinks[$key]])
                            <x-device-link :device="$row[$deviceLinks[$key]]" tab="routing" section="mpls" />
                        @elseif($key === 'port')
                            @if($row['port'])
                                <x-port-link :port="$row['port']" />
                            @else
                                {{ $row['port_name'] }}
                            @endif
                        @elseif($key === 'svc_oid' && isset($row['graph_vars']))
                            <x-popup>
                                <strong>{{ $row['svc_oid'] }}</strong>
                                <x-slot name="title">{{ __('SAP Traffic') }}</x-slot>
                                <x-slot name="body">
                                    <x-graph-row loading="lazy" type="device_sap" :vars="$row['graph_vars']" />
                                </x-slot>
                            </x-popup>
                        @elseif(isset($labelColors[$key]))
                            <span class="label label-{{ $row[$labelColors[$key]] }}">{{ $row[$key] }}</span>
                        @elseif($loop->index === ($showDevice ? 1 : 0))
                            <strong>{{ $row[$key] }}</strong>
                        @else
                            {{ $row[$key] ?? '' }}
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </x-slot:body>
</x-table>
