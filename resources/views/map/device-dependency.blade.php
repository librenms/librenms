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
    <input type="checkbox" class="custom-control-input" id="showparentdevicepath" onChange="refreshMap()">
    <label class="custom-control-label" for="showparentdevicepath">{{ __('Highlight Dependencies to Root Device') }}</label>
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

    var Countdown;

    function refreshMap() {
        var highlight = $("#highlight_node").val();
        var showpath = $("#showparentdevicepath")[0].checked ? 1 : 0;
@if($group_id)
        var group = {{$group_id}};
@else
        var group = null;
@endif

        $.post( '{{ route('maps.getdevices') }}', {disabled: 0, disabled_alerts: 0, link_type: "depends", url_type: "links", group: group, highlight_node: highlight, showpath: showpath})
            .done(function( data ) {
                function deviceSort(a,b) {
                    return (data[a]["sname"] > data[b]["sname"]) ? 1 : -1;
                }

                var keys = Object.keys(data).sort(deviceSort);
                $.each( keys, function( dev_idx, device_id ) {
                    var device = data[device_id];
                    var this_dev = {id: device_id, label: device["sname"], title: device["url"], shape: "box", level: device["level"]}
                    if (device["style"]) {
                        // Merge the style if it has been defined
                        this_dev = Object.assign(device["style"], this_dev);
                    }
                    if (network_nodes.get(device_id)) {
                        network_nodes.update(this_dev);
                    } else {
                        network_nodes.add([this_dev]);
                        $("#highlight_node").append("<option value='" + device_id + "' id='highlight-device-" + device_id + "'>" + device["sname"] + "</option>")
                    }
                    $.each( device["parents"], function( parent_idx, parent_id ) {
                        link_id = device_id + "." + parent_id;
                        if (!network_edges.get(link_id)) {
                            network_edges.add([{from: device_id, to: parent_id, width: 2}]);
                        }
                    })
                })

                // Initialise map if we haven't already.  If we do it earlier, the radom seeding doesn not work
                if (! network) {
                    // Flush the nodes and edges so they are rendered immediately
                    network_nodes.flush();
                    network_edges.flush();

                    var container = document.getElementById('visualization');
                    var options = {!! $options !!};
                    network = new vis.Network(container, {nodes: network_nodes, edges: network_edges, stabilize: true}, options);

                    network.on('click', function (properties) {
                        if (properties.nodes > 0) {
                            window.location.href = "device/device="+properties.nodes+"/"
                        }
                    });
                } else {
                    $.each( network_nodes.getIds(), function( dev_idx, device_id ) {
                        if (!(device_id in data)) {
                            network_nodes.remove(device_id);
                            var option_id = "#highlight-device-" + device_id;
                            $(option_id).remove();
                        }
                    });
                }

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
            });
    }

    $(document).ready(function () {
        Countdown = {
            sec: {{$page_refresh}},

            Start: function () {
                var cur = this;
                this.interval = setInterval(function () {
                    cur.sec -= 1;
                    if (cur.sec <= 0) {
                        refreshMap();
                        cur.sec = {{$page_refresh}};
                    }
                }, 1000);
            },

            Pause: function () {
                clearInterval(this.interval);
                delete this.interval;
            },
        };

        Countdown.Start();

        refreshMap();
    });
</script>
@endsection

