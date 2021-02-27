@extends('services.index')

@section('title', __('Service Logs'))

@section('content')

@parent

<x-panel id="manage-log-panel">
    <x-slot name="title">
        <i class="fa fa-stackoverflow fa-fw fa-lg" aria-hidden="true"></i> @lang('Service Logs')
    </x-slot>

    <div class="table-responsive">
        <table id="log" class="table table-hover table-condensed table-striped" style="display: none;">
            <thead>
            <tr>
                <th data-column-id='timestamp'>@lang('Timestamp')</th>
                <th data-column-id='hostname'>@lang('Hostname')</th>
                <th data-column-id='message'>@lang('Message')</th>
                <th data-column-id='severity'>@lang('Status')</th>
            </tr>
            </thead>
            <tbody id="log_rows">
                @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->datetime }}</td>
                        <td>{{ $log->device_id }}</td>
                        <td>{{ $log->message }}</td>
                        <td>{{ $log->severity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-panel>
@endsection

@section('scripts')
<script>
    var log_grid = $("#log");
    log_grid.bootgrid();
    log_grid.css('display', 'table'); // done loading, show
</script>
@endsection

@section('css')
<style>
    #manage-log-panel .panel-title { font-size: 18px; }
</style>
@endsection
