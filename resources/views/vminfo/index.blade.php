@extends('layouts.librenmsv1')

@section('title', __('Virtual Machines'))

@section('content')
<div class="container-fluid">
    <div class="tw:flex tw:items-center tw:justify-between tw:pb-4 tw:gap-4">
        <div class="tw:flex-1">
            <x-filter name="vminfo" :fields="$filterFields" :initial="$filter" :reload="true"/>
        </div>
        <x-table-export
            :export-route="route('table.vminfo.export')"
            :filter="$filter"
            :page="$vms->currentPage()"
            :per-page="$perPage"
            class="tw:shrink-0"
        />
    </div>

    @include('vminfo.includes.table', [
        'vms' => $vms,
        'showDevice' => true,
        'perPage' => $perPage,
    ])
</div>
@endsection
