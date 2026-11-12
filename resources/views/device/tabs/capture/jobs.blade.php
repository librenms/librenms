@if($jobs['monitor'])
<x-panel style="margin-top: 10px">
    <x-slot name="heading">
        <x-help-heading id="job-history" :label="__('Job History')">{{ __('Only failed jobs are kept by default. Record every poll and discovery job for this device to troubleshoot it, then stop recording to clear the history.') }}</x-help-heading>
    </x-slot>
    @if($jobs['monitoring'])
        @can('worker.update')
        <form method="post" action="{{ route('device.jobs.stop-monitoring', $device) }}" class="pull-right">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-stop"></i> {{ __('Stop recording') }}</button>
        </form>
        @endcan
        <p>{{ __('Recording every job for this device.') }}</p>
        @include('poller.partials.jobs-table', ['jobs' => $jobs['jobs'], 'showDevice' => false, 'showRuntime' => true, 'actions' => false, 'empty' => __('No jobs recorded yet')])
    @else
        <form method="post" action="{{ route('device.jobs.monitor', $device) }}">
            @csrf
            @can('worker.update')
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-circle"></i> {{ __('Start recording') }}</button>
            @endcan
        </form>
    @endif
</x-panel>
@endif

<x-panel style="margin-top: 10px">
    <x-slot name="heading">
        <x-help-heading id="device-failed-jobs" :label="__('Failed Jobs')">{{ __('Jobs that crashed, timed out, or ran out of memory. A device being down is not a failed job.') }}</x-help-heading>
    </x-slot>
    @include('poller.partials.jobs-table', ['jobs' => $jobs['failed'], 'showDevice' => false, 'actions' => Gate::allows('worker.update'), 'empty' => __('No failed jobs')])
</x-panel>
