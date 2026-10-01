@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-panel title="{{ __('Processes') }}">
        <div class="table-responsive">
            <table class="table table-hover table-condensed table-striped">
                <thead>
                    <tr>
                        @foreach($data['columns'] as $col)
                            <th>
                                <a href="{{ $col['url'] }}">
                                    @if($col['icon'])
                                        <i class="{{ $col['icon'] }}" aria-hidden="true"></i>
                                    @endif
                                    @if($col['title'])
                                        <abbr title="{{ $col['title'] }}">{{ $col['label'] }}</abbr>
                                    @else
                                        {{ $col['label'] }}
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
    </x-panel>
</x-device.page>
@endsection
