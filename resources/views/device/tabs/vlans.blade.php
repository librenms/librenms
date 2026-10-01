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
                @foreach(request()->except(['searchVlanNumber', 'searchVlanName']) as $key => $value)
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
                            {!! \LibreNMS\Util\Url::modernPortLink($port->port, new \Illuminate\Support\HtmlString('<img src="' . e(route('graph', ['type' => 'port_' . $vars, 'id' => $port->port->port_id, 'from' => '-2d', 'width' => 132, 'height' => 40, 'legend' => 'no'])) . '">')) !!}
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
    @if(($data['searchVlanNumber'] || $data['searchVlanName']) && count($data['vlans']) > 0)
        <div class="alert alert-success">
            <i class="fa fa-check-circle"></i>
            Showing {{ count($data['vlans']) }} VLAN{{ count($data['vlans']) !== 1 ? 's' : '' }} matching your criteria
        </div>
    @endif
</x-device.page>
@endsection



