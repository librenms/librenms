@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{ __('Packages') }}</h3>
        </div>
        <div class="panel-body">
            <table class="table table-hover table-condensed table-striped">
                <thead>
                    <tr>
                        <th style="width: 250px;">{{ __('Package Name') }}</th>
                        <th>{{ __('Version') }}</th>
                        <th>{{ __('Architecture') }}</th>
                        <th>{{ __('Package Manager') }}</th>
                        <th>{{ __('Size') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($data['packages'] as $package)
                    <tr>
                        <td>
                            <a href="{{ url('packages') . '?name=' . urlencode($package->name) }}">{{ $package->name }}</a>
                        </td>
                        <td>
                            {{ $package->version }}{{ $package->build ? '-' . $package->build : '' }}
                        </td>
                        <td>{{ $package->arch }}</td>
                        <td>{{ $package->manager }}</td>
                        <td>{{ \LibreNMS\Util\Number::formatSi($package->size, 2, 0, '') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 20px;">
                            <em>{{ __('No packages found for this device.') }}</em>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-device.page>
@endsection
