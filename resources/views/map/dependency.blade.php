@extends('layouts.librenmsv1')

@section('title', __('Dependency Map'))

@section('content')

@if($node_count)
<div id="visualization"></div>
@else
<div class="alert alert-success" role="alert">@lang('No devices found')</div>
@endif

@endsection

@section('scripts')
<script src="js/vis.min.js"></script>
<script type="text/javascript">
var height = $(window).height() - 100;
$('#visualization').height(height + 'px');
    // create an array with nodes
    var nodes = {!! $nodes !!};

    // create an array with edges
    var edges = {!! $edges !!};
    // create a network
    var container = document.getElementById('visualization');
    var data = {
        nodes: nodes,
        edges: edges,
        stabilize: true
    };
    var options = {!! $options !!};
    var network = new vis.Network(container, data, options);
    network.on('click', function (properties) {
        if (properties.nodes > 0) {
            window.location.href = "device/device="+properties.nodes+"/"
        }
    });
</script>
@endsection
