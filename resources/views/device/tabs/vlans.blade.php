@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    @isset($data['submenu'])
        <x-submenu :title="$title" :menu="$data['submenu']" :device-id="$device_id" :current-tab="$current_tab" :selected="$vars" />
    @endisset

    {{-- VLAN Search Filters --}}
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-12">
            <form method="GET" action="{{ request()->url() }}" class="form-inline">
                @foreach(request()->except(['searchVlanNumber', 'searchVlanName', 'useRegex']) as $key => $value)
                    @if(is_string($value))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                
                <div class="form-group" style="margin-right: 10px;">
                    <input type="text" 
                           class="form-control" 
                           name="searchVlanNumber" 
                           value="{{ $data['searchVlanNumber'] ?? '' }}" 
                           placeholder="VLAN Number..." 
                           style="width: 150px;">
                </div>
                
                <div class="form-group" style="margin-right: 10px;">
                    <input type="text" 
                           class="form-control" 
                           name="searchVlanName" 
                           value="{{ $data['searchVlanName'] ?? '' }}" 
                           placeholder="VLAN Name..." 
                           style="width: 200px;">
                </div>
                
                <div class="form-group" style="margin-right: 10px;">
                    <label style="font-weight: normal; margin-bottom: 0;">
                        <input type="checkbox" 
                               name="useRegex" 
                               value="1" 
                               {{ $data['useRegex'] ? 'checked' : '' }}>
                        Use Regex
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-search"></i> Filter
                </button>
                
                @if($data['searchVlanNumber'] || $data['searchVlanName'])
                    <a href="{{ request()->url() }}" class="btn btn-default" style="margin-left: 5px;">
                        <i class="fa fa-times"></i> Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Regex Mode Indicator --}}
    @if($data['useRegex'] && ($data['searchVlanNumber'] || $data['searchVlanName']))
        <div class="alert alert-info" style="margin-bottom: 15px;">
            <i class="fa fa-info-circle"></i> 
            <strong>Regex Mode Active:</strong>
            @if($data['searchVlanNumber'])
                VLAN Number pattern: <code>{{ $data['searchVlanNumber'] }}</code>
            @endif
            @if($data['searchVlanName'])
                @if($data['searchVlanNumber']) | @endif
                VLAN Name pattern: <code>{{ $data['searchVlanName'] }}</code>
            @endif
        </div>
    @endif

    {{-- Error Message --}}
    @if($data['error'])
        <div class="alert alert-danger">
            <i class="fa fa-exclamation-triangle"></i> 
            <strong>Error:</strong> {{ $data['error'] }}
        </div>
    @endif

    {{-- VLANs Table --}}
    <table class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th style="width: 150px;">{{ __('VLAN Number') }}</th>
                <th style="width: 250px;">{{ __('VLAN Name') }}</th>
                <th>{{ __('Ports') }}</th>
            </tr>
        </thead>
        <tbody>

        @forelse($data['vlans'] as $vlan_number => $vlans)
            <tr>
                <td>{{ $vlan_number }}</td>
                <td>{{ $vlans->first()->vlan_name }}</td>
                <td>
                @foreach($vlans as $port)
                    @if(!$port->port)
                        @continue
                    @endif

                    @if(!$vars)
                        <span class="tw:inline-flex">
                            <x-port-link :port="$port->port">{{ $port->port->getShortLabel() }}</x-port-link>
                            @if($port->untagged)<span>&nbsp;(U)</span>@endif
                            @if(!$loop->last)<span>,</span>@endif
                        </span>
                    @else
                        <div style="display: block; padding: 2px; margin: 2px; min-width: 139px; max-width:139px; min-height:85px; max-height:85px; text-align: center; float: left; background-color: {{ \App\Facades\LibrenmsConfig::get('list_colour.odd_alt2') }}">
                            <div style="font-weight: bold;">{{ $port->port->ifDescr }}</div>
                            <a href="{{ route('device', ['device' => $device->device_id, 'tab' => 'port', 'vars' => 'port='.$port->port->port_id]) }}" onmouseover="return overlib('<div style=\'font-size: 16px; padding:5px; font-weight: bold; color: #e5e5e5;\'>{{ $device->hostname }}-{{ $port->port->ifDescr }}</div>{{ $port->port->ifAlias }}<img src=\'{{ url('graph.php') }}?type=port_{{ $vars }}&amp;id={{ $port->port->port_id }}&amp;from={{ \App\Facades\LibrenmsConfig::get('time.twoday') }}&amp;to={{ \App\Facades\LibrenmsConfig::get('time.now') }}&amp;width=450&amp;height=150\'>', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);" onmouseout="return nd();">
                                <img src="{{ url('graph.php') }}?type=port_{{ $vars }}&amp;id={{ $port->port->port_id }}&amp;from={{ \App\Facades\LibrenmsConfig::get('time.twoday') }}&amp;to={{ \App\Facades\LibrenmsConfig::get('time.now') }}&amp;width=132&amp;height=40&amp;legend=no">
                            </a>
                            <div style="font-size: 9px;">{{ $port->port->ifAlias }}</div>
                        </div>
                    @endif
                @endforeach
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center" style="padding: 20px;">
                    <em>
                        @if($data['searchVlanNumber'] || $data['searchVlanName'])
                            No VLANs match your filter criteria.
                        @else
                            No VLANs found for this device.
                        @endif
                    </em>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
    
    {{-- Results Count --}}
    @if(($data['searchVlanNumber'] || $data['searchVlanName']) && $data['matchedCount'] > 0)
        <div class="alert alert-success">
            <i class="fa fa-check-circle"></i> 
            Showing {{ $data['matchedCount'] }} VLAN{{ $data['matchedCount'] !== 1 ? 's' : '' }} matching your criteria
        </div>
    @endif
</x-device.page>
@endsection



