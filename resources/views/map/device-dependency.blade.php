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
    <input type="checkbox" class="custom-control-input" id="showparentdevicepath" onChange="updateHighlight(this)">
    <label class="custom-control-label" for="showparentdevicepath">{{ __('Highlight Dependencies to Root Device') }}</label>
    <input type="checkbox" class="custom-control-input" id="showchilddevicepath" onChange="updateHighlight(this)">
    <label class="custom-control-label" for="showchilddevicepath">{{ __('Highlight All Child Devices') }}</label>
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

    function updateHighlight(hlcb) {
        if (hlcb.id == 'showchilddevicepath') {
            $("#showparentdevicepath").prop( "checked", false );
        } else if (hlcb.id == 'showparentdevicepath') {
            $("#showchilddevicepath").prop( "checked", false );
        }
        refreshMap();
    }

    function async refreshMap() {
        var highlight = $("#highlight_node").val();
        var showpath = 0;
        if ($("#showparentdevicepath")[0].checked) {
            showpath = 1;
        } else if ($("#showchilddevicepath")[0].checked) {
            showpath = -1;
        }
@if($group_id)
        var group = {{ $group_id }};
@else
        var group = null;
@endif

        await $.post( '{{ route('maps.getdevices') }}', {disabled: 0, disabled_alerts: null, link_type: "depends", url_type: "links", group: group, highlight_node: highlight, showpath: showpath})
            .done(function( data ) {
                let device_count = Object.keys(data).length;
                if (device_count === 0) {
                    $("#alert").text("No devices found");
                    $("#alert-row").show();
                } else if (device_count > 500) {
                    $("#alert").text("The initial render will be slow due to the number of devices.  Auto refresh has been paused.");
                    $("#alert-row").show();
                } else {
                    $("#alert").text("");
                    $("#alert-row").hide();
                }

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
                        var highlight_option = document.createElement("option");
                        highlight_option.value = device_id;
                        highlight_option.id = "highlight-device-" + device_id;
                        highlight_option.textContent = device["sname"];
                        document.getElementById("highlight_node").appendChild(highlight_option);
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
                            let cur_highlighted = $('#highlight_node').val();
                            if (cur_highlighted == properties.nodes) {
                                $('#highlight_node').val(0).trigger('change');
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
                } else {
                    $.each( network_nodes.getIds(), function( dev_idx, device_id ) {
                        if (!(device_id in data)) {
                            network_nodes.remove(device_id);
                            var option_id = "#highlight-device-" + device_id;
                            $(option_id).remove();
                        }
                    });
                }

                $("#alert").text("");
                $("#alert-row").hide();
            });
    }

    // initial load pause countdown in case load is long
    $(document).ready(async function () {
        Countdown.Pause();
        await refreshMap();
        Countdown.Resume();
    });
</script>
<x-refresh-timer :refresh="$page_refresh" callback="refreshMap"></x-refresh-timer>
@endsection
