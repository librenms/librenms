@extends('layouts.librenmsv1')

@section('title', __('Alert Rules'))

@section('content')
    @if($device)
        <x-device.page :device="$device">
            <x-device.edit-tabs :device="$device" tab="alert-rules" />
            @include('alert-rules.list')
        </x-device.page>
    @else
        <div class="container-fluid">
            @include('alert-rules.list')
        </div>
    @endif

    @if(Gate::allows('create', \App\Models\AlertRule::class) || $rules->contains(fn ($rule) => Gate::allows('update', $rule)))
        @include('alert-rules.editor')
    @endif
    @can('create', \App\Models\AlertRule::class)
        @include('alert-rules.pickers')
    @endcan
@endsection
