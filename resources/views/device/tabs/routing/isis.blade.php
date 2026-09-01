<x-panel>
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
                <tr>
                    <td>
                        @if($adj['port'])
                            <x-port-link :port="$adj['port']" />
                        @else
                            <span class="text-muted">{{ __('Port') }} #{{ $adj['port_id'] }}</span>
                        @endif
                    </td>
                    <td>{{ $adj['ip_address'] }}</td>
                    <td>{{ $adj['neighbour_sys_id'] }}</td>
                    <td>{{ $adj['area_address'] }}</td>
                    <td>{{ $adj['neighbour_sys_type'] }}</td>
                    <td>{{ $adj['admin_state'] }}</td>
                    <td><span class="label label-{{ $adj['state_color'] }}">{{ $adj['state'] }}</span></td>
                    <td>{{ $adj['last_uptime'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="tw:text-center tw:p-5">
                        <em>{{ __('No IS-IS adjacencies found for this device.') }}</em>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-panel>

