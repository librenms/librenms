<x-option-bar name="{{ __('VRFs') }}" :options="$data['vrf_options']" :selected="$data['selected_option']" />

<div class="panel panel-default">
    <div class="panel-body">
        <table class="table table-condensed table-hover" style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 200px;">{{ __('VRF') }}</th>
                    <th style="width: 150px;">{{ __('Description') }}</th>
                    <th style="width: 100px;">{{ __('RD') }}</th>
                    <th>{{ __('Interfaces') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse($data['vrfs'] as $vrf)
                <tr>
                    <td class="tw:font-bold">
                        {{ $vrf->vrf_name }}
                    </td>
                    <td>{{ $vrf->mplsVpnVrfDescription }}</td>
                    <td>{{ $vrf->mplsVpnVrfRouteDistinguisher }}</td>
                    <td>
                        @if($data['view'] === 'graphs')
                            <div class="tw:flex tw:flex-wrap tw:gap-1">
                                @foreach($vrf->ports as $port)
                                    <div style="display: block; padding: 2px; margin: 2px; min-width: 139px; max-width: 139px; min-height: 85px; max-height: 85px; text-align: center; float: left; background-color: #e9e9e9;">
                                        <div style="font-weight: bold;">{{ $port->getShortLabel() }}</div>
                                        <x-port-link :port="$port">
                                            <x-graph :port="$port" :type="'port_' . $data['graph']" from="-2d" width="132" height="40" legend="no" />
                                        </x-port-link>
                                        <div style="font-size: 9px;">{{ $port->ifAlias }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            @foreach($vrf->ports as $port)
                                <x-port-link :port="$port">{{ $port->getShortLabel() }}</x-port-link>@if(! $loop->last), @endif
                            @endforeach
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 20px;">
                        <em>{{ __('No VRFs found for this device.') }}</em>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
