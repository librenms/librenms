@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{ __('Processes') }}</h3>
        </div>
        <div class="panel-body">
            @php
                $columns = [
                    'pid' => ['label' => 'PID', 'title' => ''],
                    'vsz' => ['label' => 'VSZ', 'title' => 'Virtual Memory'],
                    'rss' => ['label' => 'RSS', 'title' => 'Resident Memory'],
                    'cputime' => ['label' => 'cputime', 'title' => ''],
                    'user' => ['label' => 'user', 'title' => ''],
                    'command' => ['label' => 'command', 'title' => ''],
                ];
            @endphp
            <div class="table-responsive">
                <table class="table table-hover table-condensed table-striped">
                    <thead>
                        <tr>
                            @foreach($columns as $colKey => $colMeta)
                                @php
                                    $isSorted = $data['order'] === $colKey;
                                    $nextBy = ($isSorted && $data['by'] === 'asc') ? 'desc' : 'asc';
                                    $iconClass = $isSorted ? ($data['by'] === 'asc' ? 'fa fa-chevron-up' : 'fa fa-chevron-down') : '';
                                    $colUrl = route('device', [
                                        'device' => $device,
                                        'tab' => 'processes',
                                        'vars' => 'order=' . $colKey . '/by=' . $nextBy
                                    ]);
                                @endphp
                                <th>
                                    <a href="{{ $colUrl }}">
                                        @if($iconClass)<i class="{{ $iconClass }}" aria-hidden="true"></i>@endif
                                        @if($colMeta['title'])
                                            <abbr title="{{ $colMeta['title'] }}">{{ $colMeta['label'] }}</abbr>
                                        @else
                                            {{ $colMeta['label'] }}
                                        @endif
                                    </a>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($data['processes'] as $process)
                        <tr>
                            <td>{{ $process->pid }}</td>
                            <td>{{ \LibreNMS\Util\Number::formatSi($process->vsz * 1024, 2, 0, '') }}</td>
                            <td>{{ \LibreNMS\Util\Number::formatSi($process->rss * 1024, 2, 0, '') }}</td>
                            <td>{{ $process->cputime }}</td>
                            <td>{{ $process->user }}</td>
                            <td class="tw:break-all">{{ $process->command }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 20px;">
                                <em>{{ __('No processes found for this device.') }}</em>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-device.page>
@endsection
