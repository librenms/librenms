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
            @forelse($data['instances'] as $index => $instance)
                <tbody>
                    <tr data-toggle="collapse" data-target="#ospfv3-panel-{{ $index }}" class="accordion-toggle tw:cursor-pointer">
                        <td>
                            <button id="ospfv3-panel_button-{{ $index }}" class="btn btn-default btn-xs">
                                <i id="ospfv3-panel_span-{{ $index }}" class="fa fa-plus" aria-hidden="true"></i>
                            </button>
                        </td>
                        <td class="tw:font-bold">{{ $instance['router_id'] }}</td>
                        <td><span class="label label-{{ $instance['status_color'] }}">{{ $instance['admin_status'] }}</span></td>
                        <td><span class="label label-{{ $instance['abr_color'] }}">{{ $instance['abr_status'] }}</span></td>
                        <td><span class="label label-{{ $instance['asbr_color'] }}">{{ $instance['asbr_status'] }}</span></td>
                        <td>{{ $instance['area_count'] }}</td>
                        <td>{{ $instance['port_count'] }} ({{ $instance['port_count_enabled'] }})</td>
                        <td>{{ $instance['nbr_count'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="8" class="tw:p-0 tw:border-none">
                            <div class="collapse tw:p-4" id="ospfv3-panel-{{ $index }}">
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
                                            @forelse($instance['areas'] as $area)
                                                <tr>
                                                    <td>{{ $area['area_id_ip'] }}</td>
                                                    <td>{{ $area['port_count'] }} ({{ $area['port_count_enabled'] }})</td>
                                                    <td>{{ $area['lsa_count'] }}</td>
                                                    <td><span class="label label-{{ $area['status_color'] }}">{{ $area['status'] }}</span></td>
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
                                            @forelse($instance['ports'] as $ospfPort)
                                                <tr>
                                                    <td>
                                                        @if($ospfPort['port'])
                                                            <x-port-link :port="$ospfPort['port']" />
                                                        @else
                                                            <span class="text-muted">{{ __('Port') }} #{{ $ospfPort['port_id'] }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $ospfPort['type'] }}</td>
                                                    <td>{{ $ospfPort['state'] }}</td>
                                                    <td>{{ $ospfPort['cost'] }}</td>
                                                    <td>{{ $ospfPort['area_id_ip'] }}</td>
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
                                            @forelse($instance['nbrs'] as $nbr)
                                                <tr>
                                                    <td>{{ $nbr['router_id'] }}</td>
                                                    <td>
                                                        @if($nbr['device_id'])
                                                            <x-device-link :device="$nbr['device_id']" tab="routing" proto="ospfv3" />
                                                        @else
                                                            <span class="text-muted">{{ __('Unknown') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $nbr['address'] }}</td>
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
                        <td colspan="8" class="text-center tw:p-5">
                            <em>{{ __('No OSPFv3 instances found for this device.') }}</em>
                        </td>
                    </tr>
                </tbody>
            @endforelse
        </table>
    </div>
</x-panel>

