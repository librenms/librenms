@extends('layouts.librenmsv1')

@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
<div class="row">
<div class="col-md-12">

<div class="panel panel-default">
    <div class="panel-heading">
        @if (count($subtypeOptions) > 1)
            <div class="pull-right">
                <x-select name="graph_subtype" :options="$subtypeOptions" :selected="$subtypeSelected"
                          x-data @change="window.location = $event.target.value"></x-select>
            </div>
        @endif
        @if ($port)
            <x-device-link :device="$device"></x-device-link> :: {{ __('Port') }} <x-port-link :port="$port"></x-port-link>
        @elseif ($device)
            <x-device-link :device="$device"></x-device-link>
        @endif
        {{ $subtitle }}
    </div>
</div>

@unless ($showCommand)
<table width="100%" class="thumbnail_graph_table"><tr>
@foreach ($periodThumbs as $thumb)
    <td style="text-align: center;">
        <b>{{ $thumb['text'] }}</b>
        <a href="{{ $thumb['link'] }}">
            {!! \LibreNMS\Util\Url::lazyGraphTag($thumb['vars']) !!}
        </a>
    </td>
@endforeach
</tr></table>
<hr />
@endunless

{!! $dateSelectorHtml !!}

<div style="padding-top: 5px"></div>
<center>
@foreach ($toggles as $toggle)
    @if (! $loop->first) | @endif
    <a href="{{ $toggle['link'] }}">{{ $toggle['text'] }}</a>
@endforeach
@if ($trendHint) | {{ __('To show trend, set to future date') }} @endif
</center>

{!! $graphJsState !!}

<div style="width: {{ $graphWidth }}; margin: auto;"><center>
@if ($dynamicGraphHtml !== null)
{!! $dynamicGraphHtml !!}
@else
{!! $mainGraphTag !!}
@endif
</center></div>

@if ($graphDescr !== null)
<div class="panel panel-default">
    <div class="panel-heading">
        <div style="float: left; width: 30px;">
            <div style="margin: auto auto;">
                <i class="fa-solid fa-circle-info fa-lg icon-theme" aria-hidden="true"></i>
            </div>
        </div>
        {{ $graphDescr }}
    </div>
</div>
@endif

@if ($showCommand)
{!! $rrdCommandHtml !!}
@endif

</div>
</div>
</div>

<x-refresh-timer :refresh="$refresh"></x-refresh-timer>
@endsection
