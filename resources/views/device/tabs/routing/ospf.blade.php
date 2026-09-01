<x-panel>
    <div class="table-responsive">
        <table class="table table-condensed tw:border-collapse">
            <thead>
                <tr>
                    <th class="tw:w-10">&nbsp;</th>
                    <th>{{ __('Router ID') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('ABR') }}</th>
                    <th>{{ __('ASBR') }}</th>
                    <th>{{ __('Areas') }}</th>
                    <th>{{ __('Ports (Enabled)') }}</th>
                    <th>{{ __('Neighbours') }}</th>
                </tr>
            </thead>
            @forelse($data['instances'] as $index => $inst)
                <tbody>
                    <tr data-toggle="collapse" data-target="#ospf-panel-{{ $index }}" class="accordion-toggle tw:cursor-pointer">
                        <td>
                            <button id="ospf-panel_button-{{ $index }}" class="btn btn-default btn-xs">
                                <i id="ospf-panel_span-{{ $index }}" class="fa fa-plus" aria-hidden="true"></i>
                            </button>
                        </td>
                        <td class="tw:font-bold">{{ $inst['router_id'] }}</td>
                        <td><span class="label label-{{ $inst['status_color'] }}">{{ $inst['admin_stat'] }}</span></td>
                        <td><span class="label label-{{ $inst['abr_status_color'] }}">{{ $inst['abr_status'] }}</span></td>
                        <td><span class="label label-{{ $inst['asbr_status_color'] }}">{{ $inst['asbr_status'] }}</span></td>
                        <td>{{ $inst['area_count'] }}</td>
                        <td>{{ $inst['port_count'] }} ({{ $inst['port_count_enabled'] }})</td>
                        <td>{{ $inst['nbr_count'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="8" class="tw:p-0 tw:border-none">
                            <div class="collapse tw:p-4" id="ospf-panel-{{ $index }}">
                                <div class="row">
                                    <div class="col-xs-12 col-md-4">
                                        <h4><span class="label label-primary">{{ __('Areas') }}</span></h4>
                                        <table class="table table-striped table-hover table-condensed">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Area ID') }}</th>
                                                    <th>{{ __('Ports (Enabled)') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($inst['areas'] as $area)
                                                <tr>
                                                    <td>{{ $area['area_id'] }}</td>
                                                    <td>{{ $area['port_count'] }} ({{ $area['port_count_enabled'] }})</td>
                                                    <td><span class="label label-{{ $inst['status_color'] }}">{{ $area['status'] }}</span></td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="text-muted"><em>{{ __('No areas configured.') }}</em></td></tr>
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
                                            @forelse($inst['ports'] as $ospfPort)
                                                <tr>
                                                    <td>
                                                        @if($ospfPort->port)
                                                            <x-port-link :port="$ospfPort->port" />
                                                        @else
                                                            {{ $ospfPort->ospfIfIpAddress }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $ospfPort->ospfIfType }}</td>
                                                    <td>{{ $ospfPort->ospfIfState }}</td>
                                                    <td>{{ $ospfPort->ospfIfMetricValue }}</td>
                                                    <td>{{ $ospfPort->ospfIfAreaId }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-muted"><em>{{ __('No enabled ports.') }}</em></td></tr>
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
                                            @forelse($inst['nbrs'] as $nbr)
                                                <tr>
                                                    <td>{{ $nbr['router_id'] }}</td>
                                                    <td>
                                                        @if($nbr['device'])
                                                            <x-device-link :device="$nbr['device']" tab="routing" proto="ospf" />
                                                        @else
                                                            <span class="text-muted">{{ __('Unknown') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $nbr['ip_address'] }}</td>
                                                    <td><span class="label label-{{ $nbr['status_color'] }}">{{ $nbr['state'] }}</span></td>
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
                        <td colspan="8" class="tw:text-center tw:p-5">
                            <em>{{ __('No OSPF instances found for this device.') }}</em>
                        </td>
                    </tr>
                </tbody>
            @endforelse
        </table>
    </div>
</x-panel>

