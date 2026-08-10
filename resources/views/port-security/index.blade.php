@extends('layouts.librenmsv1')

@section('title', __('Port Security'))

@section('content')
<div class="container-fluid">
    <div class="tw:flex tw:items-center tw:justify-between tw:pb-4 tw:gap-4">
        <div class="tw:flex-1">
            <x-filter name="port-security" :fields="$filterFields" :initial="$filter" :reload="true"/>
        </div>
        <x-table-export
            :export-route="route('port-security.export')"
            :filter="$filter"
            :page="$portSecurity->currentPage()"
            :per-page="$perPage"
            class="tw:shrink-0"
        />
    </div>

    @include('port-security.includes.table', [
        'portSecurity' => $portSecurity,
        'showDevice' => $showDevice,
        'perPage' => $perPage,
        'paginationOptions' => $paginationOptions,
    ])
</div>
@endsection
