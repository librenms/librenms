@extends('device.index')

@section('tab')
    <x-option-bar name="Neighbours" :options="$data['selections']" :selected="$data['selection']"></x-option-bar>
@if($data['selection'] == 'list')
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
@foreach($data['links'] as $link)
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
@elseif($data['selection'] == 'map')
    <div id="netmap"></div>

@push('scripts')
<script>
var network_nodes = new vis.DataSet({queue: {delay: 100}});
var network_edges = new vis.DataSet({queue: {delay: 100}});

$.post( '{{ route('maps.getdevicelinks') }}', {device: {{$device->device_id}}, link_types: @json($data['link_types'])})
    .done(function( data ) {
        var devices = [];
        $.each(data, function( link_id, link ) {
            var this_edge = link['style'];
            this_edge['from'] = link['ldev'];
            this_edge['to'] = link['rdev'];
            this_edge['label'] = link['ifnames'];

            network_edges.add(this_edge);
            devices[link['ldev']] = true;
            devices[link['rdev']] = true;
        });

        $.post( '{{ route('maps.getdevices') }}', {devices: Object.keys(devices), url_type: 'links'})
            .done(function( data ) {
                $.each(data, function( dev_id, dev ) {
                    let title = document.createElement("div");
                    title.innerHTML = dev["url"];

                    var this_dev = {id: dev_id, label: dev["sname"], title: title, shape: "box"};
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
var options = {!! $data['visoptions'] !!};
var data = {
    nodes: network_nodes,
    edges: network_edges,
    stabilize: true
};

var network = new vis.Network(container, data, options);
network.on('click', function (properties) {
    if (properties.nodes > 0) {
       window.location.href = "{{ @url('device') }}/device="+properties.nodes+"/tab=neighbours/selection=map/"
    }
});
</script>
@endpush
@endif
@endsection

@section('javascript')
@if($data['selection'] == 'map')
    <script src="{{ url('js/vis-network.min.js') }}"></script>
    <script src="{{ url('js/vis-data.min.js') }}"></script>
@endif
@endsection
