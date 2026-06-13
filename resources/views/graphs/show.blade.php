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
<table class="thumbnail_graph_table tw:w-full"><tr>
@foreach ($periodThumbs as $thumb)
    <td class="tw:text-center">
        <b>{{ $thumb['text'] }}</b>
        <a href="{{ $thumb['link'] }}">
            {!! \LibreNMS\Util\Url::lazyGraphTag($thumb['vars']) !!}
        </a>
    </td>
@endforeach
</tr></table>
<hr />
@endunless

<div class="tw:w-[48ch] tw:max-w-full tw:mx-auto tw:whitespace-nowrap">
    <x-date-range-picker :start="$graphFrom" :end="$graphTo"
                         class="tw:w-full tw:text-center tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-md"></x-date-range-picker>
</div>

<div class="tw:pt-[5px]"></div>
<div class="tw:text-center">
@foreach ($toggles as $toggle)
    @if (! $loop->first) | @endif
    <a href="{{ $toggle['link'] }}">{{ $toggle['text'] }}</a>
@endforeach
@if ($trendHint) | {{ __('To show trend, set to future date') }} @endif
</div>

{!! $graphJsState !!}

<div class="tw:w-fit tw:mx-auto tw:text-center">
@if ($dynamicGraphHtml !== null)
{!! $dynamicGraphHtml !!}
@else
{!! $mainGraphTag !!}
@endif
</div>

@if ($graphDescr !== null)
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="tw:float-left tw:w-[30px]">
            <div class="tw:m-auto">
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

@push('scripts')
<script>
    window.addEventListener('date-range-changed', (event) => {
        const { relativeStartSeconds, relativeEndSeconds, start, end } = event.detail;
        const url = new URL(window.location.href);

        const setOrDelete = (param, value) =>
            value ? url.searchParams.set(param, value) : url.searchParams.delete(param);

        const prevFrom = url.searchParams.get('from');
        const prevTo = url.searchParams.get('to');

        const fromOffset = relativeStartSeconds && LibreNMS.Date.toShortOffset(relativeStartSeconds);
        setOrDelete('from', fromOffset === '-1d' ? null : fromOffset || (start && LibreNMS.Date.toUrl(start)));
        setOrDelete('to', relativeEndSeconds ? LibreNMS.Date.toShortOffset(relativeEndSeconds) : end && LibreNMS.Date.toUrl(end));

        if (url.searchParams.get('from') !== prevFrom || url.searchParams.get('to') !== prevTo) {
            window.location.href = url.toString();
        }
    });
</script>
@endpush
