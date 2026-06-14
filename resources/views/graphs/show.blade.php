@extends('layouts.librenmsv1')

@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
<div class="row">
<div class="col-md-12">

<div class="panel panel-default">
    <div class="panel-heading">
        <div class="tw:flex tw:flex-col tw:items-start tw:gap-2 tw:sm:flex-row tw:sm:items-center tw:sm:justify-between">
            <div>
                @if ($port)
                    <x-device-link :device="$device"></x-device-link> :: {{ __('Port') }} <x-port-link :port="$port"></x-port-link>
                @elseif ($device)
                    <x-device-link :device="$device"></x-device-link>
                @endif
                {{ $subtitle }}
            </div>
            @if (count($subtypeOptions) > 1)
                <x-select name="graph_subtype" :options="$subtypeOptions" :selected="$subtypeSelected"
                          class="tw:[&_select]:px-3 tw:[&_select]:py-1.5"
                          x-data @change="window.location = $event.target.value"></x-select>
            @endif
        </div>
    </div>
</div>

@unless ($showCommand)
<div class="tw:flex tw:flex-wrap tw:justify-center tw:gap-1">
@foreach ($periodThumbs as $thumb)
    <a href="{{ $thumb['link'] }}"
       @if ($thumb['active']) aria-current="true" @endif
       style="--thumb-w: {{ $thumb['vars']['width'] }}px; --thumb-h: {{ $thumb['vars']['height'] }}px"
       class="tw:flex tw:flex-col tw:items-center tw:gap-0.5 tw:p-1 tw:rounded-lg tw:border tw:no-underline tw:transition-colors
              @if ($thumb['active']) tw:border-blue-500 tw:bg-blue-50 tw:dark:bg-gray-700 @else tw:border-transparent tw:hover:border-gray-300 tw:hover:bg-gray-100 tw:dark:hover:bg-gray-700 @endif">
        <span class="tw:text-base tw:font-medium tw:whitespace-nowrap tw:text-gray-700 tw:dark:text-gray-200">{{ $thumb['text'] }}</span>
        {!! \LibreNMS\Util\Url::lazyGraphTag($thumb['vars'], 'tw:block tw:w-[var(--thumb-w)] tw:h-[var(--thumb-h)] tw:object-cover tw:rounded') !!}
    </a>
@endforeach
</div>
<hr />
@endunless

<div class="tw:w-[48ch] tw:max-w-full tw:mx-auto">
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

<div class="tw:text-center tw:[&_img]:mx-auto">
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
