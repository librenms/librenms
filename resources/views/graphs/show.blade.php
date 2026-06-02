@extends('layouts.librenmsv1')

@section('title', strip_tags($title))

@section('content')
@php
/** @var array $vars */

// Helper: build a /graphs/key=val/... URL, merging $changes into $vars (null removes a key).
$graphUrl = fn(array $changes = []): string => url('graphs') . '/' . http_build_query(
    array_filter(array_merge($vars, $changes), fn ($v) => $v !== null),
    '',
    '/'
);

$thumbGraph = array_merge($vars, [
    'height' => '60',
    'width'  => $thumbWidth,
    'legend' => 'no',
    'to'     => \App\Facades\LibrenmsConfig::get('time.now'),
]);
@endphp

<div class="container-fluid">
<div class="row">
<div class="col-md-12">

@php print_optionbar_start(); @endphp
{!! $title !!}
@if (count($graphSubtypes) > 1)
<div style="float: right;"><form action="">
    {!! csrf_field() !!}
    <select name="type" id="type" onchange="window.open(this.options[this.selectedIndex].value,'_top')" class="devices-graphs-select">
        @foreach ($graphSubtypes as $avail_type)
            <option value="{{ $graphUrl(['type' => $type . '_' . $avail_type]) }}"{{ $avail_type === $subtype ? ' selected' : '' }}>
                {{ \LibreNMS\Util\StringHelpers::niceCase($avail_type) }}
            </option>
        @endforeach
    </select>
</form></div>
@endif
@php print_optionbar_end(); @endphp

@if (! $showCommand)
<table width="100%" class="thumbnail_graph_table"><tr>
@foreach ($thumbArray as $period => $text)
@php $thumbGraph['from'] = \App\Facades\LibrenmsConfig::get("time.$period"); @endphp
<td style="text-align: center;">
    <b>{{ $text }}</b>
    <a href="{{ $graphUrl(['from' => $thumbGraph['from'], 'to' => $thumbGraph['to']]) }}">
        {!! \LibreNMS\Util\Url::lazyGraphTag($thumbGraph) !!}
    </a>
</td>
@endforeach
</tr></table>
<hr />
@endif

@php
$mainGraph = array_merge($vars, ['height' => $graphHeight, 'width' => $graphWidth]);
$graph_array = $mainGraph;
include base_path('includes/html/print-date-selector.inc.php');
@endphp

<div style="padding-top: 5px"></div>
<center>
@if (isset($vars['legend']) && $vars['legend'] == 'no')
<a href="{{ $graphUrl(['legend' => null]) }}">Show Legend</a>
@else
<a href="{{ $graphUrl(['legend' => 'no']) }}">Hide Legend</a>
@endif
 |
@if (isset($vars['previous']) && $vars['previous'] == 'yes')
<a href="{{ $graphUrl(['previous' => null]) }}">Hide Previous</a>
@else
<a href="{{ $graphUrl(['previous' => 'yes']) }}">Show Previous</a>
@endif
 |
@if ($showCommand)
<a href="{{ $graphUrl(['showcommand' => null]) }}">Hide RRD Command</a>
@else
<a href="{{ $graphUrl(['showcommand' => 'yes']) }}">Show RRD Command</a>
@endif
@if (($vars['type'] ?? '') === 'port_bits')
 |
@if ($vars['port_speed_zoom'] ?? \App\Facades\LibrenmsConfig::get('graphs.port_speed_zoom'))
<a href="{{ $graphUrl(['port_speed_zoom' => 0]) }}">Zoom to Traffic</a>
@else
<a href="{{ $graphUrl(['port_speed_zoom' => 1]) }}">Zoom to Port Speed</a>
@endif
 | To show trend, set to future date
@endif
@if (str_contains((string) ($vars['type'] ?? ''), 'sensor_'))
 | To show trend, set to future date
@endif
</center>

{!! generate_graph_js_state($mainGraph) !!}

<div style="width: {{ $graphWidth }}; margin: auto;"><center>
@if (\App\Facades\LibrenmsConfig::get('webui.dynamic_graphs', false) === true)
{!! generate_dynamic_graph_js($mainGraph) !!}
{!! generate_dynamic_graph_tag($mainGraph) !!}
@else
{!! \LibreNMS\Util\Url::lazyGraphTag($mainGraph) !!}
@endif
</center></div>

@if (\App\Facades\LibrenmsConfig::has('graph_descr.' . ($vars['type'] ?? '')))
@php print_optionbar_start(); @endphp
<div style="float: left; width: 30px;">
    <div style="margin: auto auto;">
        <i class="fa-solid fa-circle-info fa-lg icon-theme" aria-hidden="true"></i>
    </div>
</div>
{{ \App\Facades\LibrenmsConfig::get('graph_descr.' . $vars['type']) }}
@php print_optionbar_end(); @endphp
@endif

@if ($showCommand)
@php
$vars = $graph_array;
$_GET = $graph_array;
$auth = false;
$command_only = 1;
require base_path('includes/html/graphs/graph.inc.php');
@endphp
@endif

</div>
</div>
</div>

<x-refresh-timer :refresh="$refresh"></x-refresh-timer>
@endsection
