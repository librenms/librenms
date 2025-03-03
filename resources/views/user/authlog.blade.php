@extends('layouts.librenmsv1')

@section('title', __('Authlog'))

@section('content')
<div class="container-fluid">


<x-panel id="manage-authlog-panel">
    <x-slot name="title">
        <i class="fa fa-user-circle-o fa-fw fa-lg" aria-hidden="true"></i> {{ __('Authlog') }}
    </x-slot>

    <div class="table-responsive">
        <table id="authlog" class="table table-hover table-condensed table-striped" style="display: none;">
            <thead>
            <tr>
                <th data-column-id='timestamp'>{{ __('Timestamp') }}</th>
                <th data-column-id='user'>{{ __('User') }}</th>
                <th data-column-id='ip'>{{ __('IP Address') }}</th>
                <th data-column-id='authres'>{{ __('Result') }}</th>
            </tr>
            </thead>
            <tbody id="authlog_rows">
                @foreach($authlog as $log)
                    <tr>
                        <td>{{ $log->datetime }}</td>
                        <td>{{ $log->user }}</td>
                        <td>{{ $log->address }}</td>
                        <td>{{ $log->result }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-panel>
@endsection

@section('scripts')
<script>
    var authlog_grid = $("#authlog");
    authlog_grid.bootgrid();
    authlog_grid.css('display', 'table'); // done loading, show
</script>
@endsection

@section('css')
<style>
    #manage-authlog-panel .panel-title { font-size: 18px; }
</style>
@endsection
