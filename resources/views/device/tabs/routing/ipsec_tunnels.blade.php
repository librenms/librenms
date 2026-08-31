<x-option-bar name="{{ __('IPSEC Tunnels') }}" :options="$data['ipsec_options']" :selected="$data['selected_option']" />

@if($data['view'] === 'graphs')
    @forelse($data['tunnels'] as $entry)
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{{ $entry['local_addr'] }} » {{ $entry['peer_addr'] }}</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <x-graph-row :type="'ipsectunnel_' . $data['graph']" :vars="['id' => $entry['id']]" />
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info">{{ __('No IPsec tunnels found for this device.') }}</div>
    @endforelse
@else
    <div class="panel panel-default">
        <div class="panel-body">
            <table class="table table-condensed table-hover">
                <thead>
                    <tr>
                        <th>{{ __('Local Identity') }}</th>
                        <th>{{ __('Remote Identity') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($data['tunnels'] as $entry)
                    <tr>
                        <td>{{ $entry['local_addr'] }}</td>
                        <td>{{ $entry['peer_addr'] }}</td>
                        <td>{{ $entry['tunnel_name'] }}</td>
                        <td>
                            <span class="label label-{{ $entry['status_label'] }}">{{ $entry['tunnel_status'] }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 20px;">
                            <em>{{ __('No IPsec tunnels found for this device.') }}</em>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
