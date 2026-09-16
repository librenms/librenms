@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" :subtitle="__('MPLS')">
        <x-device.routing-tabs :device="$device" tab="mpls" />

        <x-option-bar name="{{ __('MPLS') }}" :options="$mpls_options" :selected="$view" />

        <x-panel>
            <div class="table-responsive">
                @if($view === 'lsp')
                    <table class="table table-hover table-condensed table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Destination') }}</th>
                                <th>{{ __('VRF') }}</th>
                                <th>{{ __('Admin State') }}</th>
                                <th>{{ __('Oper State') }}</th>
                                <th>{{ __('Last Change') }}</th>
                                <th>{{ __('Transitions') }}</th>
                                <th>{{ __('Last Transition') }}</th>
                                <th>{{ __('Paths (Conf / Stby / Oper)') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('FRR') }}</th>
                                <th>{{ __('Availability %') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td class="tw:font-bold">{{ $item['name'] }}</td>
                                <td>
                                    @if($item['destination_device'])
                                        <x-device-link :device="$item['destination_device']" tab="routing" section="mpls" />
                                    @else
                                        {{ $item['to_addr'] }}
                                    @endif
                                </td>
                                <td>{{ $item['vrf_name'] ?: '-' }}</td>
                                <td><span class="label label-{{ $item['admin_color'] }}">{{ $item['admin_state'] }}</span></td>
                                <td><span class="label label-{{ $item['oper_color'] }}">{{ $item['oper_state'] }}</span></td>
                                <td>{{ $item['last_change'] }}</td>
                                <td>{{ $item['transitions'] }}</td>
                                <td>{{ $item['last_transition'] }}</td>
                                <td><span class="label label-{{ $item['path_color'] }}">{{ $item['configured_paths'] }} / {{ $item['standby_paths'] }} / {{ $item['operational_paths'] }}</span></td>
                                <td>{{ $item['type'] }}</td>
                                <td>{{ $item['fast_reroute'] }}</td>
                                <td>{{ $item['availability'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center tw:p-5">
                                    <em>{{ __('No MPLS LSPs found for this device.') }}</em>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                @elseif($view === 'paths')
                    <table class="table table-hover table-condensed table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('LSP Name') }}</th>
                                <th>{{ __('Index') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Admin State') }}</th>
                                <th>{{ __('Oper State') }}</th>
                                <th>{{ __('Last Change') }}</th>
                                <th>{{ __('Transitions') }}</th>
                                <th>{{ __('Bandwidth') }}</th>
                                <th>{{ __('Oper BW') }}</th>
                                <th>{{ __('State') }}</th>
                                <th>{{ __('Failcode') }}</th>
                                <th>{{ __('Fail Node') }}</th>
                                <th>{{ __('Metric') }}</th>
                                <th>{{ __('Oper Metric') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td class="tw:font-bold">{{ $item['name'] }}</td>
                                <td>{{ $item['path_oid'] }}</td>
                                <td>{{ $item['type'] }}</td>
                                <td><span class="label label-{{ $item['admin_color'] }}">{{ $item['admin_state'] }}</span></td>
                                <td><span class="label label-{{ $item['oper_color'] }}">{{ $item['oper_state'] }}</span></td>
                                <td>{{ $item['last_change'] }}</td>
                                <td>{{ $item['transition_count'] }}</td>
                                <td>{{ $item['bandwidth'] }}</td>
                                <td>{{ $item['oper_bandwidth'] }}</td>
                                <td>{{ $item['state'] }}</td>
                                <td><span class="label label-{{ $item['fail_code_color'] }}">{{ $item['fail_code'] }}</span></td>
                                <td>
                                    @if($item['fail_node_device'])
                                        <x-device-link :device="$item['fail_node_device']" tab="routing" section="mpls" />
                                    @else
                                        {{ $item['fail_node_addr'] ?: '-' }}
                                    @endif
                                </td>
                                <td>{{ $item['metric'] }}</td>
                                <td>{{ $item['oper_metric'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center tw:p-5">
                                    <em>{{ __('No MPLS LSP paths found for this device.') }}</em>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                @elseif($view === 'sdps')
                    <table class="table table-hover table-condensed table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('SDP Id') }}</th>
                                <th>{{ __('Destination') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('LSP Type') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Admin State') }}</th>
                                <th>{{ __('Oper State') }}</th>
                                <th>{{ __('Admin MTU') }}</th>
                                <th>{{ __('Oper MTU') }}</th>
                                <th>{{ __('Last Mgmt Change') }}</th>
                                <th>{{ __('Last Status Change') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td class="tw:font-bold">{{ $item['sdp_oid'] }}</td>
                                <td>
                                    @if($item['destination_device'])
                                        <x-device-link :device="$item['destination_device']" tab="routing" section="mpls" />
                                    @else
                                        {{ $item['far_end_addr'] }}
                                    @endif
                                </td>
                                <td>{{ $item['delivery'] }}</td>
                                <td>{{ $item['active_lsp_type'] }}</td>
                                <td>{{ $item['description'] ?: '-' }}</td>
                                <td><span class="label label-{{ $item['admin_color'] }}">{{ $item['admin_status'] }}</span></td>
                                <td><span class="label label-{{ $item['oper_color'] }}">{{ $item['oper_status'] }}</span></td>
                                <td>{{ $item['admin_path_mtu'] }}</td>
                                <td>{{ $item['oper_path_mtu'] }}</td>
                                <td>{{ $item['last_mgmt_change'] }}</td>
                                <td>{{ $item['last_status_change'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center tw:p-5">
                                    <em>{{ __('No MPLS SDPs found for this device.') }}</em>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                @elseif($view === 'sdpbinds')
                    <table class="table table-hover table-condensed table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Service ID') }}</th>
                                <th>{{ __('SDP Bind Id') }}</th>
                                <th>{{ __('Bind Type') }}</th>
                                <th>{{ __('VC Type') }}</th>
                                <th>{{ __('Admin State') }}</th>
                                <th>{{ __('Oper State') }}</th>
                                <th>{{ __('Last Mgmt Change') }}</th>
                                <th>{{ __('Last Status Change') }}</th>
                                <th>{{ __('Ing Fwd Packets') }}</th>
                                <th>{{ __('Ing Fwd Octets') }}</th>
                                <th>{{ __('Egr Fwd Packets') }}</th>
                                <th>{{ __('Egr Fwd Octets') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $item['svc_id'] }}</td>
                                <td class="tw:font-bold">{{ $item['sdp_oid'] }}:{{ $item['svc_oid'] }}</td>
                                <td>{{ $item['bind_type'] }}</td>
                                <td>{{ $item['vc_type'] }}</td>
                                <td><span class="label label-{{ $item['admin_color'] }}">{{ $item['admin_status'] }}</span></td>
                                <td><span class="label label-{{ $item['oper_color'] }}">{{ $item['oper_status'] }}</span></td>
                                <td>{{ $item['last_mgmt_change'] }}</td>
                                <td>{{ $item['last_status_change'] }}</td>
                                <td>{{ $item['ing_fwd_packets'] }}</td>
                                <td>{{ $item['ing_fwd_octets'] }}</td>
                                <td>{{ $item['egr_fwd_packets'] }}</td>
                                <td>{{ $item['egr_fwd_octets'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center tw:p-5">
                                    <em>{{ __('No MPLS SDP binds found for this device.') }}</em>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                @elseif($view === 'services')
                    <table class="table table-hover table-condensed table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Service ID') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Admin Status') }}</th>
                                <th>{{ __('Oper Status') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Service MTU') }}</th>
                                <th>{{ __('Num SAPs') }}</th>
                                <th>{{ __('Last Mgmt Change') }}</th>
                                <th>{{ __('Last Status Change') }}</th>
                                <th>{{ __('VRF') }}</th>
                                <th>{{ __('MAC Learning') }}</th>
                                <th>{{ __('FDB Table Size') }}</th>
                                <th>{{ __('FDB Entries') }}</th>
                                <th>{{ __('STP Admin Status') }}</th>
                                <th>{{ __('STP Oper Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td class="tw:font-bold">{{ $item['svc_oid'] }}</td>
                                <td>{{ $item['type'] }}</td>
                                <td>{{ $item['cust_id'] }}</td>
                                <td><span class="label label-{{ $item['admin_color'] }}">{{ $item['admin_status'] }}</span></td>
                                <td><span class="label label-{{ $item['oper_color'] }}">{{ $item['oper_status'] }}</span></td>
                                <td>{{ $item['description'] ?: '-' }}</td>
                                <td>{{ $item['mtu'] }}</td>
                                <td>{{ $item['num_saps'] }}</td>
                                <td>{{ $item['last_mgmt_change'] }}</td>
                                <td>{{ $item['last_status_change'] }}</td>
                                <td>{{ $item['vrf_name'] ?: '-' }}</td>
                                <td>{{ $item['mac_learning'] }}</td>
                                <td>{{ $item['fdb_table_size'] }}</td>
                                <td><span class="label label-{{ $item['fdb_color'] }}">{{ $item['fdb_num_entries'] }}</span></td>
                                <td>{{ $item['stp_admin_status'] }}</td>
                                <td>{{ $item['stp_oper_status'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="16" class="text-center tw:p-5">
                                    <em>{{ __('No MPLS services found for this device.') }}</em>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                @elseif($view === 'saps')
                    <table class="table table-hover table-condensed table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Service ID') }}</th>
                                <th>{{ __('SAP Port') }}</th>
                                <th>{{ __('Encapsulation') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Admin Status') }}</th>
                                <th>{{ __('Oper Status') }}</th>
                                <th>{{ __('Last Mgmt Change') }}</th>
                                <th>{{ __('Last Oper Change') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td class="tw:font-bold">{{ $item['svc_oid'] }}</td>
                                <td>
                                    @if($item['port'])
                                        <x-port-link :port="$item['port']" />
                                    @else
                                        <span class="text-muted">{{ __('Port') }} #{{ $item['port_id'] }}</span>
                                    @endif
                                </td>
                                <td>{{ $item['encap_value'] }}</td>
                                <td>{{ $item['type'] }}</td>
                                <td>{{ $item['description'] ?: '-' }}</td>
                                <td><span class="label label-{{ $item['admin_color'] }}">{{ $item['admin_status'] }}</span></td>
                                <td><span class="label label-{{ $item['oper_color'] }}">{{ $item['oper_status'] }}</span></td>
                                <td>{{ $item['last_mgmt_change'] }}</td>
                                <td>{{ $item['last_oper_change'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="tw:text-center tw:p-5">
                                    <em>{{ __('No MPLS SAPs found for this device.') }}</em>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                @endif
            </div>
        </x-panel>
    </x-device.page>
@endsection
