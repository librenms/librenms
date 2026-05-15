@extends('layouts.librenmsv1')

@section('title', __('Port Security'))

@section('content')
<div class="container-fluid">
    <div class="tw:pb-4">
        <x-filter name="port-security" :fields="$filterFields" :initial="$filter" :reload="true"/>
    </div>

    @include('port-security.includes.table', [
        'portSecurity' => $portSecurity,
        'showDevice' => $showDevice,
        'perPage' => $perPage,
        'paginationOptions' => $paginationOptions,
    ])
</div>
@endsection
