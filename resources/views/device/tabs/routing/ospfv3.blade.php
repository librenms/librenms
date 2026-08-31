<div class="panel panel-default">
    <div class="panel-body">
        <table class="table table-condensed" style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 40px;">&nbsp;</th>
                    <th>{{ __('Router ID') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('ABR') }}</th>
                    <th>{{ __('ASBR') }}</th>
                    <th>{{ __('Areas') }}</th>
                    <th>{{ __('Ports (Enabled)') }}</th>
                    <th>{{ __('Neighbours') }}</th>
                </tr>
            </thead>
            @forelse($data['instances'] as $index => $instance)
                @php
                    $portCountEnabled = $instance->ospfv3Ports->where('ospfv3IfAdminStatus', 'enabled')->count();
                    $statusColor = $instance->ospfv3AdminStatus === 'enabled' ? 'success' : 'default';
                    $abrColor = $instance->ospfv3AreaBdrRtrStatus === 'true' ? 'success' : 'default';
                    $asbrColor = $instance->ospfv3ASBdrRtrStatus === 'true' ? 'success' : 'default';
                @endphp
                <tbody>
                    <tr data-toggle="collapse" data-target="#ospfv3-panel-{{ $index }}" class="accordion-toggle" style="cursor: pointer;">
                        <td>
                            <button id="ospfv3-panel_button-{{ $index }}" class="btn btn-default btn-xs">
                                <i id="ospfv3-panel_span-{{ $index }}" class="fa fa-plus" aria-hidden="true"></i>
                            </button>
                        </td>
                        <td class="tw:font-bold">{{ $instance->router_id }}</td>
                        <td><span class="label label-{{ $statusColor }}">{{ $instance->ospfv3AdminStatus }}</span></td>
                        <td><span class="label label-{{ $abrColor }}">{{ $instance->ospfv3AreaBdrRtrStatus }}</span></td>
                        <td><span class="label label-{{ $asbrColor }}">{{ $instance->ospfv3ASBdrRtrStatus }}</span></td>
                        <td>{{ $instance->areas->count() }}</td>
                        <td>{{ $instance->ospfv3Ports->count() }} ({{ $portCountEnabled }})</td>
                        <td>{{ $instance->nbrs->count() }}</td>
                    </tr>
                    <tr>
                        <td colspan="8" style="padding: 0; border: none;">
                            <div class="collapse" id="ospfv3-panel-{{ $index }}" style="padding: 15px;">
                                <div class="row">
                                    <div class="col-xs-12 col-md-4">
                                        <h4><span class="label label-primary">{{ __('Areas') }}</span></h4>
                                        <table class="table table-striped table-hover table-condensed">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Area ID') }}</th>
                                                    <th>{{ __('Ports (Enabled)') }}</th>
                                                    <th>{{ __('LSAs') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($instance->areas as $area)
                                                @php
                                                    $areaPortCount = $area->ospfv3Ports->count();
                                                    $areaPortCountEnabled = $area->ospfv3Ports->where('ospfv3IfAdminStatus', 'enabled')->count();
                                                @endphp
                                                <tr>
                                                    <td>{{ long2ip($area->ospfv3AreaId) }}</td>
                                                    <td>{{ $areaPortCount }} ({{ $areaPortCountEnabled }})</td>
                                                    <td>{{ $area->ospfv3AreaScopeLsaCount }}</td>
                                                    <td><span class="label label-{{ $statusColor }}">{{ $instance->ospfv3AdminStatus }}</span></td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-muted"><em>{{ __('No areas configured.') }}</em></td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="col-xs-12 col-md-4">
                                        <h4><span class="label label-primary">{{ __('Ports') }}</span></h4>
                                        <table class="table table-striped table-hover table-condensed">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Port') }}</th>
                                                    <th>{{ __('Type') }}</th>
                                                    <th>{{ __('State') }}</th>
                                                    <th>{{ __('Cost') }}</th>
                                                    <th>{{ __('Area ID') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($instance->ospfv3Ports as $ospfPort)
                                                <tr>
                                                    <td>
                                                        @if($ospfPort->port)
                                                            <x-port-link :port="$ospfPort->port" />
                                                        @else
                                                            <span class="text-muted">{{ __('Port') }} #{{ $ospfPort->port_id }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $ospfPort->ospfv3IfType }}</td>
                                                    <td>{{ $ospfPort->ospfv3IfState }}</td>
                                                    <td>{{ $ospfPort->ospfv3IfMetricValue }}</td>
                                                    <td>{{ long2ip($ospfPort->ospfv3IfAreaId) }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-muted"><em>{{ __('No ports configured.') }}</em></td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="col-xs-12 col-md-4">
                                        <h4><span class="label label-primary">{{ __('Neighbours') }}</span></h4>
                                        <table class="table table-striped table-hover table-condensed">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Router ID') }}</th>
                                                    <th>{{ __('Device') }}</th>
                                                    <th>{{ __('IP Address') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($instance->nbrs as $nbr)
                                                @php
                                                    $nbrStatusColor = match ($nbr->ospfv3NbrState) {
                                                        'full' => 'success',
                                                        'down' => 'danger',
                                                        default => 'default',
                                                    };
                                                @endphp
                                                <tr>
                                                    <td>{{ $nbr->router_id }}</td>
                                                    <td>
                                                        @if($nbr->port)
                                                            <x-device-link :device="$nbr->port->device_id" tab="routing" vars="proto=ospfv3" />
                                                        @else
                                                            <span class="text-muted">{{ __('Unknown') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $nbr->ospfv3NbrAddress }}</td>
                                                    <td><span class="label label-{{ $nbrStatusColor }}">{{ $nbr->ospfv3NbrState }}</span></td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-muted"><em>{{ __('No neighbours.') }}</em></td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            @empty
                <tbody>
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 20px;">
                            <em>{{ __('No OSPFv3 instances found for this device.') }}</em>
                        </td>
                    </tr>
                </tbody>
            @endforelse
        </table>
    </div>
</div>
