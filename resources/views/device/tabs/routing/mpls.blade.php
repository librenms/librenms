<div class="panel panel-default">
    <div class="panel-heading" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
        <h3 class="panel-title" style="margin-right: 15px;">
            {{ __('MPLS') }}
        </h3>
        @php
            $views = [
                'lsp' => __('LSPs'),
                'paths' => __('Paths'),
                'sdps' => __('SDPs'),
                'sdpbinds' => __('SDP binds'),
                'services' => __('Services'),
                'saps' => __('SAPs'),
            ];
        @endphp
        <ul class="nav nav-pills" style="margin: 0;">
            @foreach($views as $viewKey => $viewLabel)
                <li class="{{ $data['view'] === $viewKey ? 'active' : '' }}">
                    <a href="{{ route('device', ['device' => $device, 'tab' => 'routing', 'vars' => 'proto=mpls/view=' . $viewKey]) }}">
                        {{ $viewLabel }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            @if($data['view'] === 'lsp')
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
                    @forelse($data['items'] as $item)
                        @php $lsp = $item['lsp']; @endphp
                        <tr>
                            <td class="tw:font-bold">{{ $lsp->mplsLspName }}</td>
                            <td>
                                @if($item['destination_device'])
                                    <x-device-link :device="$item['destination_device']" tab="routing" vars="proto=mpls/view=lsp" />
                                @else
                                    {{ $lsp->mplsLspToAddr }}
                                @endif
                            </td>
                            <td>{{ $lsp->vrf_name ?: '-' }}</td>
                            <td><span class="label label-{{ $item['admin_color'] }}">{{ $lsp->mplsLspAdminState }}</span></td>
                            <td><span class="label label-{{ $item['oper_color'] }}">{{ $lsp->mplsLspOperState }}</span></td>
                            <td>{{ \LibreNMS\Util\Time::formatInterval($lsp->mplsLspLastChange) }}</td>
                            <td>{{ $lsp->mplsLspTransitions }}</td>
                            <td>{{ \LibreNMS\Util\Time::formatInterval($lsp->mplsLspLastTransition) }}</td>
                            <td><span class="label label-{{ $item['path_color'] }}">{{ $lsp->mplsLspConfiguredPaths }} / {{ $lsp->mplsLspStandbyPaths }} / {{ $lsp->mplsLspOperationalPaths }}</span></td>
                            <td>{{ $lsp->mplsLspType }}</td>
                            <td>{{ $lsp->mplsLspFastReroute }}</td>
                            <td>{{ $item['availability'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center" style="padding: 20px;">
                                <em>{{ __('No MPLS LSPs found for this device.') }}</em>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

            @elseif($data['view'] === 'paths')
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
                    @forelse($data['items'] as $item)
                        @php $path = $item['path']; @endphp
                        <tr>
                            <td class="tw:font-bold">{{ $path->mplsLspName }}</td>
                            <td>{{ $path->path_oid }}</td>
                            <td>{{ $path->mplsLspPathType }}</td>
                            <td><span class="label label-{{ $item['admin_color'] }}">{{ $path->mplsLspPathAdminState }}</span></td>
                            <td><span class="label label-{{ $item['oper_color'] }}">{{ $path->mplsLspPathOperState }}</span></td>
                            <td>{{ \LibreNMS\Util\Time::formatInterval($path->mplsLspPathLastChange) }}</td>
                            <td>{{ $path->mplsLspPathTransitionCount }}</td>
                            <td>{{ $path->mplsLspPathBandwidth }}</td>
                            <td>{{ $path->mplsLspPathOperBandwidth }}</td>
                            <td>{{ $path->mplsLspPathState }}</td>
                            <td><span class="label label-{{ $item['fail_code_color'] }}">{{ $path->mplsLspPathFailCode }}</span></td>
                            <td>
                                @if($item['fail_node_device'])
                                    <x-device-link :device="$item['fail_node_device']" tab="routing" vars="proto=mpls" />
                                @else
                                    {{ $path->mplsLspPathFailNodeAddr ?: '-' }}
                                @endif
                            </td>
                            <td>{{ $path->mplsLspPathMetric }}</td>
                            <td>{{ $path->mplsLspPathOperMetric }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center" style="padding: 20px;">
                                <em>{{ __('No MPLS LSP paths found for this device.') }}</em>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

            @elseif($data['view'] === 'sdps')
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
                    @forelse($data['items'] as $item)
                        @php $sdp = $item['sdp']; @endphp
                        <tr>
                            <td class="tw:font-bold">{{ $sdp->sdp_oid }}</td>
                            <td>
                                @if($item['destination_device'])
                                    <x-device-link :device="$item['destination_device']" tab="routing" vars="proto=mpls/view=sdps" />
                                @else
                                    {{ $sdp->sdpFarEndInetAddress }}
                                @endif
                            </td>
                            <td>{{ $sdp->sdpDelivery }}</td>
                            <td>{{ $sdp->sdpActiveLspType }}</td>
                            <td>{{ $sdp->sdpDescription ?: '-' }}</td>
                            <td><span class="label label-{{ $item['admin_color'] }}">{{ $sdp->sdpAdminStatus }}</span></td>
                            <td><span class="label label-{{ $item['oper_color'] }}">{{ $sdp->sdpOperStatus }}</span></td>
                            <td>{{ $sdp->sdpAdminPathMtu }}</td>
                            <td>{{ $sdp->sdpOperPathMtu }}</td>
                            <td>{{ \LibreNMS\Util\Time::formatInterval($sdp->sdpLastMgmtChange) }}</td>
                            <td>{{ \LibreNMS\Util\Time::formatInterval($sdp->sdpLastStatusChange) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center" style="padding: 20px;">
                                <em>{{ __('No MPLS SDPs found for this device.') }}</em>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

            @elseif($data['view'] === 'sdpbinds')
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
                    @forelse($data['items'] as $item)
                        @php $sdpbind = $item['sdpbind']; @endphp
                        <tr>
                            <td>{{ $sdpbind->svcId }}</td>
                            <td class="tw:font-bold">{{ $sdpbind->sdp_oid }}:{{ $sdpbind->svc_oid }}</td>
                            <td>{{ $sdpbind->sdpBindType }}</td>
                            <td>{{ $sdpbind->sdpBindVcType }}</td>
                            <td><span class="label label-{{ $item['admin_color'] }}">{{ $sdpbind->sdpBindAdminStatus }}</span></td>
                            <td><span class="label label-{{ $item['oper_color'] }}">{{ $sdpbind->sdpBindOperStatus }}</span></td>
                            <td>{{ \LibreNMS\Util\Time::formatInterval($sdpbind->sdpBindLastMgmtChange) }}</td>
                            <td>{{ \LibreNMS\Util\Time::formatInterval($sdpbind->sdpLastStatusChange) }}</td>
                            <td>{{ $sdpbind->sdpBindBaseStatsIngFwdPackets }}</td>
                            <td>{{ $sdpbind->sdpBindBaseStatsIngFwdOctets }}</td>
                            <td>{{ $sdpbind->sdpBindBaseStatsEgrFwdPackets }}</td>
                            <td>{{ $sdpbind->sdpBindBaseStatsEgrFwdOctets }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center" style="padding: 20px;">
                                <em>{{ __('No MPLS SDP binds found for this device.') }}</em>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

            @elseif($data['view'] === 'services')
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
                    @forelse($data['items'] as $item)
                        @php $svc = $item['service']; @endphp
                        <tr>
                            <td class="tw:font-bold">{{ $svc->svc_oid }}</td>
                            <td>{{ $svc->svcType }}</td>
                            <td>{{ $svc->svcCustId }}</td>
                            <td><span class="label label-{{ $item['admin_color'] }}">{{ $svc->svcAdminStatus }}</span></td>
                            <td><span class="label label-{{ $item['oper_color'] }}">{{ $svc->svcOperStatus }}</span></td>
                            <td>{{ $svc->svcDescription ?: '-' }}</td>
                            <td>{{ $svc->svcMtu }}</td>
                            <td>{{ $svc->svcNumSaps }}</td>
                            <td>{{ \LibreNMS\Util\Time::formatInterval($svc->svcLastMgmtChange) }}</td>
                            <td>{{ \LibreNMS\Util\Time::formatInterval($svc->svcLastStatusChange) }}</td>
                            <td>{{ $svc->vrf_name ?: '-' }}</td>
                            <td>{{ $svc->svcTlsMacLearning }}</td>
                            <td>{{ $svc->svcTlsFdbTableSize }}</td>
                            <td><span class="label label-{{ $item['fdb_color'] }}">{{ $svc->svcTlsFdbNumEntries }}</span></td>
                            <td>{{ $svc->svcTlsStpAdminStatus }}</td>
                            <td>{{ $svc->svcTlsStpOperStatus }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="16" class="text-center" style="padding: 20px;">
                                <em>{{ __('No MPLS services found for this device.') }}</em>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

            @elseif($data['view'] === 'saps')
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
                    @forelse($data['items'] as $item)
                        @php $sap = $item['sap']; @endphp
                        <tr>
                            <td class="tw:font-bold">{{ $sap->svc_oid }}</td>
                            <td>
                                @if($sap->port)
                                    <x-port-link :port="$sap->port" />
                                @else
                                    <span class="text-muted">{{ __('Port') }} #{{ $sap->port_id }}</span>
                                @endif
                            </td>
                            <td>{{ $sap->sapEncapValue }}</td>
                            <td>{{ $sap->sapType }}</td>
                            <td>{{ $sap->sapDescription ?: '-' }}</td>
                            <td><span class="label label-{{ $item['admin_color'] }}">{{ $sap->sapAdminStatus }}</span></td>
                            <td><span class="label label-{{ $item['oper_color'] }}">{{ $sap->sapOperStatus }}</span></td>
                            <td>{{ \LibreNMS\Util\Time::formatInterval($sap->sapLastMgmtChange) }}</td>
                            <td>{{ \LibreNMS\Util\Time::formatInterval($sap->sapLastStatusChange) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center" style="padding: 20px;">
                                <em>{{ __('No MPLS SAPs found for this device.') }}</em>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
