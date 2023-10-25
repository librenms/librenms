@extends('device.index')

@section('tab')
    <x-option-bar name="{{ trans('stp.vlan') }}" :options="$data['vlans']" :selected="$data['vlan']"></x-option-bar>

    @foreach($data['stpInstances'] as $instance)
        <x-panel class="stp-panel">
            <x-slot name="title"><span class="tw-font-bold">{{ trans('stp.stp_info') }}</span></x-slot>
            <table class="table table-condensed table-striped table-hover">
                <tr>
                    <td>{{ trans('stp.root_bridge') }}</td>
                    <td>{{ $instance['rootBridge'] ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <td>{{ trans('stp.bridge_address') }}</td>
                    <td>
                        {{ \LibreNMS\Util\Mac::parse($instance['bridgeAddress'])->readable() }}
                        @if($url = \LibreNMS\Util\Url::deviceLink(\App\Facades\DeviceCache::get(\App\Models\Stp::where('bridgeAddress', $instance['bridgeAddress'])->value('device_id'))))
                            ({!! $url !!})
                        @elseif($brVendor = \LibreNMS\Util\Mac::parse($instance['bridgeAddress'])->vendor())
                            ({{ $brVendor }})
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>{{ trans('stp.protocol') }}</td>
                    <td>{{ $instance['protocolSpecification'] }}</td>
                </tr>
                <tr>
                    <td>{{ trans('stp.priority') }}</td>
                    <td>{{ $instance['priority'] }}</td>
                </tr>
                <tr>
                    <td>{{ trans('stp.last_topology_change') }}</td>
                    <td>{{ \LibreNMS\Util\Time::formatInterval($instance['timeSinceTopologyChange']) }}</td>
                </tr>
                <tr>
                    <td>{{ trans('stp.topology_changes') }}</td>
                    <td>{{ $instance['topChanges'] }}</td>
                </tr>
                <tr>
                    <td>{{ trans('stp.designated_root') }}</td>
                    <td>
                        {{ \LibreNMS\Util\Mac::parse($instance['designatedRoot'])->readable() }}
                        @if($url = \LibreNMS\Util\Url::deviceLink(\App\Facades\DeviceCache::get(\App\Models\Stp::where('bridgeAddress', $instance['designatedRoot'])->value('device_id'))))
                            ({!! $url !!})
                        @elseif($drVendor = \LibreNMS\Util\Mac::parse($instance['designatedRoot'])->vendor())
                            ({{ $drVendor }})
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>{{ trans('stp.root_cost') }}</td>
                    <td>{{ $instance['rootCost'] }}</td>
                </tr>
                <tr>
                    <td>{{ trans('stp.root_port') }}</td>
                    <td>{{ $instance['rootPort'] }}</td>
                </tr>
                <tr>
                    <td>{{ trans('stp.max_age') }}</td>
                    <td>{{ $instance['maxAge'] }}</td>
                </tr>
                <tr>
                    <td>{{ trans('stp.hello_time') }}</td>
                    <td>{{ $instance['helloTime'] }}</td>
                </tr>
                <tr>
                    <td>{{ trans('stp.hold_time') }}</td>
                    <td>{{ $instance['holdTime'] }}</td>
                </tr>
                <tr>
                    <td>{{ trans('stp.forward_delay') }}</td>
                    <td>{{ $instance['forwardDelay'] }}</td>
                </tr>
                <tr>
                    <td>{{ trans('stp.bridge_max_age') }}</td>
                    <td>{{ $instance['bridgeMaxAge'] }}</td>
                </tr>
                <tr>
                    <td>{{ trans('stp.bridge_hello_time') }}</td>
                    <td>{{ $instance['bridgeHelloTime'] }}</td>
                </tr>
                <tr>
                    <td>{{ trans('stp.bridge_forward_delay') }}</td>
                    <td>{{ $instance['bridgeForwardDelay'] }}</td>
                </tr>
            </table>
        </x-panel>
    @endforeach

    @if($data['stpPorts'])
        <x-panel class="stp-panel">
            <x-slot name="title"><span class="tw-font-bold">{{ trans('stp.stp_ports') }}</span></x-slot>
            <div class="table-responsive">
                <table id="stp-ports" class="table table-condensed table-hover">
                    <thead>
                    <tr>
                        <th data-column-id="port_id">Port</th>
                        <th data-column-id="vlan" data-visible="false">{{ trans('stp.vlan') }}</th>
                        <th data-column-id="priority">{{ trans('stp.priority') }}</th>
                        <th data-column-id="state">State</th>
                        <th data-column-id="enable">Enable</th>
                        <th data-column-id="pathCost">Path cost</th>
                        <th data-column-id="designatedRoot" data-formatter="stpDevice">Designated root</th>
                        <th data-column-id="designatedCost">Designated cost</th>
                        <th data-column-id="designatedBridge" data-formatter="stpDevice">Designated bridge</th>
                        <th data-column-id="designatedPort">Designated port</th>
                        <th data-column-id="forwardTransitions">Forward transitions</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </x-panel>
    @endif
@endsection

@push('scripts')
    <script>
        var grid = $("#stp-ports").bootgrid({
            ajax: true,
            templates: {search: ""},
            formatters: {
                "stpDevice": function (column, row) {
                    var html = '<span title="' + row[column.id + '_vendor'] + '">' + row[column.id] + '</span>';
                    if (row[column.id + '_device']) {
                        html = row[column.id + '_device'] + '<br />' + html;
                    }

                    return html;
                }
            },
            post: function () {
                return {
                    device_id: '{{ $data['device_id'] }}',
                    vlan: '{{ $data['vlan'] }}',
                };
            },
            url: "{{ url('/ajax/table/port-stp') }}"
        });

    </script>
@endpush


@push('styles')
    <style>
        .stp-panel .panel-body {
            padding: 0;
            margin-top: -1px;
        }

        .stp-panel .table {
            margin-bottom: 0;
        }
    </style>
@endpush
