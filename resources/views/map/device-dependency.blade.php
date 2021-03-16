@extends('layouts.librenmsv1')

@section('title', __('Device Dependency Map'))

@section('content')

@if($node_count)
&nbsp;<big><b>{{ $group_name }}</b></big>
<div class="pull-right">
<input type="checkbox" class="custom-control-input" id="showparentdevicepath" onChange="highlightNode()" @if($showparentdevicepath) checked @endif>
<label class="custom-control-label" for="showparentdevicepath">@lang('Highlight Dependencies to Root Device')</label>
<select name="highlight_node" id="highlight_node" class="input-sm" onChange="highlightNode()";>
<option value="0">None</option>
<option value="{{ $isolated_device_id }}">@lang('Isolated Devices')</option>
@foreach($device_list as $device)
<option value="{{ $device['id'] }}">{{ $device['label'] }}</option>
@endforeach
</select>
</div>
<div id="visualization"></div>
@else
<div class="alert alert-success" role="alert">@lang('No devices found')</div>
@endif

@endsection

@section('javascript')
<script type="text/javascript" src="{{ asset('js/vis.min.js') }}"></script>
@endsection

@section('scripts')
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

    function highlightNode(e) {
        highlight_node = document.getElementById("highlight_node").value;
        showparentdevicepath = document.getElementById("showparentdevicepath").checked ? 1: 0;
        window.location.href = 'maps/devicedependency?group={{ $group_id }}&highlight_node=' + highlight_node + '&showparentdevicepath=' + showparentdevicepath;
    }

    $('#highlight_node option[value="{{$highlight_node}}"]').prop('selected', true);
</script>
@endsection

