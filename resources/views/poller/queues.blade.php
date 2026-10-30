@extends('poller.index')

@section('title', __('Queues'))

@section('content')

@parent

<x-panel>
    <x-slot name="heading">
        <div class="tw:flex tw:justify-between tw:items-center">
            <h3 class="panel-title">{{ __('Queues') }}</h3>
            <a href="{{ route('poller.queues') }}" class="btn btn-default btn-xs"><i class="fa fa-refresh" aria-hidden="true"></i> {{ __('Refresh') }}</a>
        </div>
    </x-slot>
    <div class="table-responsive">
    <table class="table table-condensed table-hover">
        <thead>
        <tr>
            <th>{{ __('Queue') }}</th>
            <th title="{{ __('Jobs ready to run') }}">{{ __('Pending') }}</th>
            <th title="{{ __('Jobs scheduled to run later') }}">{{ __('Scheduled') }}</th>
            <th title="{{ __('Jobs waiting to retry a device that did not respond') }}">{{ __('Retrying') }}</th>
            <th title="{{ __('Jobs being run now') }}">{{ __('Running') }}</th>
            <th title="{{ __('Time the oldest job ready to run has waited') }}">{{ __('Oldest') }}</th>
            <th>{{ __('Workers') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($queues as $queue)
        <tr>
            <td>{{ $queue['name'] }}</td>
            <td>{{ $queue['pending'] ?? '-' }}</td>
            <td>{{ $queue['scheduled'] ?? '-' }}</td>
            <td @class(['text-warning' => $queue['retrying'] > 0])>{{ $queue['retrying'] }}</td>
            <td>{{ $queue['reserved'] ?? '-' }}</td>
            <td @class(['text-danger' => $queue['interval'] !== null && $queue['age'] > $queue['interval']])>{{ $queue['age'] === null ? '-' : (\LibreNMS\Util\Time::formatInterval($queue['age']) ?: '0s') }}</td>
            <td>
                @if($queue['worked'])
                    <span class="label label-success">{{ __('running') }}</span>
                @else
                    <span class="label label-danger">{{ __('none') }}</span>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</x-panel>

<x-panel title="{{ __('Worker Nodes') }}">
    @if($workers->isEmpty())
        <p class="text-muted">{{ __('No worker nodes have checked in. Is the librenms-worker service running and NODE_ID set in .env?') }}</p>
    @else
    <div class="table-responsive">
    <table class="table table-condensed">
        <thead>
        <tr>
            <th>{{ __('Node') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Last Checkin') }}</th>
            <th>{{ __('Queues') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($workers as $node)
        <tr>
            <td>{{ $node['name'] }}</td>
            <td><span class="label label-{{ $node['status'] == 'running' ? 'success' : 'warning' }}">{{ $node['status'] }}</span></td>
            <td><span title="{{ $node['last_report'] }}">{{ $node['last_report']->diffForHumans() }}</span></td>
            <td>{{ implode(', ', $node['queues']) }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
    @if(\App\Horizon\Supervisors::available() && Gate::allows('worker.horizon'))
    <a href="{{ url(config('horizon.path')) }}" target="_blank">{{ __('Open Horizon') }} <i class="fa fa-external-link"></i></a>
    @endif
</x-panel>

<x-panel title="{{ __('Down Devices Retrying') }} ({{ $retrying->count() }})">
    <p class="text-muted">{{ __('Devices that did not respond are retried a few times before their next scheduled run. The device is down, the job did not fail.') }}</p>
    @if($retrying->isEmpty())
        <p class="text-muted">{{ __('No devices are being retried') }}</p>
    @else
    <div class="table-responsive">
    <table class="table table-condensed table-hover">
        <thead>
        <tr>
            <th>{{ __('Device') }}</th>
            <th>{{ __('Job') }}</th>
            <th>{{ __('Attempts') }}</th>
            <th>{{ __('Next Retry') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($retrying as $retry)
        <tr>
            <td>@if($retry['device_id'])<x-device-link :device="$retry['device_id']" />@endif</td>
            <td>{{ $retry['name'] }}</td>
            <td>{{ $retry['attempt'] }}@if($retry['tries']) / {{ $retry['tries'] }}@endif</td>
            <td><span title="{{ $retry['retry_at'] }}">{{ $retry['retry_at']->diffForHumans() }}</span></td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</x-panel>

<x-panel title="{{ __('Failed Jobs') }} ({{ $failed_count }})">
    <p class="text-muted">{{ __('Jobs that crashed, timed out, or ran out of memory. A device being down is not a failed job.') }}</p>
    @include('poller.partials.jobs-table', ['jobs' => $failed, 'showDevice' => true, 'actions' => Gate::allows('worker.update'), 'empty' => __('No failed jobs')])
</x-panel>

@endsection
