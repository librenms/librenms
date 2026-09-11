@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-panel :title="__('Packages')">
        <x-slot:table>
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
                        <td colspan="5" class="tw:text-center tw:p-5">
                            <em>{{ __('No packages found for this device.') }}</em>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </x-slot:table>
    </x-panel>
</x-device.page>
@endsection
