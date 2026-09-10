@extends('layouts.librenmsv1')

@section('title', __('Interface Group'))

@section('content')
<div class="container-fluid">
    @if($ports->count() == 0)
    <div class="row">
        <div class=col-md-12><span class=list-large>{{ __("No ports in port group") }} {{ $group->name }}</span></div>
    </div>
    @else
    <x-graph-row type="multiport_bits_separate" :vars="['id' => $ports->pluck('port_id')->implode(',')]" columns="responsive">
        <x-slot name="title">
            <div class="row">
                <div class=col-md-12><span class=list-large>{{ __("Total Graph for port group") }} {{ $group->name }}</span></div>
            </div>
        </x-slot>
    </x-graph-row>
    @endif
    @foreach($ports as $port)
        <x-graph-row type="port_bits" :port=$port columns="responsive" legend=true>
            <x-slot name="title">
                <div class="row">
                    <div class=col-md-3><x-port-link :port="$port" :basic=true><span class=list-large>{{ $port->port_descr_descr }}</span></x-port-link></div>
                    <div class=col-md-3>{{ $port->port_descr_speed }}</div>
                    <div class=col-md-3>{{ $port->port_descr_circuit }}</div>
                    <div class=col-md-3>{{ $port->port_descr_notes }}</div>
                </div>
                <div class="row">
                    <div class=col-md-12><x-device-link :device="$port->device" /> - <x-port-link :port="$port" :basic=true /></div>
                </div>
                @if($port->macAccounting_count > 0)
                    <div class="row">
                        <div class=col-md-12><x-port-link :port="$port" :vars="['view' => 'macaccounting']" :basic=true><i class='fa fa-pie-chart fa-lg icon-theme' aria-hidden='true'></i> {{ __("MAC Accounting") }}</x-port-link></div>
                    </div>
                @endif
            </x-slot>
        </x-graph-row>
    @endforeach
</div>
@endsection
