<div class="panel panel-default">
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-condensed table-hover table-striped">
                <thead>
                    <tr>
                        <th>{{ __('Local Interface') }}</th>
                        <th>{{ __('Adjacent IP') }}</th>
                        <th>{{ __('System ID') }}</th>
                        <th>{{ __('Area') }}</th>
                        <th>{{ __('System Type') }}</th>
                        <th>{{ __('Admin') }}</th>
                        <th>{{ __('State') }}</th>
                        <th>{{ __('Last Uptime') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($data['adjacencies'] as $adj)
                    @php
                        $stateColor = $adj->isisISAdjState === 'up' ? 'success' : 'danger';
                    @endphp
                    <tr>
                        <td>
                            @if($adj->port)
                                <x-port-link :port="$adj->port" />
                            @else
                                <span class="text-muted">{{ __('Port') }} #{{ $adj->port_id }}</span>
                            @endif
                        </td>
                        <td>{{ $adj->isisISAdjIPAddrAddress }}</td>
                        <td>{{ $adj->isisISAdjNeighSysID }}</td>
                        <td>{{ $adj->isisISAdjAreaAddress }}</td>
                        <td>{{ $adj->isisISAdjNeighSysType }}</td>
                        <td>{{ $adj->isisCircAdminState }}</td>
                        <td><span class="label label-{{ $stateColor }}">{{ $adj->isisISAdjState }}</span></td>
                        <td>{{ \LibreNMS\Util\Time::formatInterval($adj->isisISAdjLastUpTime) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 20px;">
                            <em>{{ __('No IS-IS adjacencies found for this device.') }}</em>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
