@if($jobs->isEmpty())
    <p class="text-muted">{{ $empty }}</p>
@else
<div class="table-responsive">
<table class="table table-condensed table-hover">
    <thead>
    <tr>
        <th>{{ __('Job') }}</th>
        @if($showDevice)<th>{{ __('Device') }}</th>@endif
        <th>{{ __('Queue') }}</th>
        <th>{{ __('Status') }}</th>
        <th>{{ __('Finished') }}</th>
        <th>{{ __('Runtime') }}</th>
        <th>{{ __('Error') }}</th>
        @if($actions)<th></th>@endif
    </tr>
    </thead>
    <tbody>
    @foreach($jobs as $job)
    <tr>
        <td>{{ $job['name'] }}</td>
        @if($showDevice)
        <td>@if($job['device_id'])<x-device-link :device="$job['device_id']" tab="capture" section="jobs" />@endif</td>
        @endif
        <td>{{ $job['queue'] }}</td>
        <td>
            <span @if($job['status'] == 'retrying') title="{{ __('The device did not respond, the job will run again') }}" @endif class="label label-{{ match($job['status']) { 'completed' => 'success', 'failed' => 'danger', 'retrying' => 'warning', 'reserved' => 'info', default => 'default' } }}">{{ $job['status'] }}</span>
            @if($job['retried'])<span class="label label-default">{{ __('manually retried') }}</span>@endif
        </td>
        <td>@if($job['finished'])<span title="{{ $job['finished'] }}">{{ $job['finished']->diffForHumans() }}</span>@endif</td>
        <td>@if($job['runtime'] !== null){{ round($job['runtime'], 2) }}s @endif</td>
        <td>
            @if($job['error'])
            <details>
                <summary>{{ \Illuminate\Support\Str::limit($job['error'], 120) }}</summary>
                <pre style="max-height: 400px; overflow: auto">{{ $job['exception'] }}</pre>
            </details>
            @endif
        </td>
        @if($actions)
        <td class="text-nowrap">
            <form method="post" action="{{ route('poller.queues.retry', $job['id']) }}" style="display: inline">
                @csrf
                <button type="submit" class="btn btn-xs btn-default" title="{{ __('Retry') }}"><i class="fa fa-repeat"></i></button>
            </form>
            <form method="post" action="{{ route('poller.queues.forget', $job['id']) }}" style="display: inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-xs btn-danger" title="{{ __('Delete') }}"><i class="fa fa-trash"></i></button>
            </form>
        </td>
        @endif
    </tr>
    @endforeach
    </tbody>
</table>
</div>
@endif
