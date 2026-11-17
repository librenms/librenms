@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-option-bar name="Neighbours" :options="$selections" :selected="$selection"></x-option-bar>
@if($selection == 'list')
    <table class="table table-hover table-condensed" id="neighbour-table">
        <thead>
            <tr>
                <th>Local Port</th>
                <th>Remote Device</th>
                <th>Remote Port</th>
                <th>Protocol</th>
            </tr>
        </thead>
        <tbody>
@foreach($links as $link)
            <tr>
                <td>
                @if($link->port)
                    <x-port-link :port="$link->port" />
                    <br />{{$link->port->ifAlias}}
                @endif
                <td>
                @if($link->remoteDevice)
                    <x-device-link :device="$link->remoteDevice" />
                    <br />{{$link->remoteDevice->hardware}}
                @else
                    {{$link->remote_hostname}}
                    <br />{{$link->remote_platform}}
                @endif
                </td>
                <td>
                @if($link->remotePort)
                    <x-port-link :port="$link->remotePort" />
                    <br />{{$link->remotePort->ifAlias}}
                @else
                    {{$link->remote_port}}
                @endif
                </td>
                </td>
                <td>{{ strtoupper($link->protocol) }}</td>
            </tr>
@endforeach
        </tbody>
    </table>
@elseif($selection == 'map')
    <div id="netmap"></div>

@push('scripts')
@include('map.partials.vis-popups')
<script>
var network_nodes = new vis.DataSet({queue: {delay: 100}});
var network_edges = new vis.DataSet({queue: {delay: 100}});

$.post( '{{ route('maps.getdevicelinks') }}', {device: {{$device->device_id}}, link_types: @json($link_types)})
    .done(function( data ) {
        var devices = [];
        $.each(data, function( link_id, link ) {
            var this_edge = link['style'];
            this_edge['id'] = link_id;
            this_edge['from'] = link['ldev'];
            this_edge['to'] = link['rdev'];
            this_edge['label'] = link['ifnames'];

            network_edges.add(this_edge);
            devices[link['ldev']] = true;
            devices[link['rdev']] = true;
        });

        $.post( '{{ route('maps.getdevices') }}', {devices: Object.keys(devices)})
            .done(function( data ) {
                $.each(data, function( dev_id, dev ) {
                    var this_dev = {id: dev_id, label: dev["sname"], shape: "box"};
                    if (dev["style"]) {
                        // Merge the style if it has been defined
                        this_dev = Object.assign(dev["style"], this_dev);
                    }
                    network_nodes.add(this_dev);
                });

                network_nodes.flush();
            });
        network_edges.flush();
    });


var height = $(window).height() - 100;
$('#netmap').height(height + 'px');

// create a network
var container = document.getElementById('netmap');
var options = {{ Js::from($visoptions) }};
var data = {
    nodes: network_nodes,
    edges: network_edges,
    stabilize: true
};

var network = new vis.Network(container, data, options);
visPopups.attach(network, {ports: true});
network.on('click', function (properties) {
    if (properties.nodes > 0) {
       window.location.href = {{ \Illuminate\Support\Js::from(route('device.neighbours', ['device' => '__DEVICE__', 'selection' => 'map'])) }}.replace('__DEVICE__', properties.nodes);
    }
});
</script>
@endpush
@endif
</x-device.page>
@endsection

@section('javascript')
@if($selection == 'map')
    <script src="{{ url('js/vis-network.min.js') }}"></script>
    <script src="{{ url('js/vis-data.min.js') }}"></script>
@endif
@endsection
