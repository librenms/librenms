@extends('layouts.librenmsv1')

@section('title', __('Device Dependency Map'))

@section('content')
<div class="container-fluid">

<div class="row">
<div class="col-md-12">
@if($group_name)
&nbsp;<big><b>{{ $group_name }}</b></big>
@endif
<div class="pull-right">
    Highlight Node
    <select name="highlight_node" id="highlight_node" class="input-sm" onChange="refreshMap()";>
        <option value="0">None</option>
        <option value="-1">Isolated Devices</option>
    </select>
</div>
</div>
</div>

<div class="row" id="alert-row">
<div class="col-md-12">
<div class="alert alert-warning" role="alert" id="alert">Loading data</div>
</div>
</div>

<div class="row" id="alert">
<div class="col-md-12">
<div id="visualization"></div>
</div>
</div>

</div>
@endsection

@section('javascript')
<script type="text/javascript" src="{{ asset('js/vis.min.js') }}"></script>
@endsection

@section('scripts')
<script type="text/javascript">
    var height = $(window).height() - 100;
    $('#visualization').height(height + 'px');

    var network_nodes = new vis.DataSet({queue: {delay: 100}});
    var network_edges = new vis.DataSet({queue: {delay: 100}});
    var network;

    function refreshMap() {
        var highlight = $("#highlight_node").val();
@if($group_id)
        var group = {{$group_id}};
@else
        var group = null;
@endif

        var hide_devices = new Map();
        $.ajax({
            type: 'POST',
            url: '{{ route('maps.getdevices') }}',
            data: {disabled: 0, disabled_alerts: null, url_type: "links", group: group, highlight_node: highlight},
            dataType: 'json',
            success: function (data) {
                function deviceSort(a,b) {
                    return (data[a]["sname"] > data[b]["sname"]) ? 1 : -1;
                }

                var keys = Object.keys(data).sort(deviceSort);
                $.each( keys, function( dev_idx, device_id ) {
                    var device = data[device_id];
                    var this_dev = {id: device_id, label: device["sname"], title: device["url"], shape: "box"}
                    if (device["style"]) {
                        // Merge the style if it has been defined
                        this_dev = Object.assign(device["style"], this_dev);
                    }
                    if (network_nodes.get(device_id)) {
                        network_nodes.update(this_dev);
                    } else {
                        network_nodes.add([this_dev]);
                        var highlight_option = document.createElement("option");
                        highlight_option.value = device_id;
                        highlight_option.id = "highlight-device-" + device_id;
                        highlight_option.textContent = device["sname"];
                        document.getElementById('highlight_node').appendChild(highlight_option);
                    }
                    // Hide the device until we find a link
                    hide_devices.set(device_id.toString(), null);
                })

                // Remove any nodes that have been removed
                $.each( network_nodes.getIds(), function( dev_idx, device_id ) {
                    if (!(device_id in data)) {
                        network_nodes.remove(device_id);
                        var option_id = "#highlight-device-" + device_id;
                        $(option_id).remove();
                    }
                });

                if (Object.keys(data).length == 0) {
                    $("#alert").html("No devices found");
                    $("#alert-row").show();
                } else if (Object.keys(data).length > 500) {
                    $("#alert").html("The initial render will be slow due to the number of devices.  Auto refresh has been paused.");
                    $("#alert-row").show();
                    Countdown.Pause();
                } else {
                    $("#alert").html("");
                    $("#alert-row").hide();
                }
            },
            async: false
        });

        $.ajax({
            type: 'POST',
            url: '{{ route('maps.getdevicelinks') }}',
            data: {disabled: 0, disabled_alerts: null, group: group, link_types: @json($link_types)},
            dataType: 'json',
            success: function (data) {
                $.each( data, function( link_id, link ) {
                    var this_edge = link['style'];
                    this_edge['from'] = link['ldev'];
                    this_edge['to'] = link['rdev'];
                    this_edge['label'] = link['ifnames'];
                    this_edge['title'] = link['url'];

                    if (!network_edges.get(link_id)) {
                        network_edges.add([this_edge]);
                    }
                    // Unhide any devices we find
                    hide_devices.delete(link['ldev'].toString());
                    hide_devices.delete(link['rdev'].toString());
                })

                // Remove any links that have disappeared
                $.each( network_edges.getIds(), function( link_idx, link_id ) {
                    if (!(link_id in data)) {
                        network_edges.remove(link_id);
                    }
                });
            },
            async: false
        });

        // Flush the nodes and edges (needed for the forEach() loop below)
        network_nodes.flush();
        network_edges.flush();

        // Hide and unhide nodes
        network_nodes.forEach(function (item) {
            oldval = item.hidden;
            item.hidden = hide_devices.has(item.id.toString());
            if (oldval != item.hidden) {
                network_nodes.update(item);
            }
        });

        // Initialise map if we haven't already.  If we do it earlier, the radom seeding doesn not work
        if (! network) {
            var container = document.getElementById('visualization');
            var options = {!! $options !!};
            network = new vis.Network(container, {nodes: network_nodes, edges: network_edges, stabilize: true}, options);

            network.on('click', function (properties) {
                if (properties.nodes > 0) {
                    let cur_highlighted = $('#highlight_node').val();
                    if (cur_highlighted == properties.nodes) {
                        $('#highlight_node').val(-1).trigger('change');
                    } else {
                        $('#highlight_node').val(properties.nodes).trigger('change');
                    }
                }
            });
            network.on('doubleClick', function (properties) {
                if (properties.nodes > 0) {
                    window.location.href = "device/device="+properties.nodes+"/"
                }
            });
        }
    }

    $(document).ready(async function () {
        await refreshMap();
        Countdown.Start();
    });</script>
<x-refresh-timer :refresh="$page_refresh" callback="refreshMap"></x-refresh-timer>
@endsection
