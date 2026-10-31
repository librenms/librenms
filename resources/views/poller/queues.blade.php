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
            <th title="{{ __('Average workers kept busy, from the time each device took on its last run, and the workers Horizon nodes run for the queue') }}">{{ __('Busy / Workers') }}</th>
            <th>{{ __('Workers') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($queues as $queue)
        <tr>
            <td>{{ $queue['name'] }}</td>
            <td>{{ $queue['pending'] }}</td>
            <td>{{ $queue['scheduled'] }}</td>
            <td @class(['text-warning' => $queue['retrying'] > 0])>{{ $queue['retrying'] }}</td>
            <td>{{ $queue['reserved'] }}</td>
            <td @class(['text-danger' => $queue['interval'] !== null && $queue['age'] > $queue['interval']])>{{ \LibreNMS\Util\Time::formatInterval($queue['age']) ?: '0s' }}</td>
            <td @class(['text-danger' => $queue['busy'] !== null && $queue['workers'] !== null && $queue['busy'] >= $queue['workers']])>
                {{ $queue['busy'] ?? '' }}@if($queue['workers'] !== null) / {{ $queue['workers'] }}@endif
            </td>
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

@if($workers->isNotEmpty())
<x-panel title="{{ __('Horizon Nodes') }}">
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
    @can('worker.horizon')
    <a href="{{ url(config('horizon.path')) }}" target="_blank">{{ __('Open Horizon') }} <i class="fa fa-external-link"></i></a>
    @endcan
</x-panel>
@endif

<x-panel>
    <x-slot name="heading">
        <x-help-heading id="overdue-devices" :label="__('Overdue Devices') . ' (' . $overdue->count() . ')'">{{ __('Devices that are up but have not been polled in twice the poll interval. A device is not queued again while its poll job holds the job lock: the job is waiting, running or retrying. A lock left behind by a lost job expires after :minutes minutes, release it to queue the device now.', ['minutes' => $lock_minutes]) }}</x-help-heading>
    </x-slot>
    @if($overdue->isEmpty())
        <p class="text-muted">{{ __('All devices are being polled on time') }}</p>
    @else
    <div class="table-responsive">
    <table class="table table-condensed table-hover">
        <thead>
        <tr>
            <th>{{ __('Device') }}</th>
            <th>{{ __('Last Polled') }}</th>
            <th>{{ __('Job Lock') }}</th>
            @can('worker.update')<th></th>@endcan
        </tr>
        </thead>
        <tbody>
        @foreach($overdue as $item)
        <tr>
            <td><x-device-link :device="$item['device']" /></td>
            <td><span title="{{ $item['device']->last_polled }}">{{ $item['device']->last_polled?->diffForHumans() }}</span></td>
            <td>
                @if($item['locked'])
                    <span class="label label-warning" title="{{ __('A poll job is queued, running or retrying, or its lock was left behind') }}">{{ __('held') }}</span>
                @else
                    <span class="label label-default" title="{{ __('Nothing is holding up the device, check that its poller group queue has workers') }}">{{ __('free') }}</span>
                @endif
            </td>
            @can('worker.update')
            <td>
                @if($item['locked'])
                <form method="post" action="{{ route('poller.queues.release-lock', $item['device']->device_id) }}" style="display: inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-xs btn-default" title="{{ __('Release the lock so the device is queued again') }}"><i class="fa fa-unlock"></i></button>
                </form>
                @endif
            </td>
            @endcan
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</x-panel>

<x-panel>
    <x-slot name="heading">
        <x-help-heading id="down-devices-retrying" :label="__('Down Devices Retrying') . ' (' . $retrying->count() . ')'">{{ __('Devices that did not respond are retried a few times before their next scheduled run. The device is down, the job did not fail.') }}</x-help-heading>
    </x-slot>
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

<x-panel>
    <x-slot name="heading">
        <x-help-heading id="failed-jobs" :label="__('Failed Jobs') . ' (' . $failed_count . ')'">
            {{ __('Jobs that crashed, timed out, or ran out of memory. A device being down is not a failed job.') }}
            @if(\App\Horizon\Supervisors::supported()){{ __('Horizon keeps its own list of failed jobs, retrying or deleting a job here does not change it.') }}@endif
        </x-help-heading>
    </x-slot>
    @include('poller.partials.jobs-table', ['jobs' => $failed, 'showDevice' => true, 'actions' => Gate::allows('worker.update'), 'empty' => __('No failed jobs')])
</x-panel>

@endsection
