<x-panel title="{{ __('Job History') }}" style="margin-top: 10px">
    @if($jobs['monitoring'])
        <form method="post" action="{{ route('device.jobs.stop-monitoring', $device) }}" class="pull-right">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-stop"></i> {{ __('Stop recording') }}</button>
        </form>
        <p>{{ __('Recording every job for this device.') }}</p>
        @include('poller.partials.jobs-table', ['jobs' => $jobs['jobs'], 'showDevice' => false, 'actions' => false, 'empty' => __('No jobs recorded yet')])
    @else
        <form method="post" action="{{ route('device.jobs.monitor', $device) }}">
            @csrf
            <p>{{ __('Only failed jobs are kept by default. Record every poll and discovery job for this device to troubleshoot it, then stop recording to clear the history.') }}</p>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-circle"></i> {{ __('Start recording') }}</button>
        </form>
    @endif
</x-panel>

<x-panel title="{{ __('Failed Jobs') }}">
    @include('poller.partials.jobs-table', ['jobs' => $jobs['failed'], 'showDevice' => false, 'actions' => true, 'empty' => __('No failed jobs')])
</x-panel>
