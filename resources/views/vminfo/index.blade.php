@extends('layouts.librenmsv1')

@section('title', __('Virtual Machines'))

@section('content')
<div class="container-fluid">
    @include('vminfo.includes.table', [
        'vms' => $vms,
        'filterName' => 'vminfo',
        'filterFields' => $filterFields,
        'filter' => $filter,
        'showDevice' => true,
        'perPage' => $perPage,
    ])
</div>
@endsection
