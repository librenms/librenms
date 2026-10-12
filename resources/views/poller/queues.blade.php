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
    @if($queues->isEmpty())
        <p class="text-muted">{{ __('No queues are being worked. Is the librenms-worker service running?') }}</p>
    @else
    <div class="table-responsive">
    <table class="table table-condensed table-hover">
        <thead>
        <tr>
            <th>{{ __('Queue') }}</th>
            <th title="{{ __('Jobs ready to run') }}">{{ __('Pending') }}</th>
            <th title="{{ __('Jobs scheduled to run later') }}">{{ __('Scheduled') }}</th>
            <th title="{{ __('Jobs being run now') }}">{{ __('Running') }}</th>
            <th>{{ __('Workers') }}</th>
            <th title="{{ __('Estimated time to clear pending jobs') }}">{{ __('Wait') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($queues as $queue)
        <tr>
            <td>{{ $queue['name'] }}</td>
            <td>{{ $queue['pending'] }}</td>
            <td>{{ $queue['delayed'] }}</td>
            <td>{{ $queue['reserved'] }}</td>
            <td>{{ $queue['processes'] }}</td>
            <td @class(['text-danger' => $queue['wait'] > 60])>{{ \LibreNMS\Util\Time::formatInterval($queue['wait']) ?: '0s' }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</x-panel>

<x-panel title="{{ __('Workers') }}">
    @forelse($workers as $master)
        <h4>
            {{ $master['name'] }}
            <span class="label label-{{ $master['status'] == 'running' ? 'success' : 'warning' }}">{{ $master['status'] }}</span>
        </h4>
        <table class="table table-condensed">
            <tbody>
            @foreach($master['supervisors'] as $supervisor)
            <tr>
                <td style="width: 25%">{{ $supervisor['name'] }}</td>
                <td style="width: 15%"><span class="label label-{{ $supervisor['status'] == 'running' ? 'success' : 'warning' }}">{{ $supervisor['status'] }}</span></td>
                <td>
                    @foreach($supervisor['processes'] as $queue => $count)
                        {{ \Illuminate\Support\Str::after($queue, ':') }}: {{ $count }}@if(! $loop->last), @endif
                    @endforeach
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    @empty
        <p class="text-muted">{{ __('No workers are running. Is the librenms-worker service running?') }}</p>
    @endforelse
    <a href="{{ url(config('horizon.path')) }}" target="_blank">{{ __('Open Horizon') }} <i class="fa fa-external-link"></i></a>
</x-panel>

<x-panel title="{{ __('Failed Jobs') }} ({{ $failed_count }})">
    @include('poller.partials.jobs-table', ['jobs' => $failed, 'showDevice' => true, 'actions' => true, 'empty' => __('No failed jobs')])
</x-panel>

@endsection
